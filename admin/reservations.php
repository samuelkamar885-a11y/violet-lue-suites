<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escape($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = '';

// Handle reservation status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $reservationId = filter_input(
        INPUT_POST,
        'reservation_id',
        FILTER_VALIDATE_INT
    );
    $newStatus = $_POST['status'] ?? '';

    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $error = 'Invalid request. Please refresh the page.';
    } elseif (
        !$reservationId ||
        !in_array($newStatus, ['confirmed', 'cancelled'], true)
    ) {
        $error = 'Invalid reservation update.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE reservations
                SET status = :status
                WHERE id = :id
                AND status = 'pending'
            ");

            $stmt->execute([
                'status' => $newStatus,
                'id' => $reservationId
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['reservation_success'] =
                    'Reservation status updated successfully.';

                header('Location: reservations.php');
                exit;
            }

            $error = 'Reservation was not updated. It may already have been processed.';
        } catch (PDOException $e) {
            error_log('Reservation update error: ' . $e->getMessage());
            $error = 'Unable to update reservation. Please try again.';
        }
    }
}

// Display success messages
$success = $_SESSION['reservation_success'] ?? '';
unset($_SESSION['reservation_success']);

if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $success = 'Reservation deleted successfully.';
}

// Deletion errors
if (isset($_GET['error'])) {
    $errorMessages = [
        'invalid_request' => 'Invalid request. Please try again.',
        'invalid_reservation' => 'Invalid reservation selected.',
        'not_found' => 'The reservation could not be found.',
        'delete_failed' => 'Unable to delete reservation. Please try again.'
    ];

    $error = $errorMessages[$_GET['error']] ?? 'An error occurred.';
}

// Dashboard statistics
$totalReservations = (int) $pdo->query(
    "SELECT COUNT(*) FROM reservations"
)->fetchColumn();

$pendingReservations = (int) $pdo->query(
    "SELECT COUNT(*) FROM reservations WHERE status = 'pending'"
)->fetchColumn();

$confirmedReservations = (int) $pdo->query(
    "SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'"
)->fetchColumn();

$cancelledReservations = (int) $pdo->query(
    "SELECT COUNT(*) FROM reservations WHERE status = 'cancelled'"
)->fetchColumn();

// Search and filter
$search = trim($_GET['search'] ?? '');

$allowedStatuses = ['pending', 'confirmed', 'cancelled'];
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($statusFilter, array_merge(['all'], $allowedStatuses), true)) {
    $statusFilter = 'all';
}

// Fetch reservations
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

    $params['search'] = '%' . $search . '%';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reservations Management | VIOLET-LUE</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #FFFDF5;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .reservations-container {
            max-width: 1500px;
            margin: 30px auto;
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
            margin: 0 0 8px;
        }

        .page-heading p {
            color: #777;
            margin: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 9px;
            border-left: 5px solid #D4AF37;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        .stat-card h3 {
            margin: 0 0 12px;
            color: #777;
            font-size: 14px;
        }

        .stat-card strong {
            color: #4B006E;
            font-size: 29px;
        }

        .stat-icon {
            color: #D4AF37;
            margin-right: 7px;
        }

        .reservation-panel {
            background: white;
            padding: 22px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .panel-heading h2 {
            color: #4B006E;
            margin: 0;
        }

        .report-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .report-buttons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .export-btn {
            background: #238636;
        }

        .print-btn {
            background: #4B006E;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin: 22px 0;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-form button {
            padding: 12px 20px;
            background: #4B006E;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 10px 15px;
            border: 1px solid #4B006E;
            border-radius: 5px;
            color: #4B006E;
            background: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .filter-btn.active {
            background: #4B006E;
            color: white;
        }

        .filter-btn span {
            margin-left: 5px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #260033;
            color: white;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
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

        .reservation-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .reservation-actions a,
        .reservation-actions button,
        .action-form button {
            display: inline-block;
            padding: 7px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .view-btn {
            background: #2563a6;
        }

        .edit-btn {
            background: #d18b00;
        }

        .delete-btn,
        .cancel-btn {
            background: #b42318;
        }

        .confirm-btn {
            background: #238636;
        }

        .action-form {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .message.success {
            background: #d9f5df;
            color: #176b2b;
        }

        .message.error {
            background: #ffe0e0;
            color: #a00000;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        @media (max-width: 600px) {
            .search-form {
                flex-direction: column;
            }

            .reservation-panel {
                padding: 15px;
            }

            .panel-heading {
                align-items: stretch;
            }

            .report-buttons {
                flex-direction: column;
            }

            .report-buttons a {
                width: 100%;
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
            <h1>Reservations Management</h1>

            <div class="admin-user">
                Welcome, <?= escape($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </header>

        <div class="reservations-container">

            <div class="page-heading">
                <div>
                    <h2>Guest Reservations</h2>
                    <p>Manage guest bookings, reservation status and records.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?= escape($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->

            <section class="stats">

                <div class="stat-card">
                    <h3><i class="fas fa-calendar-check stat-icon"></i>Total Reservations</h3>
                    <strong><?= $totalReservations ?></strong>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-clock stat-icon"></i>Pending</h3>
                    <strong><?= $pendingReservations ?></strong>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-check-circle stat-icon"></i>Confirmed</h3>
                    <strong><?= $confirmedReservations ?></strong>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-ban stat-icon"></i>Cancelled</h3>
                    <strong><?= $cancelledReservations ?></strong>
                </div>

            </section>

            <!-- Reservation Records -->

            <section class="reservation-panel">

                <div class="panel-heading">

                    <h2>Reservation Records</h2>

                    <div class="report-buttons">

                        <a
                            href="exports/export_reservations.php?status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($search) ?>"
                            class="export-btn"
                        >
                            <i class="fas fa-file-excel"></i>
                            Export to Excel
                        </a>

                        <a
                            href="print_reservations.php?status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($search) ?>"
                            class="print-btn"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fas fa-print"></i>
                            Print Reservations
                        </a>

                    </div>

                </div>

                <!-- Search -->

                <form method="GET" class="search-form">

                    <input
                        type="search"
                        name="search"
                        placeholder="Search booking reference, guest name, email or phone"
                        value="<?= escape($search) ?>"
                    >

                    <input
                        type="hidden"
                        name="status"
                        value="<?= escape($statusFilter) ?>"
                    >

                    <button type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>

                </form>

                <!-- Filters -->

                <div class="filter-section">

                    <a
                        href="reservations.php?status=all&search=<?= urlencode($search) ?>"
                        class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>"
                    >
                        All <span>(<?= $totalReservations ?>)</span>
                    </a>

                    <a
                        href="reservations.php?status=pending&search=<?= urlencode($search) ?>"
                        class="filter-btn <?= $statusFilter === 'pending' ? 'active' : '' ?>"
                    >
                        Pending <span>(<?= $pendingReservations ?>)</span>
                    </a>

                    <a
                        href="reservations.php?status=confirmed&search=<?= urlencode($search) ?>"
                        class="filter-btn <?= $statusFilter === 'confirmed' ? 'active' : '' ?>"
                    >
                        Confirmed <span>(<?= $confirmedReservations ?>)</span>
                    </a>

                    <a
                        href="reservations.php?status=cancelled&search=<?= urlencode($search) ?>"
                        class="filter-btn <?= $statusFilter === 'cancelled' ? 'active' : '' ?>"
                    >
                        Cancelled <span>(<?= $cancelledReservations ?>)</span>
                    </a>

                </div>

                <p>
                    Showing <strong><?= count($reservations) ?></strong>
                    reservation(s).
                </p>

                <!-- Reservations Table -->

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Booking Reference</th>
                                <th>Guest Details</th>
                                <th>Room</th>
                                <th>Stay Dates</th>
                                <th>Guests</th>
                                <th>Special Requests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($reservations)): ?>

                            <?php foreach ($reservations as $reservation): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= escape($reservation['booking_reference']) ?>
                                        </strong>
                                        <br>
                                        <small>
                                            <?= escape($reservation['created_at']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <strong><?= escape($reservation['full_name']) ?></strong>
                                        <br>
                                        <?= escape($reservation['email']) ?>
                                        <br>
                                        <?= escape($reservation['phone']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['room_type']) ?>
                                    </td>

                                    <td>
                                        Check-in:
                                        <?= escape($reservation['check_in']) ?>
                                        <br>
                                        Check-out:
                                        <?= escape($reservation['check_out']) ?>
                                    </td>

                                    <td>
                                        <?= escape($reservation['guests']) ?>
                                    </td>

                                    <td>
                                        <?= nl2br(escape($reservation['special_requests'] ?? '')) ?>
                                    </td>

                                    <td>
                                        <span class="status <?= escape($reservation['status']) ?>">
                                            <?= escape($reservation['status']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <div class="reservation-actions">

                                            <a
                                                href="view_reservation.php?id=<?= (int) $reservation['id'] ?>"
                                                class="view-btn"
                                            >
                                                <i class="fas fa-eye"></i> View
                                            </a>

                                            <a
                                                href="edit_reservation.php?id=<?= (int) $reservation['id'] ?>"
                                                class="edit-btn"
                                            >
                                                <i class="fas fa-edit"></i> Update
                                            </a>

                                            <form
                                                method="POST"
                                                action="delete_reservation.php"
                                                onsubmit="return confirm('Permanently delete this reservation? This action cannot be undone.');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escape($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="reservation_id"
                                                    value="<?= (int) $reservation['id'] ?>"
                                                >

                                                <button type="submit" class="delete-btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>

                                        </div>

                                        <?php if ($reservation['status'] === 'pending'): ?>

                                            <form method="POST" class="action-form">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escape($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="reservation_id"
                                                    value="<?= (int) $reservation['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="status"
                                                    value="confirmed"
                                                    class="confirm-btn"
                                                >
                                                    Confirm
                                                </button>

                                                <button
                                                    type="submit"
                                                    name="status"
                                                    value="cancelled"
                                                    class="cancel-btn"
                                                >
                                                    Cancel
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="empty">
                                    <i class="fas fa-calendar-times"></i>
                                    <br><br>
                                    No reservations found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

    </main>

</div>

</body>
</html>
