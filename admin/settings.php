<?php
session_start();

require_once __DIR__ . '/../config/database.php';

/* Protect the page */
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'settings';

/* CSRF protection */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = isset($_GET['saved']) ? 'Settings updated successfully.' : '';

/* Default settings */
$defaults = [
    'business_name' => 'VIOLET-LUE SUITES & APARTMENTS',
    'tagline' => 'Your Comfort, Our Priority.',
    'address' => 'Monrovia, Liberia',
    'phone' => '',
    'email' => '',
    'currency' => 'USD',
    'check_in_time' => '14:00',
    'check_out_time' => '11:00',
    'advance_booking_days' => '365',
    'max_guests_per_booking' => '10',
    'cancellation_notice_hours' => '24'
];

$settings = $defaults;

/* Load saved settings */
try {
    $stmt = $pdo->query("
        SELECT setting_key, setting_value
        FROM settings
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (array_key_exists($row['setting_key'], $settings)) {
            $settings[$row['setting_key']] =
                $row['setting_value'] ?? $defaults[$row['setting_key']];
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Unable to load system settings.';
}

/* Save settings */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {

        $submitted = [];

        foreach ($defaults as $key => $defaultValue) {
            $submitted[$key] = trim($_POST[$key] ?? '');
        }

        /* Validate business information */
        if ($submitted['business_name'] === '') {
            $error = 'Business name is required.';
        } elseif (strlen($submitted['business_name']) > 150) {
            $error = 'Business name cannot exceed 150 characters.';
        } elseif (strlen($submitted['tagline']) > 255) {
            $error = 'Tagline cannot exceed 255 characters.';
        } elseif (strlen($submitted['address']) > 255) {
            $error = 'Business address cannot exceed 255 characters.';
        } elseif (strlen($submitted['phone']) > 30) {
            $error = 'Phone number cannot exceed 30 characters.';
        } elseif (strlen($submitted['email']) > 150) {
            $error = 'Email address cannot exceed 150 characters.';
        } elseif (
            $submitted['email'] !== ''
            && !filter_var($submitted['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $error = 'Please enter a valid email address.';
        }

        /* Validate currency */
        $allowedCurrencies = ['USD', 'LRD', 'RWF', 'EUR', 'GBP'];

        if (
            !$error
            && !in_array($submitted['currency'], $allowedCurrencies, true)
        ) {
            $error = 'Please select a valid currency.';
        }

        /* Validate times */
        $timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

        if (
            !$error
            && (
                !preg_match($timePattern, $submitted['check_in_time'])
                || !preg_match($timePattern, $submitted['check_out_time'])
            )
        ) {
            $error = 'Please enter valid check-in and check-out times.';
        }

        /* Validate reservation limits */
        $advanceDays = filter_var(
            $submitted['advance_booking_days'],
            FILTER_VALIDATE_INT
        );

        $maxGuests = filter_var(
            $submitted['max_guests_per_booking'],
            FILTER_VALIDATE_INT
        );

        $cancellationHours = filter_var(
            $submitted['cancellation_notice_hours'],
            FILTER_VALIDATE_INT
        );

        if (
            !$error
            && ($advanceDays === false || $advanceDays < 1 || $advanceDays > 365)
        ) {
            $error = 'Advance booking days must be between 1 and 365.';
        }

        if (
            !$error
            && ($maxGuests === false || $maxGuests < 1 || $maxGuests > 50)
        ) {
            $error = 'Maximum guests must be between 1 and 50.';
        }

        if (
            !$error
            && ($cancellationHours === false
                || $cancellationHours < 0
                || $cancellationHours > 720)
        ) {
            $error = 'Cancellation notice must be between 0 and 720 hours.';
        }

        /* Save validated settings */
        if (!$error) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO settings (
                        setting_key,
                        setting_value
                    )
                    VALUES (
                        :setting_key,
                        :setting_value
                    )
                    ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value)
                ");

                foreach ($submitted as $key => $value) {
                    $stmt->execute([
                        'setting_key' => $key,
                        'setting_value' => $value
                    ]);
                }

                $pdo->commit();

                header('Location: settings.php?saved=1');
                exit;

            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());
                $error = 'Unable to save settings. Please try again.';
            }
        }

        /* Preserve submitted values when validation fails */
        $settings = $submitted;
    }
}

function settingEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>System Settings | VIOLET-LUE</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-heading h1 {
            color: #4B006E;
            margin-bottom: 5px;
        }

        .page-heading p {
            color: #777;
            margin: 0;
        }

        .settings-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
        }

        .settings-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
            border-top: 4px solid #D4AF37;
        }

        .settings-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 18px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .settings-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: #f2eaf7;
            color: #4B006E;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }

        .settings-card-header h3 {
            color: #4B006E;
            margin: 0 0 4px;
            font-size: 18px;
        }

        .settings-card-header p {
            color: #888;
            font-size: 13px;
            margin: 0;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            color: #444;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
            background: #fff;
            color: #333;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #4B006E;
            box-shadow: 0 0 0 3px rgba(75, 0, 110, .08);
        }

        .form-group small {
            display: block;
            color: #888;
            font-size: 11px;
            margin-top: 6px;
        }

        .settings-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-purple {
            background: #4B006E;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-purple:hover {
            background: #260033;
            color: #fff;
        }

        .btn-light {
            background: #f2eaf7;
            color: #4B006E;
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-light:hover {
            background: #e7d8ef;
            color: #4B006E;
        }

        .alert {
            padding: 14px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
        }

        .settings-note {
            background: #fff8e6;
            border-left: 4px solid #D4AF37;
            padding: 14px;
            border-radius: 6px;
            color: #725b20;
            font-size: 13px;
            margin-bottom: 20px;
        }

        @media(max-width: 650px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .settings-card {
                padding: 18px;
            }

            .settings-actions {
                flex-direction: column;
            }

            .settings-actions button,
            .settings-actions a {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <div class="page-heading">
            <div>
                <h1>
                    <i class="fas fa-cog"></i>
                    System Settings
                </h1>

                <p>
                    Manage your business information and reservation preferences.
                </p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= settingEscape($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= settingEscape($error) ?>
            </div>
        <?php endif; ?>

        <div class="settings-note">
            <i class="fas fa-info-circle"></i>
            Changes made here will be saved in your system settings database.
            Please verify your business contact information and currency before
            using the system for official transactions.
        </div>

        <form method="POST" action="settings.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= settingEscape($_SESSION['csrf_token']) ?>"
            >

            <div class="settings-container">

                <!-- Business Information -->
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="fas fa-building"></i>
                        </div>

                        <div>
                            <h3>Business Information</h3>
                            <p>Manage your hotel's name and public identity.</p>
                        </div>

                    </div>

                    <div class="settings-grid">

                        <div class="form-group full-width">
                            <label for="business_name">Business Name *</label>

                            <input
                                type="text"
                                id="business_name"
                                name="business_name"
                                maxlength="150"
                                required
                                value="<?= settingEscape($settings['business_name']) ?>"
                            >
                        </div>

                        <div class="form-group full-width">
                            <label for="tagline">Business Tagline</label>

                            <input
                                type="text"
                                id="tagline"
                                name="tagline"
                                maxlength="255"
                                value="<?= settingEscape($settings['tagline']) ?>"
                            >
                        </div>

                        <div class="form-group full-width">
                            <label for="address">Business Address</label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                maxlength="255"
                            ><?= settingEscape($settings['address']) ?></textarea>
                        </div>

                    </div>

                </section>

                <!-- Contact Information -->
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="fas fa-address-book"></i>
                        </div>

                        <div>
                            <h3>Contact Information</h3>
                            <p>Set the official business contact details.</p>
                        </div>

                    </div>

                    <div class="settings-grid">

                        <div class="form-group">
                            <label for="phone">Business Phone Number</label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                maxlength="30"
                                placeholder="Enter business phone number"
                                value="<?= settingEscape($settings['phone']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Business Email Address</label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                maxlength="150"
                                placeholder="Enter business email"
                                value="<?= settingEscape($settings['email']) ?>"
                            >
                        </div>

                    </div>

                </section>

                <!-- Currency and Payments -->
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>

                        <div>
                            <h3>Currency & Payment Preferences</h3>
                            <p>Select the currency used for financial records.</p>
                        </div>

                    </div>

                    <div class="settings-grid">

                        <div class="form-group">
                            <label for="currency">Business Currency</label>

                            <select id="currency" name="currency">

                                <option value="USD"
                                    <?= $settings['currency'] === 'USD' ? 'selected' : '' ?>>
                                    USD - US Dollar ($)
                                </option>

                                <option value="LRD"
                                    <?= $settings['currency'] === 'LRD' ? 'selected' : '' ?>>
                                    LRD - Liberian Dollar
                                </option>

                                <option value="RWF"
                                    <?= $settings['currency'] === 'RWF' ? 'selected' : '' ?>>
                                    RWF - Rwandan Franc
                                </option>

                                <option value="EUR"
                                    <?= $settings['currency'] === 'EUR' ? 'selected' : '' ?>>
                                    EUR - Euro (€)
                                </option>

                                <option value="GBP"
                                    <?= $settings['currency'] === 'GBP' ? 'selected' : '' ?>>
                                    GBP - British Pound (£)
                                </option>

                            </select>

                            <small>
                                This saves your preferred currency. It does not
                                automatically convert existing payment amounts.
                            </small>
                        </div>

                    </div>

                </section>

                <!-- Check-in and Check-out -->
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="fas fa-clock"></i>
                        </div>

                        <div>
                            <h3>Check-in & Check-out</h3>
                            <p>Set the standard arrival and departure times.</p>
                        </div>

                    </div>

                    <div class="settings-grid">

                        <div class="form-group">
                            <label for="check_in_time">Check-in Time</label>

                            <input
                                type="time"
                                id="check_in_time"
                                name="check_in_time"
                                required
                                value="<?= settingEscape($settings['check_in_time']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="check_out_time">Check-out Time</label>

                            <input
                                type="time"
                                id="check_out_time"
                                name="check_out_time"
                                required
                                value="<?= settingEscape($settings['check_out_time']) ?>"
                            >
                        </div>

                    </div>

                </section>

                <!-- Reservation Preferences -->
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>

                        <div>
                            <h3>Reservation Preferences</h3>
                            <p>Control basic booking limits and cancellation notice.</p>
                        </div>

                    </div>

                    <div class="settings-grid">

                        <div class="form-group">
                            <label for="advance_booking_days">
                                Maximum Advance Booking (Days)
                            </label>

                            <input
                                type="number"
                                id="advance_booking_days"
                                name="advance_booking_days"
                                min="1"
                                max="365"
                                required
                                value="<?= settingEscape($settings['advance_booking_days']) ?>"
                            >

                            <small>
                                Maximum number of days ahead a guest may book.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="max_guests_per_booking">
                                Maximum Guests Per Booking
                            </label>

                            <input
                                type="number"
                                id="max_guests_per_booking"
                                name="max_guests_per_booking"
                                min="1"
                                max="50"
                                required
                                value="<?= settingEscape($settings['max_guests_per_booking']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="cancellation_notice_hours">
                                Cancellation Notice (Hours)
                            </label>

                            <input
                                type="number"
                                id="cancellation_notice_hours"
                                name="cancellation_notice_hours"
                                min="0"
                                max="720"
                                required
                                value="<?= settingEscape($settings['cancellation_notice_hours']) ?>"
                            >

                            <small>
                                This stores your preferred cancellation notice
                                period. It does not yet enforce a cancellation policy.
                            </small>
                        </div>

                    </div>

                </section>

            </div>

            <div class="settings-actions">

                <a href="dashboard.php" class="btn-light">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>

                <button type="submit" class="btn-purple">
                    <i class="fas fa-save"></i>
                    Save Settings
                </button>

            </div>

        </form>

    </main>

</div>

</body>
</html>