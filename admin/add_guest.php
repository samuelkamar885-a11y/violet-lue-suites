<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

$fullName = '';
$email = '';
$phone = '';
$nationality = '';
$address = '';
$notes = '';

// Handle guest registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } else {

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $nationality = trim($_POST['nationality'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validate required fields
        if ($fullName === '' || $phone === '') {
            $error = 'Guest full name and phone number are required.';
        } elseif (strlen($fullName) > 150) {
            $error = 'Guest name must not exceed 150 characters.';
        } elseif (strlen($phone) > 30) {
            $error = 'Phone number must not exceed 30 characters.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($email) > 150) {
            $error = 'Email address must not exceed 150 characters.';
        } elseif (strlen($nationality) > 100) {
            $error = 'Nationality must not exceed 100 characters.';
        } else {

            try {
                // Generate unique guest reference
                $guestReference = 'VLG-' .
                    date('Ymd') . '-' .
                    strtoupper(bin2hex(random_bytes(4)));

                $stmt = $pdo->prepare("
                    INSERT INTO guests (
                        guest_reference,
                        full_name,
                        email,
                        phone,
                        nationality,
                        address,
                        notes
                    ) VALUES (
                        :guest_reference,
                        :full_name,
                        :email,
                        :phone,
                        :nationality,
                        :address,
                        :notes
                    )
                ");

                $stmt->execute([
                    ':guest_reference' => $guestReference,
                    ':full_name' => $fullName,
                    ':email' => $email !== '' ? $email : null,
                    ':phone' => $phone,
                    ':nationality' => $nationality !== '' ? $nationality : null,
                    ':address' => $address !== '' ? $address : null,
                    ':notes' => $notes !== '' ? $notes : null
                ]);

                // Success message
                $_SESSION['guest_success'] =
                    'Guest registered successfully. Reference: ' . $guestReference;

                // Redirect to Guest Management
                header('Location: guests.php');
                exit;

            } catch (PDOException $e) {
                error_log('Guest registration error: ' . $e->getMessage());
                $error = 'Unable to register this guest. Please try again.';
            }
        }
    }
}

$activePage = 'guests';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Guest | Violet-Lue Suites & Apartments</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .page-header h1 {
            color: #4B006E;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #777;
            margin: 0;
        }

        .form-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            max-width: 1000px;
        }

        .form-section-title {
            color: #4B006E;
            font-size: 18px;
            font-weight: 700;
            margin: 10px 0 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .guest-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: #444;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #7A219E;
            box-shadow: 0 0 0 3px rgba(122, 33, 158, 0.10);
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .required {
            color: #dc3545;
        }

        .form-note {
            color: #777;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-submit,
        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-submit {
            background: #4B006E;
            color: #fff;
        }

        .btn-submit:hover {
            background: #260033;
        }

        .btn-cancel {
            background: #eee;
            color: #444;
        }

        .btn-cancel:hover {
            background: #ddd;
        }

        .error-message {
            background: #f8d7da;
            color: #842029;
            padding: 14px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        @media (max-width: 650px) {
            .guest-form-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <div>
                <h2>Guest Management</h2>
                <p>Register a new guest</p>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1>Register New Guest</h1>
                <p>Add guest information to the hotel management system.</p>
            </div>

            <a href="guests.php" class="btn-cancel">
                <i class="fas fa-arrow-left"></i>
                Back to Guests
            </a>
        </div>

        <div class="form-card">

            <?php if ($error !== ''): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <h3 class="form-section-title">
                    <i class="fas fa-user"></i>
                    Guest Information
                </h3>

                <div class="guest-form-grid">

                    <div class="form-group">
                        <label for="full_name">
                            Full Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            maxlength="150"
                            value="<?= htmlspecialchars($fullName) ?>"
                            placeholder="Enter guest's full name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            Phone Number <span class="required">*</span>
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            maxlength="30"
                            value="<?= htmlspecialchars($phone) ?>"
                            placeholder="Enter phone number"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            maxlength="150"
                            value="<?= htmlspecialchars($email) ?>"
                            placeholder="Enter email address"
                        >
                    </div>

                    <div class="form-group">
                        <label for="nationality">Nationality</label>

                        <input
                            type="text"
                            id="nationality"
                            name="nationality"
                            maxlength="100"
                            value="<?= htmlspecialchars($nationality) ?>"
                            placeholder="Enter nationality"
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Residential Address</label>

                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter guest's residential address"
                        ><?= htmlspecialchars($address) ?></textarea>
                    </div>

                </div>

                <h3 class="form-section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Additional Information
                </h3>

                <div class="guest-form-grid">

                    <div class="form-group full-width">
                        <label for="notes">Guest Notes</label>

                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Enter any additional information about this guest..."
                        ><?= htmlspecialchars($notes) ?></textarea>

                        <span class="form-note">
                            Optional information for internal hotel records.
                        </span>
                    </div>

                </div>

                <div class="form-actions">

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Register Guest
                    </button>

                    <a href="guests.php" class="btn-cancel">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>