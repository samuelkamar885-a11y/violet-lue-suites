<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'reports';

function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Validate date filters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$datePattern = '/^\d{4}-\d{2}-\d{2}$/';

if ($startDate !== '' && !preg_match($datePattern, $startDate)) {
    $startDate = '';
}

if ($endDate !== '' && !preg_match($datePattern, $endDate)) {
    $endDate = '';
}

if ($startDate !== '' && $endDate !== '' && $startDate > $endDate) {
    $dateError = 'The start date cannot be later than the end date.';
    $startDate = '';
    $endDate = '';
} else {
    $dateError = '';
}

// Prepare date conditions
$reservationDateCondition = '';
$paymentDateCondition = '';

$reservationParams = [];
$paymentParams = [];

if ($startDate !== '') {
    $reservationDateCondition .= " AND created_at >= :reservation_start";
    $paymentDateCondition .= " AND payment_date >= :payment_start";

    $reservationParams['reservation_start'] = $startDate . ' 00:00:00';
    $paymentParams['payment_start'] = $startDate . ' 00:00:00';
}

if ($endDate !== '') {
    $nextDay = date('Y-m-d', strtotime($endDate . ' +1 day'));

    $reservationDateCondition .= " AND created_at < :reservation_end";
    $paymentDateCondition .= " AND payment_date < :payment_end";

    $reservationParams['reservation_end'] = $nextDay . ' 00:00:00';
    $paymentParams['payment_end'] = $nextDay . ' 00:00:00';
}

// Default report data
$reservationStats = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0
];

$paymentStats = [
    'total' => 0,
    'pending' => 0,
    'paid' => 0,
    'failed' => 0,
    'refunded' => 0,
    'revenue' => 0,
    'refunded_amount' => 0
];

$totalGuests = 0;
$totalRooms = 0;
$paymentMethods = [];
$monthlyRevenue = [];
$recentReservations = [];
$error = '';

try {

    // Reservation statistics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(status = 'pending'), 0) AS pending,
            COALESCE(SUM(status = 'confirmed'), 0) AS confirmed,
            COALESCE(SUM(status = 'cancelled'), 0) AS cancelled
        FROM reservations
        WHERE 1 = 1
        $reservationDateCondition
    ");

    $stmt->execute($reservationParams);
    $reservationStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $reservationStats;

    // Payment statistics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(payment_status = 'pending'), 0) AS pending,
            COALESCE(SUM(payment_status = 'paid'), 0) AS paid,
            COALESCE(SUM(payment_status = 'failed'), 0) AS failed,
            COALESCE(SUM(payment_status = 'refunded'), 0) AS refunded,

            COALESCE(
                SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN amount
                        ELSE 0
                    END
                ), 0
            ) AS revenue,

            COALESCE(
                SUM(
                    CASE
                        WHEN payment_status = 'refunded' THEN amount
                        ELSE 0
                    END
                ), 0
            ) AS refunded_amount

        FROM payments
        WHERE 1 = 1
        $paymentDateCondition
    ");

    $stmt->execute($paymentParams);
    $paymentStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $paymentStats;

    // Guest total
    $totalGuests = (int)$pdo->query(
        "SELECT COUNT(*) FROM guests"
    )->fetchColumn();

    // Room total
    $totalRooms = (int)$pdo->query(
        "SELECT COUNT(*) FROM rooms"
    )->fetchColumn();

    // Payment method summary
    $stmt = $pdo->prepare("
        SELECT
            payment_method,
            COUNT(*) AS payment_count,
            COALESCE(
                SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN amount
                        ELSE 0
                    END
                ), 0
            ) AS total_amount
        FROM payments
        WHERE 1 = 1
        $paymentDateCondition
        GROUP BY payment_method
        ORDER BY total_amount DESC
    ");

    $stmt->execute($paymentParams);
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly revenue for the selected period
    $stmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(payment_date, '%Y-%m') AS revenue_month,
            COUNT(*) AS payment_count,
            SUM(amount) AS total_amount
        FROM payments
        WHERE payment_status = 'paid'
        AND payment_date IS NOT NULL
        $paymentDateCondition
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY revenue_month DESC
        LIMIT 12
    ");

    $stmt->execute($paymentParams);
    $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent reservations within the selected period
    $stmt = $pdo->prepare("
        SELECT
            booking_reference,
            full_name,
            room_type,
            check_in,
            check_out,
            guests,
            status,
            created_at
        FROM reservations
        WHERE 1 = 1
        $reservationDateCondition
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $stmt->execute($reservationParams);
    $recentReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Reports page error: ' . $e->getMessage());
    $error = 'Unable to generate reports. Please check your database tables.';
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && !$error) {

    $filename = 'violet_lue_report_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Excel UTF-8 support
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, ['VIOLET-LUE SUITES & APARTMENTS']);
    fputcsv($output, ['Management Report']);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, ['Start Date:', $startDate ?: 'All dates']);
    fputcsv($output, ['End Date:', $endDate ?: 'All dates']);
    fputcsv($output, []);

    fputcsv($output, ['RESERVATION SUMMARY']);
    fputcsv($output, ['Total Reservations', $reservationStats['total']]);
    fputcsv($output, ['Pending Reservations', $reservationStats['pending']]);
    fputcsv($output, ['Confirmed Reservations', $reservationStats['confirmed']]);
    fputcsv($output, ['Cancelled Reservations', $reservationStats['cancelled']]);
    fputcsv($output, []);

    fputcsv($output, ['PAYMENT SUMMARY']);
    fputcsv($output, ['Total Payment Records', $paymentStats['total']]);
    fputcsv($output, ['Paid Payments', $paymentStats['paid']]);
    fputcsv($output, ['Pending Payments', $paymentStats['pending']]);
    fputcsv($output, ['Failed Payments', $paymentStats['failed']]);
    fputcsv($output, ['Refunded Payments', $paymentStats['refunded']]);
    fputcsv($output, ['Total Revenue', $paymentStats['revenue']]);
    fputcsv($output, ['Refunded Amount', $paymentStats['refunded_amount']]);
    fputcsv($output, []);

    fputcsv($output, ['BUSINESS SUMMARY']);
    fputcsv($output, ['Total Registered Guests', $totalGuests]);
    fputcsv($output, ['Total Rooms and Apartments', $totalRooms]);
    fputcsv($output, []);

    fputcsv($output, ['PAYMENT METHODS']);
    fputcsv($output, ['Payment Method', 'Payment Count', 'Paid Amount']);

    foreach ($paymentMethods as $method) {
        fputcsv($output, [
            ucwords(str_replace('-', ' ', $method['payment_method'])),
            $method['payment_count'],
            $method['total_amount']
        ]);
    }

    fputcsv($output, []);
    fputcsv($output, ['MONTHLY REVENUE']);
    fputcsv($output, ['Month', 'Number of Payments', 'Revenue']);

    foreach ($monthlyRevenue as $month) {
        fputcsv($output, [
            $month['revenue_month'],
            $month['payment_count'],
            $month['total_amount']
        ]);
    }

    fputcsv($output, []);
    fputcsv($output, ['RECENT RESERVATIONS']);
    fputcsv($output, [
        'Booking Reference',
        'Guest Name',
        'Room Type',
        'Check-in',
        'Check-out',
        'Guests',
        'Status',
        'Created At'
    ]);

    foreach ($recentReservations as $reservation) {
        fputcsv($output, [
            $reservation['booking_reference'],
            $reservation['full_name'],
            $reservation['room_type'],
            $reservation['check_in'],
            $reservation['check_out'],
            $reservation['guests'],
            $reservation['status'],
            $reservation['created_at']
        ]);
    }

    fclose($output);
    exit;
}

function methodLabel($method) {
    return ucwords(str_replace('-', ' ', $method));
}

function reportStatusClass($status) {
    return match ($status) {
        'confirmed', 'paid' => 'success',
        'pending' => 'warning',
        'cancelled', 'failed' => 'danger',
        'refunded' => 'info',
        default => 'secondary'
    };
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports | VIOLET-LUE Suites & Apartments</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #FFFDF5;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .reports-container {
            max-width: 1500px;
            margin: 25px auto;
            padding: 0 20px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-heading h2 {
            color: #4B006E;
            margin-bottom: 6px;
        }

        .page-heading p {
            color: #777;
            margin: 0;
        }

        .report-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .report-actions a,
        .report-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border: none;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }

        .export-btn {
            background: #238636;
        }

        .print-btn {
            background: #4B006E;
        }

        .date-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }

        .date-filter h3 {
            color: #4B006E;
            margin-top: 0;
        }

        .date-filter form {
            display: flex;
            align-items: end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .date-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .date-field label {
            font-size: 13px;
            font-weight: bold;
            color: #555;
        }

        .date-field input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filter-button,
        .reset-button {
            padding: 11px 17px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .filter-button {
            background: #4B006E;
            color: white;
        }

        .reset-button {
            background: #eee;
            color: #333;
        }

        .report-section {
            margin-bottom: 28px;
        }

        .section-title {
            color: #4B006E;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
            gap: 17px;
        }

        .summary-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            border-left: 5px solid #D4AF37;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }

        .summary-card h4 {
            margin: 0 0 12px;
            color: #777;
            font-size: 14px;
            font-weight: normal;
        }

        .summary-card strong {
            font-size: 26px;
            color: #4B006E;
        }

        .summary-card i {
            color: #D4AF37;
            margin-right: 7px;
        }

        .report-panel {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
            margin-bottom: 25px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .report-table th {
            background: #260033;
            color: white;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status.warning {
            background: #fff3cd;
            color: #856404;
        }

        .status.danger {
            background: #f8d7da;
            color: #842029;
        }

        .status.info {
            background: #cfe2ff;
            color: #084298;
        }

        .status.secondary {
            background: #eee;
            color: #555;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .alert-error {
            padding: 13px;
            background: #f8d7da;
            color: #842029;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .empty-state {
            padding: 25px;
            text-align: center;
            color: #777;
        }

        .report-note {
            color: #777;
            font-size: 12px;
            margin-top: 12px;
        }

        @media(max-width: 600px) {
            .page-heading {
                align-items: stretch;
            }

            .report-actions {
                flex-direction: column;
            }

            .report-actions a,
            .report-actions button {
                width: 100%;
            }

            .date-filter form {
                align-items: stretch;
                flex-direction: column;
            }

            .date-field input,
            .filter-button,
            .reset-button {
                width: 100%;
            }

            .report-panel {
                padding: 15px;
            }
        }

        @media print {
            body {
                background: white;
            }

            .admin-sidebar,
            .admin-topbar,
            .date-filter,
            .report-actions {
                display: none !important;
            }

            .admin-main {
                margin: 0 !important;
                padding: 0 !important;
            }

            .reports-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .summary-card,
            .report-panel {
                box-shadow: none;
                border: 1px solid #ddd;
                break-inside: avoid;
            }

            .report-section {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <h1>Business Reports</h1>

            <div class="admin-user">
                Welcome, <?= escape($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </header>

        <div class="reports-container">

            <div class="page-heading">

                <div>
                    <h2>
                        <i class="fas fa-chart-line"></i>
                        Management Reports
                    </h2>

                    <p>Monitor reservations, guests, rooms and payments.</p>
                </div>

                <div class="report-actions">

                    <a
                        href="reports.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&export=csv"
                        class="export-btn"
                    >
                        <i class="fas fa-file-excel"></i>
                        Export Report
                    </a>

                    <button type="button" class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Print Report
                    </button>

                </div>

            </div>

            <?php if ($dateError): ?>
                <div class="alert-error"><?= escape($dateError) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error"><?= escape($error) ?></div>
            <?php endif; ?>

            <!-- Date Filter -->

            <section class="date-filter">

                <h3>
                    <i class="fas fa-calendar-alt"></i>
                    Filter Reports by Date
                </h3>

                <form method="GET" action="reports.php">

                    <div class="date-field">
                        <label for="start_date">Start Date</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?= escape($startDate) ?>"
                        >
                    </div>

                    <div class="date-field">
                        <label for="end_date">End Date</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?= escape($endDate) ?>"
                        >
                    </div>

                    <button type="submit" class="filter-button">
                        <i class="fas fa-filter"></i>
                        Generate Report
                    </button>

                    <a href="reports.php" class="reset-button">
                        <i class="fas fa-undo"></i>
                        Reset
                    </a>

                </form>

                <p class="report-note">
                    Leave both dates empty to display all available records.
                </p>

            </section>

            <!-- Reservation Summary -->

            <section class="report-section">

                <h3 class="section-title">
                    <i class="fas fa-calendar-check"></i>
                    Reservation Summary
                </h3>

                <div class="summary-grid">

                    <div class="summary-card">
                        <h4>Total Reservations</h4>
                        <strong><?= number_format((int)$reservationStats['total']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Pending Reservations</h4>
                        <strong><?= number_format((int)$reservationStats['pending']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Confirmed Reservations</h4>
                        <strong><?= number_format((int)$reservationStats['confirmed']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Cancelled Reservations</h4>
                        <strong><?= number_format((int)$reservationStats['cancelled']) ?></strong>
                    </div>

                </div>

            </section>

            <!-- Payment Summary -->

            <section class="report-section">

                <h3 class="section-title">
                    <i class="fas fa-money-bill-wave"></i>
                    Payment Summary
                </h3>

                <div class="summary-grid">

                    <div class="summary-card">
                        <h4>Total Payment Records</h4>
                        <strong><?= number_format((int)$paymentStats['total']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Successful Payments</h4>
                        <strong><?= number_format((int)$paymentStats['paid']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Pending Payments</h4>
                        <strong><?= number_format((int)$paymentStats['pending']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Failed Payments</h4>
                        <strong><?= number_format((int)$paymentStats['failed']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Refunded Payments</h4>
                        <strong><?= number_format((int)$paymentStats['refunded']) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Total Revenue</h4>
                        <strong>$<?= number_format((float)$paymentStats['revenue'], 2) ?></strong>
                    </div>

                </div>

                <p class="report-note">
                    Revenue includes payments marked as paid. Refunded amounts are shown separately in the export.
                </p>

            </section>

            <!-- Business Summary -->

            <section class="report-section">

                <h3 class="section-title">
                    <i class="fas fa-building"></i>
                    Business Overview
                </h3>

                <div class="summary-grid">

                    <div class="summary-card">
                        <h4>Total Registered Guests</h4>
                        <strong><?= number_format($totalGuests) ?></strong>
                    </div>

                    <div class="summary-card">
                        <h4>Total Rooms & Apartments</h4>
                        <strong><?= number_format($totalRooms) ?></strong>
                    </div>

                </div>

                <p class="report-note">
                    Guest and room totals represent all registered records and are not restricted by the selected date range.
                </p>

            </section>

            <!-- Payment Methods -->

            <section class="report-panel">

                <h3 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    Payment Method Summary
                </h3>

                <div class="table-wrapper">

                    <table class="report-table">

                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Number of Payments</th>
                                <th>Successful Payment Amount</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($paymentMethods)): ?>

                            <?php foreach ($paymentMethods as $method): ?>

                                <tr>
                                    <td><?= escape(methodLabel($method['payment_method'])) ?></td>

                                    <td>
                                        <?= number_format((int)$method['payment_count']) ?>
                                    </td>

                                    <td>
                                        $<?= number_format((float)$method['total_amount'], 2) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="empty-state">
                                    No payment method records found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

            <!-- Monthly Revenue -->

            <section class="report-panel">

                <h3 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Monthly Revenue
                </h3>

                <div class="table-wrapper">

                    <table class="report-table">

                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Successful Payments</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($monthlyRevenue)): ?>

                            <?php foreach ($monthlyRevenue as $month): ?>

                                <tr>
                                    <td>
                                        <?= escape(date('F Y', strtotime($month['revenue_month'] . '-01'))) ?>
                                    </td>

                                    <td>
                                        <?= number_format((int)$month['payment_count']) ?>
                                    </td>

                                    <td>
                                        $<?= number_format((float)$month['total_amount'], 2) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="empty-state">
                                    No monthly revenue records found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

            <!-- Recent Reservations -->

            <section class="report-panel">

                <h3 class="section-title">
                    <i class="fas fa-list"></i>
                    Recent Reservations
                </h3>

                <div class="table-wrapper">

                    <table class="report-table">

                        <thead>
                            <tr>
                                <th>Booking Reference</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($recentReservations)): ?>

                            <?php foreach ($recentReservations as $reservation): ?>

                                <tr>

                                    <td>
                                        <?= escape($reservation['booking_reference']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['full_name']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['room_type']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['check_in']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['check_out']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['guests']) ?>
                                    </td>

                                    <td>
                                        <span class="status <?= reportStatusClass($reservation['status']) ?>">
                                            <?= escape($reservation['status']) ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="empty-state">
                                    No reservations found for this report.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

            <p class="report-note">
                Report generated on <?= date('F d, Y h:i A') ?>.
                VIOLET-LUE Suites & Apartments — Your Comfort, Our Priority.
            </p>

        </div>

    </main>

</div>

</body>
</html>
