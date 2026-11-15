<?php

/*
|--------------------------------------------------------------------------
| Example Database Configuration
|--------------------------------------------------------------------------
| Copy this file to database.php and enter your own database details.
*/

$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_database_username';
$password = 'your_database_password';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    error_log("Database connection failed: " . $e->getMessage());

    http_response_code(500);

    exit("Unable to connect to the database.");

}