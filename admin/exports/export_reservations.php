<?php
session_start();

require_once __DIR__ . '/../../config/database.php';

// Protect the export file
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get dashboard filters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$allowedStatuses = ['all', 'pending', 'confirmed', 'cancelled'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

// Build query
$sql = "SELECT
            booking_reference,
            full_name,
            email,
            phone,
            room_type,
            check_in,
            check_out,
            guests,
            special_requests,
            status,
            created_at
        FROM reservations
        WHERE 1=1";

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

// Excel-compatible CSV download
$filename = 'Violet-Lue-Reservations-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// Column headings
fputcsv($output, [
    'Booking Reference',
    'Guest Name',
    'Email',
    'Phone',
    'Room Type',
    'Check-in',
    'Check-out',
    'Guests',
    'Special Requests',
    'Status',
    'Booking Date'
]);

// Export reservation records
foreach ($reservations as $reservation) {
    $row = [
        $reservation['booking_reference'],
        $reservation['full_name'],
        $reservation['email'],
        $reservation['phone'],
        $reservation['room_type'],
        $reservation['check_in'],
        $reservation['check_out'],
        $reservation['guests'],
        $reservation['special_requests'],
        $reservation['status'],
        $reservation['created_at']
    ];

    // Reduce spreadsheet formula-injection risk
    foreach ($row as &$value) {
        $value = (string) ($value ?? '');

        if (preg_match('/^[\s]*[=+\-@]/', $value)) {
            $value = "'" . $value;
        }
    }
    unset($value);

    fputcsv($output, $row);
}

fclose($output);
exit;