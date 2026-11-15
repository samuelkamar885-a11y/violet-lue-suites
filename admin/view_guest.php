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

// Fetch guest details and reservation history
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

    // Fetch all reservations belonging to this guest
    $reservationStmt = $pdo->prepare("
        SELECT
            id,
            booking_reference,
            room_type,
            check_in,
            check_out,
            guests,
            status,
            created_at
        FROM reservations
        WHERE guest_id = :guest_id
        ORDER BY created_at DESC
    ");

    $reservationStmt->execute([':guest_id' => $guestId]);
    $reservations = $reservationStmt->fetchAll();

} catch (PDOException $e) {
    error_log('View guest error: ' . $e->getMessage());
    exit('Unable to load guest information. Please try again later.');
}

$activePage = 'guests';

// Format dates
$registeredDate = !empty($guest['created_at'])
    ? date('F d, Y - h:i A', strtotime($guest['created_at']))
    : 'Not available';

$updatedDate = !empty($guest['updated_at'])
    ? date('F d, Y - h:i A', strtotime($guest['updated_at']))
    : 'Not available';

// Format room type
function formatRoomType($type)
{
    $types = [
        'luxury-room' => 'Luxury Room',
        'self-contained-apartment' => 'Self-Contained Apartment'
    ];

    return $types[$type] ?? ucwords(str_replace('-', ' ', $type));
}

// Format booking status
function getStatusClass($status)
{
    $classes = [
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'cancelled' => 'cancelled'
    ];

    return $classes[$status] ?? 'pending';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guest Details | Violet-Lue Suites & Apartments</title>

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

        .guest-profile-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            max-width: 1100px;
        }

        .guest-profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
            margin-bottom: 25px;
        }

        .guest-avatar {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: #4B006E;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            flex-shrink: 0;
        }

        .guest-profile-header h2 {
            color: #4B006E;
            margin: 0 0 8px;
            font-size: 23px;
        }

        .guest-reference {
            color: #777;
            font-size: 14px;
        }

        .guest-section-title {
            color: #4B006E;
            font-size: 17px;
            font-weight: 700;
            margin: 30px 0 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .guest-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .detail-item {
            padding: 5px 0;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .detail-label {
            display: block;
            color: #888;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .detail-value {
            color: #333;
            font-size: 15px;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .detail-value.empty {
            color: #aaa;
            font-style: italic;
        }

        /* Reservation history */
        .reservation-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .reservation-count {
            background: #F4E8FA;
            color: #4B006E;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .reservation-table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #eee;
            border-radius: 9px;
        }

        .reservation-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .reservation-table th {
            background: #4B006E;
            color: #fff;
            padding: 14px 12px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .reservation-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #eee;
            color: #444;
            font-size: 13px;
            vertical-align: middle;
        }

        .reservation-table tbody tr:hover {
            background: #FCF8FF;
        }

        .reservation-table tbody tr:last-child td {
            border-bottom: none;
        }

        .booking-reference {
            color: #4B006E;
            font-weight: 700;
            white-space: nowrap;
        }

        .booking-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .booking-status.pending {
            background: #FFF3CD;
            color: #856404;
        }

        .booking-status.confirmed {
            background: #D4EDDA;
            color: #155724;
        }

        .booking-status.cancelled {
            background: #F8D7DA;
            color: #721C24;
        }

        .empty-reservations {
            text-align: center;
            padding: 35px 20px;
            background: #FCF9FD;
            border: 1px dashed #D8C5E0;
            border-radius: 9px;
            color: #777;
        }

        .empty-reservations i {
            display: block;
            font-size: 35px;
            color: #B99BC8;
            margin-bottom: 12px;
        }

        .empty-reservations h4 {
            color: #4B006E;
            margin-bottom: 7px;
        }

        .guest-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 22px;
            border-top: 1px solid #eee;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .edit-btn {
            background: #4B006E;
            color: #fff;
        }

        .edit-btn:hover {
            background: #260033;
            color: #fff;
        }

        .back-btn {
            background: #eee;
            color: #444;
        }

        .back-btn:hover {
            background: #ddd;
            color: #333;
        }

        .print-btn {
            background: #D4AF37;
            color: #260033;
        }

        .print-btn:hover {
            background: #F8D568;
            color: #260033;
        }

        @media (max-width: 650px) {
            .guest-details-grid {
                grid-template-columns: 1fr;
            }

            .guest-profile-card {
                padding: 20px;
            }

            .guest-profile-header {
                align-items: flex-start;
            }

            .guest-avatar {
                width: 65px;
                height: 65px;
                font-size: 25px;
            }
        }

        @media print {
            .admin-sidebar,
            .admin-topbar,
            .page-header,
            .guest-actions {
                display: none !important;
            }

            .admin-main {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .guest-profile-card {
                box-shadow: none;
                max-width: 100%;
            }

            .reservation-table-wrapper {
                overflow: visible;
            }

            .reservation-table {
                min-width: 100%;
            }

            body {
                background: #fff !important;
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
                <p>View guest information and reservation history</p>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1>Guest Details</h1>
                <p>Complete information about this registered guest.</p>
            </div>

            <a href="guests.php" class="action-btn back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Guests
            </a>
        </div>

        <div class="guest-profile-card">

            <div class="guest-profile-header">

                <div class="guest-avatar">
                    <i class="fas fa-user"></i>
                </div>

                <div>
                    <h2>
                        <?= htmlspecialchars($guest['full_name']) ?>
                    </h2>

                    <div class="guest-reference">
                        Guest Reference:
                        <strong>
                            <?= htmlspecialchars($guest['guest_reference']) ?>
                        </strong>
                    </div>
                </div>

            </div>

            <!-- PERSONAL INFORMATION -->
            <h3 class="guest-section-title">
                <i class="fas fa-user-circle"></i>
                Personal Information
            </h3>

            <div class="guest-details-grid">

                <div class="detail-item">
                    <span class="detail-label">Full Name</span>
                    <div class="detail-value">
                        <?= htmlspecialchars($guest['full_name']) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Guest Reference</span>
                    <div class="detail-value">
                        <?= htmlspecialchars($guest['guest_reference']) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Phone Number</span>
                    <div class="detail-value">
                        <?= htmlspecialchars($guest['phone']) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Email Address</span>
                    <div class="detail-value <?= empty($guest['email']) ? 'empty' : '' ?>">
                        <?= !empty($guest['email'])
                            ? htmlspecialchars($guest['email'])
                            : 'Not provided' ?>
                    </div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Nationality</span>
                    <div class="detail-value <?= empty($guest['nationality']) ? 'empty' : '' ?>">
                        <?= !empty($guest['nationality'])
                            ? htmlspecialchars($guest['nationality'])
                            : 'Not provided' ?>
                    </div>
                </div>

                <div class="detail-item full-width">
                    <span class="detail-label">Residential Address</span>
                    <div class="detail-value <?= empty($guest['address']) ? 'empty' : '' ?>">
                        <?= !empty($guest['address'])
                            ? nl2br(htmlspecialchars($guest['address']))
                            : 'Not provided' ?>
                    </div>
                </div>

            </div>

            <!-- ADDITIONAL INFORMATION -->
            <h3 class="guest-section-title">
                <i class="fas fa-clipboard-list"></i>
                Additional Information
            </h3>

            <div class="guest-details-grid">

                <div class="detail-item full-width">
                    <span class="detail-label">Guest Notes</span>
                    <div class="detail-value <?= empty($guest['notes']) ? 'empty' : '' ?>">
                        <?= !empty($guest['notes'])
                            ? nl2br(htmlspecialchars($guest['notes']))
                            : 'No additional notes' ?>
                    </div>
                </div>

            </div>

            <!-- RESERVATION HISTORY -->
            <h3 class="guest-section-title">
                <i class="fas fa-calendar-check"></i>
                Reservation History
            </h3>

            <div class="reservation-summary">
                <span class="reservation-count">
                    <?= count($reservations) ?>
                    <?= count($reservations) === 1 ? 'Reservation' : 'Reservations' ?>
                </span>
            </div>

            <?php if (!empty($reservations)): ?>

                <div class="reservation-table-wrapper">

                    <table class="reservation-table">

                        <thead>
                            <tr>
                                <th>Booking Reference</th>
                                <th>Room Type</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Date Booked</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($reservations as $reservation): ?>

                                <tr>

                                    <td>
                                        <span class="booking-reference">
                                            <?= htmlspecialchars($reservation['booking_reference']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(formatRoomType($reservation['room_type'])) ?>
                                    </td>

                                    <td>
                                        <?= date('M d, Y', strtotime($reservation['check_in'])) ?>
                                    </td>

                                    <td>
                                        <?= date('M d, Y', strtotime($reservation['check_out'])) ?>
                                    </td>

                                    <td>
                                        <?= (int)$reservation['guests'] ?>
                                    </td>

                                    <td>
                                        <span class="booking-status <?= getStatusClass($reservation['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($reservation['status'])) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= !empty($reservation['created_at'])
                                            ? date('M d, Y', strtotime($reservation['created_at']))
                                            : 'Not available' ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-reservations">
                    <i class="fas fa-calendar-xmark"></i>

                    <h4>No Reservations Found</h4>

                    <p>This guest does not have any linked reservations yet.</p>
                </div>

            <?php endif; ?>

            <!-- REGISTRATION INFORMATION -->
            <h3 class="guest-section-title">
                <i class="fas fa-clock"></i>
                Registration Information
            </h3>

            <div class="guest-details-grid">

                <div class="detail-item">
                    <span class="detail-label">Date Registered</span>
                    <div class="detail-value">
                        <?= htmlspecialchars($registeredDate) ?>
                    </div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <div class="detail-value">
                        <?= htmlspecialchars($updatedDate) ?>
                    </div>
                </div>

            </div>

            <!-- ACTIONS -->
            <div class="guest-actions">

                <a href="edit_guest.php?id=<?= (int)$guest['id'] ?>"
                   class="action-btn edit-btn">
                    <i class="fas fa-edit"></i>
                    Edit Guest
                </a>

                <button type="button"
                        onclick="window.print()"
                        class="action-btn print-btn">
                    <i class="fas fa-print"></i>
                    Print Guest Details
                </button>

                <a href="guests.php" class="action-btn back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Guests
                </a>

            </div>

        </div>

    </main>

</div>

</body>
</html>
