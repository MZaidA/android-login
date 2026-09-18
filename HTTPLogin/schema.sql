-- Skema database untuk HTTPLogin API.
-- Nama database mengikuti DB_NAME pada .env (default: tugas-login).

CREATE DATABASE IF NOT EXISTS `tugas-login`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `tugas-login`;

CREATE TABLE IF NOT EXISTS `user` (
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
