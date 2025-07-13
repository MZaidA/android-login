package com.example.tugashttplogin;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MainActivity extends AppCompatActivity {

    private EditText usernameEditText;
    private EditText passwordEditText;
    private Button loginButton;
    private Button cancelButton;
    private ProgressBar progressBar;
    private TextView resultTextView;

    private final ExecutorService executorService = Executors.newSingleThreadExecutor();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        usernameEditText = findViewById(R.id.usernameEditText);
        passwordEditText = findViewById(R.id.passwordEditText);
        loginButton = findViewById(R.id.loginButton);
        cancelButton = findViewById(R.id.cancelButton);
        progressBar = findViewById(R.id.progressBar);
        resultTextView = findViewById(R.id.resultTextView);

        loginButton.setOnClickListener(v -> {
            String username = usernameEditText.getText().toString().trim();
            String password = passwordEditText.getText().toString().trim();
            if (username.isEmpty() || password.isEmpty()) {
                Toast.makeText(MainActivity.this, "Username dan password tidak boleh kosong", Toast.LENGTH_SHORT).show();
                return;
            }
            performLogin(username, password);
        });

        cancelButton.setOnClickListener(v -> {
            usernameEditText.setText("");
            passwordEditText.setText("");
            resultTextView.setText("");
        });
    }

    private void performLogin(final String username, final String password) {
        runOnUiThread(() -> {
            progressBar.setVisibility(View.VISIBLE);
            resultTextView.setText("");
            loginButton.setEnabled(false);
            cancelButton.setEnabled(false);
        });

        executorService.execute(() -> {
            String responseString;
            boolean isSuccess = false;
            try {
                URL url = new URL("http://192.168.1.3:8000/api/login");
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Content-Type", "application/json; utf-8");
                conn.setRequestProperty("Accept", "application/json");
                conn.setDoOutput(true);

                JSONObject jsonParam = new JSONObject();
                jsonParam.put("username", username);
                jsonParam.put("password", password);

                try (OutputStream os = conn.getOutputStream()) {
                    byte[] input = jsonParam.toString().getBytes(StandardCharsets.UTF_8);
                    os.write(input, 0, input.length);
                }

                int responseCode = conn.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    try (BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream(), StandardCharsets.UTF_8))) {
                        StringBuilder response = new StringBuilder();
                        String responseLine;
                        while ((responseLine = br.readLine()) != null) {
                            response.append(responseLine.trim());
                        }
                        responseString = "Login Berhasil: " + response.toString();
                        isSuccess = true;
                        usernameEditText.setText("");
                        passwordEditText.setText("");
                    }
                } else {
                    responseString = "Login Gagal: " + conn.getResponseMessage() + " (Kode: " + responseCode + ")";
                }
                conn.disconnect();
            } catch (Exception e) {
                responseString = "Error: " + e.getMessage();
            }

            final String finalResponseString = responseString;
            final boolean finalIsSuccess = isSuccess;
            runOnUiThread(() -> {
                progressBar.setVisibility(View.GONE);
                loginButton.setEnabled(true);
                cancelButton.setEnabled(true);

                if (finalIsSuccess) {
                    Toast.makeText(MainActivity.this, "Login Berhasil", Toast.LENGTH_SHORT).show();
                    Intent intent = new Intent(MainActivity.this, SuccessActivity.class);
                    startActivity(intent);
                } else {
                    resultTextView.setText(finalResponseString);
                    Toast.makeText(MainActivity.this, finalResponseString, Toast.LENGTH_LONG).show();
                }
            });
        });
    }
}