<?php
/**
 * Email konfigurace
 * Načítá hodnoty z .env souboru
 */

return [
    'from_email' => env('SMTP_FROM', 'noreply@example.com'),
    'from_name' => env('SMTP_FROM_NAME', 'VetApp'),
    'smtp_username' => env('SMTP_USER', ''),
    'smtp_password' => env('SMTP_PASS', ''),
    'smtp_server' => env('SMTP_HOST', 'localhost'),
    'smtp_port' => env('SMTP_PORT', 587),
    'use_starttls' => env('SMTP_USE_STARTTLS', true),
    'subject_registration' => 'Registrace - VetApp ZOO Tábor',

    // IMAP settings for saving sent emails
    'imap_server' => env('IMAP_HOST', 'localhost'),
    'imap_port' => env('IMAP_PORT', 993),
    'imap_use_ssl' => env('IMAP_USE_SSL', true),
    'imap_sent_folder' => env('IMAP_SENT_FOLDER', 'Sent'),
    'save_to_sent' => env('IMAP_SAVE_TO_SENT', true)
];
