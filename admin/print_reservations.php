<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the print page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get search and status filters
$search = trim($_GET['search'] ?? '');

$allowedStatuses = ['pending', 'confirmed', 'cancelled'];
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($statusFilter, array_merge(['all'], $allowedStatuses), true)) {
    $statusFilter = 'all';
}

// Build reservation query
$sql = "SELECT * FROM reservations WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND status = :status";
    $params['status'] = $statusFilter;
}

if ($search !== '') {
    $sql .= " AND (
        booking_reference LIKE :search
        OR full_name LIKE :search
        OR email LIKE :search
        OR phone LIKE :search
    )";

    $params['search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$totalReservations = count($reservations);

function escapePrint($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatRoomType($room) {
    $rooms = [
        'luxury-room' => 'Luxury Room',
        'self-contained-apartment' => 'Self-Contained Apartment'
    ];

    return $rooms[$room] ?? ucwords(str_replace('-', ' ', $room));
}

$printedBy = $_SESSION['admin_name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Print Reservations | VIOLET-LUE Suites & Apartments</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f3f0f5;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .print-container {
            max-width: 1200px;
            margin: auto;
            padding: 35px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .print-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 3px solid #D4AF37;
            text-align: left;
        }

        .print-header img {
            width: 95px;
            height: 95px;
            object-fit: contain;
        }

        .brand h1 {
            margin: 0;
            color: #4B006E;
            font-size: 27px;
            letter-spacing: 2px;
        }

        .brand h2 {
            margin: 5px 0;
            color: #D4AF37;
            font-size: 15px;
            letter-spacing: 2px;
        }

        .brand p {
            margin: 5px 0 0;
            color: #666;
            font-size: 13px;
        }

        .report-title {
            margin: 30px 0 8px;
            color: #4B006E;
            text-align: center;
            font-size: 24px;
        }

        .report-subtitle {
            margin-bottom: 25px;
            color: #666;
            text-align: center;
            font-size: 13px;
        }

        .report-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding: 15px;
            background: #faf7fc;
            border-left: 4px solid #D4AF37;
            font-size: 13px;
        }

        .report-info strong {
            color: #4B006E;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            padding: 12px 8px;
            background: #4B006E;
            color: #fff;
            text-align: left;
            border: 1px solid #4B006E;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #faf7fc;
        }

        .status {
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.pending {
            color: #b7791f;
        }

        .status.confirmed {
            color: #16803c;
        }

        .status.cancelled {
            color: #c53030;
        }

        .empty-message {
            padding: 30px;
            text-align: center;
            color: #777;
        }

        .print-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            color: #777;
            text-align: center;
            font-size: 12px;
        }

        .print-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .print-actions button {
            padding: 11px 22px;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        .print-button {
            background: #4B006E;
        }

        .back-button {
            background: #666;
        }

        @media print {
            @page {
                size: landscape;
                margin: 12mm;
            }

            body {
                padding: 0;
                background: #fff;
            }

            .print-container {
                max-width: none;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .print-actions {
                display: none !important;
            }

            th {
                background: #4B006E !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tbody tr:nth-child(even) {
                background: #faf7fc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 700px) {
            body {
                padding: 10px;
            }

            .print-container {
                padding: 15px;
            }

            .print-header {
                flex-direction: column;
                text-align: center;
            }

            .report-info {
                flex-direction: column;
            }

            .print-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

<div class="print-container">

    <div class="print-actions">
        <button class="print-button" onclick="window.print()">
            Print Reservations
        </button>

        <button class="back-button" onclick="window.location.href='dashboard.php'">
            Back to Dashboard
        </button>
    </div>

    <header class="print-header">
        <img src="../assets/images/logo.jpeg"
             alt="Violet-Lue Logo">

        <div class="brand">
            <h1>VIOLET-LUE</h1>
            <h2>SUITES & APARTMENTS</h2>
            <p>Your Comfort, Our Priority.</p>
            <p>Monrovia, Liberia</p>
        </div>
    </header>

    <h2 class="report-title">GUEST RESERVATION REPORT</h2>

    <p class="report-subtitle">
        Stay Relaxed. Feel at Home.
    </p>

    <div class="report-info">

        <div>
            <strong>Reservation Status:</strong>
            <?= escapePrint(ucfirst($statusFilter)) ?>
        </div>

        <div>
            <strong>Search:</strong>
            <?= $search !== '' ? escapePrint($search) : 'All Reservations' ?>
        </div>

        <div>
            <strong>Total Records:</strong>
            <?= $totalReservations ?>
        </div>

        <div>
            <strong>Printed On:</strong>
            <?= date('d M Y, h:i A') ?>
        </div>

        <div>
            <strong>Printed By:</strong>
            <?= escapePrint($printedBy) ?>
        </div>

    </div>

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking Reference</th>
                    <th>Guest Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Guests</th>
                    <th>Special Requests</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($reservations)): ?>

                    <?php foreach ($reservations as $index => $reservation): ?>

                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <?= escapePrint($reservation['booking_reference']) ?>
                            </td>

                            <td>
                                <?= escapePrint($reservation['full_name']) ?>
                            </td>

                            <td>
                                <?= escapePrint($reservation['email']) ?>
                            </td>

                            <td>
                                <?= escapePrint($reservation['phone']) ?>
                            </td>

                            <td>
                                <?= escapePrint(formatRoomType($reservation['room_type'])) ?>
                            </td>

                            <td>
                                <?= escapePrint(date('d M Y', strtotime($reservation['check_in']))) ?>
                            </td>

                            <td>
                                <?= escapePrint(date('d M Y', strtotime($reservation['check_out']))) ?>
                            </td>

                            <td>
                                <?= (int)$reservation['guests'] ?>
                            </td>

                            <td>
                                <?= escapePrint($reservation['special_requests'] ?: 'None') ?>
                            </td>

                            <td class="status <?= escapePrint($reservation['status']) ?>">
                                <?= escapePrint($reservation['status']) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="11" class="empty-message">
                            No reservation records found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <footer class="print-footer">
        <p>
            &copy; <?= date('Y') ?> VIOLET-LUE Suites & Apartments.
            All Rights Reserved.
        </p>

        <p>
            Your Comfort, Our Priority.
        </p>
    </footer>

</div>

</body>
</html>
