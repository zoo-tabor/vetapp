<?php
/**
 * Hlavní konfigurace aplikace
 * Načítá hodnoty z .env souboru
 */

return [
    'app_name' => env('APP_NAME', 'Parazitologická Evidence'),
    'app_url' => env('APP_URL', 'http://localhost'),
    'base_path' => '',

    // Časové pásmo
    'timezone' => env('APP_TIMEZONE', 'Europe/Prague'),

    // Session
    'session_lifetime' => env('SESSION_LIFETIME', 7200), // 2 hodiny v sekundách

    // Pagination
    'per_page' => 25,

    // Export
    'export_formats' => ['csv', 'xlsx'],

    // Debug mode
    'debug' => env('APP_DEBUG', false),
];