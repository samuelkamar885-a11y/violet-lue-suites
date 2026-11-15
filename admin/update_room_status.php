<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect this page: admin login required
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: rooms.php');
    exit;
}

// Validate CSRF token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['room_error'] = 'Invalid security token. Please try again.';
    header('Location: rooms.php');
    exit;
}

// Validate room ID
$roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);

if (!$roomId || $roomId < 1) {
    $_SESSION['room_error'] = 'Invalid room selected.';
    header('Location: rooms.php');
    exit;
}

// Validate room status
$allowedStatuses = [
    'available',
    'occupied',
    'maintenance'
];

$status = $_POST['status'] ?? '';

if (!in_array($status, $allowedStatuses, true)) {
    $_SESSION['room_error'] = 'Invalid room status selected.';
    header('Location: rooms.php');
    exit;
}

try {
    // Update room status
    $stmt = $pdo->prepare("
        UPDATE rooms
        SET status = :status
        WHERE id = :id
    ");

    $stmt->execute([
        ':status' => $status,
        ':id' => $roomId
    ]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['room_success'] = 'Room status updated successfully.';
    } else {
        // Check whether the room exists even if its status was unchanged
        $check = $pdo->prepare("SELECT id FROM rooms WHERE id = :id");
        $check->execute([':id' => $roomId]);

        if ($check->fetch()) {
            $_SESSION['room_success'] = 'Room status is already set to the selected value.';
        } else {
            $_SESSION['room_error'] = 'Room not found.';
        }
    }

} catch (PDOException $e) {
    error_log('Room status update error: ' . $e->getMessage());
    $_SESSION['room_error'] = 'Unable to update room status. Please try again.';
}

// Return to room management
header('Location: rooms.php');
exit;