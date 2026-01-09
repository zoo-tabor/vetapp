<?php
/**
 * Konfigurace databáze
 * Načítá hodnoty z .env souboru
 */

return [
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_NAME', 'd328675_vetapp'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];