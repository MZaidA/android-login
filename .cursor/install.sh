#!/usr/bin/env bash
# Idempotent environment bootstrap for the android-login repo.
# Sets up the PHP + MariaDB backend (HTTPLogin) and the Android SDK
# toolchain used to build the app (TugasHTTPLogin).
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ANDROID_HOME="${ANDROID_HOME:-$HOME/android-sdk}"
CMDLINE_TOOLS_ZIP="https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip"

echo "==> Installing system packages (PHP, MariaDB, tools)"
export DEBIAN_FRONTEND=noninteractive
sudo apt-get update -y
sudo apt-get install -y --no-install-recommends \
  php-cli php-mysql mariadb-server mariadb-client unzip curl ca-certificates

echo "==> Setting up Android SDK at $ANDROID_HOME"
CMDLINE_DIR="$ANDROID_HOME/cmdline-tools/latest"
if [ ! -x "$CMDLINE_DIR/bin/sdkmanager" ]; then
  tmpzip="$(mktemp --suffix=.zip)"
  curl -fSL -o "$tmpzip" "$CMDLINE_TOOLS_ZIP"
  mkdir -p "$ANDROID_HOME/cmdline-tools/tmp"
  unzip -q -o "$tmpzip" -d "$ANDROID_HOME/cmdline-tools/tmp"
  rm -rf "$CMDLINE_DIR"
  mv "$ANDROID_HOME/cmdline-tools/tmp/cmdline-tools" "$CMDLINE_DIR"
  rm -rf "$ANDROID_HOME/cmdline-tools/tmp" "$tmpzip"
fi

# `yes` receives SIGPIPE once sdkmanager stops reading; disable pipefail so that
# expected 141 does not abort the script, while sdkmanager's real exit still counts.
set +o pipefail
yes | "$CMDLINE_DIR/bin/sdkmanager" --sdk_root="$ANDROID_HOME" --licenses >/dev/null
set -o pipefail
"$CMDLINE_DIR/bin/sdkmanager" --sdk_root="$ANDROID_HOME" \
  "platform-tools" "platforms;android-35" "build-tools;35.0.0" >/dev/null

# Persist Android env for interactive shells.
if ! grep -q "ANDROID_HOME=" "$HOME/.bashrc" 2>/dev/null; then
  {
    echo "export ANDROID_HOME=\"$ANDROID_HOME\""
    echo "export ANDROID_SDK_ROOT=\"$ANDROID_HOME\""
    echo "export PATH=\"\$ANDROID_HOME/cmdline-tools/latest/bin:\$ANDROID_HOME/platform-tools:\$PATH\""
  } >> "$HOME/.bashrc"
fi

# Point the Gradle build at the SDK (gitignored).
echo "sdk.dir=$ANDROID_HOME" > "$REPO_ROOT/TugasHTTPLogin/local.properties"

echo "==> Preparing backend .env"
if [ ! -f "$REPO_ROOT/HTTPLogin/.env" ]; then
  cp "$REPO_ROOT/HTTPLogin/.env.example" "$REPO_ROOT/HTTPLogin/.env"
fi

echo "==> Initializing MariaDB"
sudo service mariadb start || true
for _ in $(seq 1 30); do
  sudo mariadb -e "SELECT 1" >/dev/null 2>&1 && break
  sleep 1
done
# Allow the app to connect as root with an empty password (matches .env.example),
# over both the unix socket and TCP (127.0.0.1). Dev-only convenience.
sudo mariadb -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD(''); \
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY ''; \
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; \
FLUSH PRIVILEGES;"
sudo mariadb < "$REPO_ROOT/HTTPLogin/schema.sql"

echo "==> Warming Gradle cache / validating Android build"
( cd "$REPO_ROOT/TugasHTTPLogin" \
  && ANDROID_HOME="$ANDROID_HOME" ANDROID_SDK_ROOT="$ANDROID_HOME" \
     ./gradlew assembleDebug --no-daemon )

echo "==> install.sh complete"
