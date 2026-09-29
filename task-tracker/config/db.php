<?php
// Database connection. Usage:  $pdo = require __DIR__ . '/config/db.php';
//
// The values below are for a LOCAL machine (XAMPP defaults). On the live server, edit
// these four constants in the server's copy of this file only. Never commit live
// credentials to GitHub.
// Locally use 127.0.0.1, not 'localhost': on Windows 'localhost' makes every request wait
// about 2 seconds while PHP tries IPv6 first.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'task_tracker';
const DB_USER = 'root';
const DB_PASS = '';

// Production setting: never print PHP errors to visitors, they can reveal file paths and
// SQL. Change display_errors to '1' on your own machine while debugging.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Real server-side prepared statements: values are sent separately from the
            // SQL text, so they can never be interpreted as SQL.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $ex) {
    error_log('Database connection failed: ' . $ex->getMessage());
    http_response_code(500);
    exit('The database is unavailable right now. Please try again later.');
}
