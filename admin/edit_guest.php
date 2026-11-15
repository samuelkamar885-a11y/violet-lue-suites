<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Validate guest ID
$guestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$guestId || $guestId < 1) {
    header('Location: guests.php?error=invalid_guest');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Load guest
try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM guests
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $guestId]);
    $guest = $stmt->fetch();

    if (!$guest) {
        header('Location: guests.php?error=guest_not_found');
        exit;
    }

} catch (PDOException $e) {
    error_log('Load guest for editing error: ' . $e->getMessage());
    exit('Unable to load guest information.');
}

// Initialize form values
$fullName = $guest['full_name'];
$email = $guest['email'] ?? '';
$phone = $guest['phone'];
$nationality = $guest['nationality'] ?? '';
$address = $guest['address'] ?? '';
$notes = $guest['notes'] ?? '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
                $stmt = $pdo->prepare("
                    UPDATE guests
                    SET
                        full_name = :full_name,
                        email = :email,
                        phone = :phone,
                        nationality = :nationality,
                        address = :address,
                        notes = :notes
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':full_name' => $fullName,
                    ':email' => $email !== '' ? $email : null,
                    ':phone' => $phone,
                    ':nationality' => $nationality !== '' ? $nationality : null,
                    ':address' => $address !== '' ? $address : null,
                    ':notes' => $notes !== '' ? $notes : null,
                    ':id' => $guestId
                ]);

                $_SESSION['guest_success'] =
                    'Guest information updated successfully.';

                header('Location: view_guest.php?id=' . $guestId);
                exit;

            } catch (PDOException $e) {
                error_log('Update guest error: ' . $e->getMessage());
                $error = 'Unable to update guest information. Please try again.';
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

    <title>Edit Guest | Violet-Lue Suites & Apartments</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
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

        .guest-reference {
            display: inline-block;
            background: #f3e8f7;
            color: #4B006E;
            padding: 9px 13px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 22px;
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
        .btn-cancel,
        .btn-view {
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

        .btn-view {
            background: #D4AF37;
            color: #260033;
        }

        .btn-view:hover {
            background: #F8D568;
            color: #260033;
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
                <p>Update guest information</p>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1>Edit Guest</h1>
                <p>Update the guest's personal and contact information.</p>
            </div>

            <a href="guests.php" class="btn-cancel">
                <i class="fas fa-arrow-left"></i>
                Back to Guests
            </a>
        </div>

        <div class="form-card">

            <div class="guest-reference">
                <i class="fas fa-id-card"></i>
                Guest Reference:
                <?= htmlspecialchars($guest['guest_reference']) ?>
            </div>

            <?php if ($error !== ''): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="edit_guest.php?id=<?= (int)$guestId ?>">

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
                            placeholder="Enter additional information about this guest..."
                        ><?= htmlspecialchars($notes) ?></textarea>

                        <span class="form-note">
                            Optional information for internal hotel records.
                        </span>
                    </div>

                </div>

                <div class="form-actions">

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>

                    <a href="view_guest.php?id=<?= (int)$guestId ?>"
                       class="btn-view">
                        <i class="fas fa-eye"></i>
                        View Guest
                    </a>

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