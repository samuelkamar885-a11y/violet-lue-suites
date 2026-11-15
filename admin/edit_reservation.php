<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect administrator page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Get reservation ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid reservation ID.');
}

// Fetch reservation
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    exit('Reservation not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Invalid request. Please refresh the page.';

    } else {

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roomType = $_POST['room_type'] ?? '';
        $checkIn = $_POST['check_in'] ?? '';
        $checkOut = $_POST['check_out'] ?? '';
        $guests = filter_var($_POST['guests'] ?? '', FILTER_VALIDATE_INT);
        $specialRequests = trim($_POST['special_requests'] ?? '');
        $status = $_POST['status'] ?? '';

        $allowedRooms = [
            'luxury-room',
            'self-contained-apartment'
        ];

        $allowedStatuses = [
            'pending',
            'confirmed',
            'cancelled'
        ];

        $validCheckIn = DateTime::createFromFormat('!Y-m-d', $checkIn);
        $validCheckOut = DateTime::createFromFormat('!Y-m-d', $checkOut);

        $validDates =
            $validCheckIn &&
            $validCheckOut &&
            $validCheckIn->format('Y-m-d') === $checkIn &&
            $validCheckOut->format('Y-m-d') === $checkOut &&
            $validCheckOut > $validCheckIn;

        if ($fullName === '' || strlen($fullName) > 150) {
            $error = 'Please enter a valid guest name.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $error = 'Please enter a valid email address.';

        } elseif ($phone === '' || strlen($phone) > 30) {
            $error = 'Please enter a valid phone number.';

        } elseif (!in_array($roomType, $allowedRooms, true)) {
            $error = 'Please select a valid room type.';

        } elseif (!$validDates) {
            $error = 'Please enter valid dates. Check-out must be after check-in.';

        } elseif (!$guests || $guests < 1) {
            $error = 'Please enter at least one guest.';

        } elseif (strlen($specialRequests) > 5000) {
            $error = 'Special requests cannot exceed 5000 characters.';

        } elseif (!in_array($status, $allowedStatuses, true)) {
            $error = 'Please select a valid reservation status.';

        } else {

            try {
                $update = $pdo->prepare("
                    UPDATE reservations SET
                        full_name = :full_name,
                        email = :email,
                        phone = :phone,
                        room_type = :room_type,
                        check_in = :check_in,
                        check_out = :check_out,
                        guests = :guests,
                        special_requests = :special_requests,
                        status = :status
                    WHERE id = :id
                ");

                $update->execute([
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'room_type' => $roomType,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $guests,
                    'special_requests' => $specialRequests,
                    'status' => $status,
                    'id' => $id
                ]);

                header('Location: view_reservation.php?id=' . $id . '&updated=1');
                exit;

            } catch (PDOException $e) {
                error_log('Reservation edit error: ' . $e->getMessage());
                $error = 'Unable to update reservation. Please try again.';
            }
        }

        // Keep submitted values visible if validation fails
        $reservation['full_name'] = $fullName;
        $reservation['email'] = $email;
        $reservation['phone'] = $phone;
        $reservation['room_type'] = $roomType;
        $reservation['check_in'] = $checkIn;
        $reservation['check_out'] = $checkOut;
        $reservation['guests'] = $_POST['guests'] ?? '';
        $reservation['special_requests'] = $specialRequests;
        $reservation['status'] = $status;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Reservation | VIOLET-LUE</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        body {
            background: #FFFDF5;
        }

        .edit-container {
            max-width: 850px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        }

        .edit-container h2 {
            color: #4B006E;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .save-button,
        .back-button {
            display: inline-block;
            padding: 12px 22px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .save-button {
            background: #4B006E;
            color: white;
        }

        .back-button {
            background: #eeeeee;
            color: #333;
            margin-left: 8px;
        }

        .error-message {
            padding: 13px;
            margin-bottom: 20px;
            background: #ffe0e0;
            color: #a00000;
            border-radius: 5px;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .edit-container {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php
    $activePage = 'reservations';
    require_once __DIR__ . '/includes/sidebar.php';
    ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <h1>Edit Reservation</h1>
            <div class="admin-user">
                Welcome, <?= escape($_SESSION['admin_name']) ?>
            </div>
        </header>

        <section class="edit-container">

            <h2>
                Booking: <?= escape($reservation['booking_reference']) ?>
            </h2>

            <?php if ($error !== ''): ?>
                <div class="error-message">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input type="hidden" name="csrf_token"
                       value="<?= escape($_SESSION['csrf_token']) ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label for="full_name">Guest Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                               maxlength="150" required
                               value="<?= escape($reservation['full_name']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               maxlength="150" required
                               value="<?= escape($reservation['email']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                               maxlength="30" required
                               value="<?= escape($reservation['phone']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="room_type">Room Type</label>
                        <select id="room_type" name="room_type" required>
                            <option value="luxury-room"
                                <?= $reservation['room_type'] === 'luxury-room' ? 'selected' : '' ?>>
                                Luxury Room
                            </option>

                            <option value="self-contained-apartment"
                                <?= $reservation['room_type'] === 'self-contained-apartment' ? 'selected' : '' ?>>
                                Self-Contained Apartment
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in"
                               required value="<?= escape($reservation['check_in']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out"
                               required value="<?= escape($reservation['check_out']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests</label>
                        <input type="number" id="guests" name="guests"
                               min="1" required
                               value="<?= escape($reservation['guests']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Reservation Status</label>
                        <select id="status" name="status" required>
                            <option value="pending"
                                <?= $reservation['status'] === 'pending' ? 'selected' : '' ?>>
                                Pending
                            </option>

                            <option value="confirmed"
                                <?= $reservation['status'] === 'confirmed' ? 'selected' : '' ?>>
                                Confirmed
                            </option>

                            <option value="cancelled"
                                <?= $reservation['status'] === 'cancelled' ? 'selected' : '' ?>>
                                Cancelled
                            </option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests"
                                  name="special_requests"
                                  maxlength="5000"><?= escape($reservation['special_requests']) ?></textarea>
                    </div>

                </div>

                <br>

                <button type="submit" class="save-button">
                    Save Changes
                </button>

                <a href="view_reservation.php?id=<?= (int) $id ?>"
                   class="back-button">
                    Cancel
                </a>

            </form>

        </section>

    </main>

</div>

</body>
</html>
