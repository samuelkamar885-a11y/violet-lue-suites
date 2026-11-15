<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect administrator page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get reservation ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid reservation ID.');
}

// Fetch reservation
$stmt = $pdo->prepare("
    SELECT *
    FROM reservations
    WHERE id = :id
    LIMIT 1
");

$stmt->execute(['id' => $id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    exit('Reservation not found.');
}

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Reservation | VIOLET-LUE</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        body {
            background: #FFFDF5;
        }

        .reservation-details {
            max-width: 850px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        }

        .reservation-details h2 {
            color: #4B006E;
            margin-bottom: 25px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .detail-item {
            padding: 15px;
            background: #FFFDF5;
            border-radius: 6px;
            border-left: 3px solid #D4AF37;
            overflow-wrap: anywhere;
        }

        .detail-item small {
            display: block;
            color: #777;
            margin-bottom: 7px;
        }

        .detail-item strong {
            color: #333;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            text-transform: capitalize;
            background: #eee;
        }

        .status.pending {
            background: #fff0c2;
            color: #805b00;
        }

        .status.confirmed {
            background: #d9f5df;
            color: #176b2b;
        }

        .status.cancelled {
            background: #ffe0e0;
            color: #a00000;
        }

        .back-button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background: #4B006E;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .reservation-details {
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
            <h1>Reservation Details</h1>
            <div class="admin-user">
                Welcome, <?= escape($_SESSION['admin_name']) ?>
            </div>
        </header>

        <section class="reservation-details">

            <h2>
                Booking: <?= escape($reservation['booking_reference']) ?>
            </h2>

            <div class="details-grid">

                <div class="detail-item">
                    <small>Guest Full Name</small>
                    <strong><?= escape($reservation['full_name']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Email Address</small>
                    <strong><?= escape($reservation['email']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Phone Number</small>
                    <strong><?= escape($reservation['phone']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Room Type</small>
                    <strong><?= escape($reservation['room_type']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Check-in Date</small>
                    <strong><?= escape($reservation['check_in']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Check-out Date</small>
                    <strong><?= escape($reservation['check_out']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Number of Guests</small>
                    <strong><?= escape($reservation['guests']) ?></strong>
                </div>

                <div class="detail-item">
                    <small>Reservation Status</small>
                    <strong>
                        <span class="status <?= escape($reservation['status']) ?>">
                            <?= escape($reservation['status']) ?>
                        </span>
                    </strong>
                </div>

                <div class="detail-item full-width">
                    <small>Special Requests</small>
                    <strong>
                        <?= $reservation['special_requests'] !== null && $reservation['special_requests'] !== ''
                            ? nl2br(escape($reservation['special_requests']))
                            : 'No special requests.' ?>
                    </strong>
                </div>

                <div class="detail-item full-width">
                    <small>Booking Created</small>
                    <strong><?= escape($reservation['created_at']) ?></strong>
                </div>

            </div>

            <a href="dashboard.php" class="back-button">
                Back to Dashboard
            </a>

        </section>

    </main>

</div>

</body>
</html>
