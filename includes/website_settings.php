<?php

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Default Website Settings
|--------------------------------------------------------------------------
*/

$websiteSettings = [
    'business_name' => 'VIOLET-LUE SUITES & APARTMENTS',
    'tagline' => 'Your Comfort, Our Priority.',
    'address' => 'Monrovia, Liberia',
    'phone' => '',
    'email' => ''
];

/*
|--------------------------------------------------------------------------
| Load Saved Settings From Database
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->query("
        SELECT setting_key, setting_value
        FROM settings
        WHERE setting_key IN (
            'business_name',
            'tagline',
            'address',
            'phone',
            'email'
        )
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if (array_key_exists($row['setting_key'], $websiteSettings)) {

            $websiteSettings[$row['setting_key']] =
                $row['setting_value'] ?? $websiteSettings[$row['setting_key']];

        }

    }

} catch (PDOException $e) {

    error_log('Website settings error: ' . $e->getMessage());

}

/*
|--------------------------------------------------------------------------
| Safe Output Function
|--------------------------------------------------------------------------
*/

function websiteSetting($key)
{
    global $websiteSettings;

    return htmlspecialchars(
        $websiteSettings[$key] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}