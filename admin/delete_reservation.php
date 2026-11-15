<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect administrator page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    header('Location: dashboard.php?error=invalid_request');
    exit;
}

// Validate reservation ID
$id = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: dashboard.php?error=invalid_reservation');
    exit;
}

try {

    // Check whether reservation exists
    $check = $pdo->prepare("
        SELECT id, booking_reference
        FROM reservations
        WHERE id = :id
        LIMIT 1
    ");

    $check->execute(['id' => $id]);
    $reservation = $check->fetch();

    if (!$reservation) {
        header('Location: dashboard.php?error=not_found');
        exit;
    }

    // Delete reservation
    $delete = $pdo->prepare("
        DELETE FROM reservations
        WHERE id = :id
    ");

    $delete->execute(['id' => $id]);

    header('Location: dashboard.php?deleted=1');
    exit;

} catch (PDOException $e) {

    error_log('Reservation deletion error: ' . $e->getMessage());

    header('Location: dashboard.php?error=delete_failed');
    exit;
}
