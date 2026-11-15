<?php

/*
|--------------------------------------------------------------------------
| VIOLET-LUE Suites & Apartments
| Production Database Configuration Template
|--------------------------------------------------------------------------
| Copy this file to database.php on your hosting server.
| Replace the placeholder values with your hosting database credentials.
|--------------------------------------------------------------------------
*/

$host = 'YOUR_DATABASE_HOST';
$dbname = 'YOUR_DATABASE_NAME';
$username = 'YOUR_DATABASE_USERNAME';
$password = 'YOUR_DATABASE_PASSWORD';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    // Keep technical database errors in the server error log.
    error_log(
        "VIOLET-LUE database connection failed: " . $e->getMessage()
    );

    // Never expose database details to website visitors.
    http_response_code(500);

    exit(
        "Sorry, the website is temporarily unavailable. " .
        "Please try again later."
    );

}
