<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$search = trim($_GET['search'] ?? '');
$typeFilter = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

$allowedTypes = [
    'luxury-room',
    'self-contained-apartment'
];

$allowedStatuses = [
    'available',
    'occupied',
    'maintenance'
];

if ($typeFilter !== 'all' && !in_array($typeFilter, $allowedTypes, true)) {
    $typeFilter = 'all';
}

if ($statusFilter !== 'all' && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$error = '';
$success = '';

// Display messages from room status update
if (!empty($_SESSION['room_success'])) {
    $success = $_SESSION['room_success'];
    unset($_SESSION['room_success']);
}

if (!empty($_SESSION['room_error'])) {
    $error = $_SESSION['room_error'];
    unset($_SESSION['room_error']);
}

// Handle room deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_room') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $roomId = filter_var($_POST['room_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$roomId || $roomId < 1) {
            $error = 'Invalid room selected.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT photo FROM rooms WHERE id = :id");
                $stmt->execute([':id' => $roomId]);
                $room = $stmt->fetch();

                if (!$room) {
                    $error = 'Room not found.';
                } else {
                    $delete = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
                    $delete->execute([':id' => $roomId]);

                    if ($delete->rowCount() > 0) {
                        if (!empty($room['photo'])) {
                            $photoPath = __DIR__ . '/../uploads/rooms/' . basename($room['photo']);

                            if (is_file($photoPath)) {
                                unlink($photoPath);
                            }
                        }

                        $success = 'Room deleted successfully.';
                    } else {
                        $error = 'The room could not be deleted.';
                    }
                }
            } catch (PDOException $e) {
                error_log('Delete room error: ' . $e->getMessage());
                $error = 'Unable to delete this room. It may be linked to another record.';
            }
        }
    }
}

// Room statistics
$totalRooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();

$availableRooms = (int)$pdo->query(
    "SELECT COUNT(*) FROM rooms WHERE status = 'available'"
)->fetchColumn();

$occupiedRooms = (int)$pdo->query(
    "SELECT COUNT(*) FROM rooms WHERE status = 'occupied'"
)->fetchColumn();

$maintenanceRooms = (int)$pdo->query(
    "SELECT COUNT(*) FROM rooms WHERE status = 'maintenance'"
)->fetchColumn();

// Build room search
$sql = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (room_number LIKE :search OR room_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($typeFilter !== 'all') {
    $sql .= " AND room_type = :room_type";
    $params[':room_type'] = $typeFilter;
}

if ($statusFilter !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

function roomTypeLabel($type)
{
    return $type === 'self-contained-apartment'
        ? 'Self-Contained Apartment'
        : 'Luxury Room';
}

function roomStatusClass($status)
{
    return match ($status) {
        'available' => 'status-available',
        'occupied' => 'status-occupied',
        'maintenance' => 'status-maintenance',
        default => ''
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Room Management | VIOLET-LUE Admin</title>

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
            color: #260033;
            font-size: 26px;
            margin: 0 0 6px;
        }

        .page-header p {
            color: #777;
            margin: 0;
        }

        .add-room-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            background: #4B006E;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        .add-room-btn:hover {
            background: #260033;
            color: #fff;
        }

        .room-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .room-stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .room-stat-card .stat-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f1e6f7;
            color: #4B006E;
            margin-bottom: 12px;
        }

        .room-stat-card h3 {
            font-size: 25px;
            color: #260033;
            margin: 0 0 5px;
        }

        .room-stat-card p {
            color: #777;
            margin: 0;
            font-size: 14px;
        }

        .rooms-panel {
            background: #fff;
            padding: 22px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .filter-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .filter-form input,
        .filter-form select {
            padding: 11px 13px;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 160px;
        }

        .filter-form input {
            flex: 1;
        }

        .filter-btn,
        .reset-btn {
            padding: 11px 17px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }

        .filter-btn {
            background: #4B006E;
            color: #fff;
        }

        .reset-btn {
            background: #eee;
            color: #333;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .rooms-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        .rooms-table th,
        .rooms-table td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .rooms-table th {
            background: #f8f5fa;
            color: #4B006E;
            font-size: 13px;
        }

        .room-photo {
            width: 65px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            background: #eee;
        }

        .no-photo {
            width: 65px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1e6f7;
            color: #7A219E;
            border-radius: 6px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-available {
            background: #e4f7eb;
            color: #18743a;
        }

        .status-occupied {
            background: #fff0d8;
            color: #9a5b00;
        }

        .status-maintenance {
            background: #fde8e8;
            color: #a12626;
        }

        /* Quick room status update */
        .status-update-form {
            display: flex;
            flex-direction: column;
            gap: 7px;
            min-width: 145px;
        }

        .status-update-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            font-size: 12px;
        }

        .status-update-btn {
            width: 100%;
            padding: 8px 10px;
            background: #4B006E;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .status-update-btn:hover {
            background: #260033;
        }

        .action-buttons {
            display: flex;
            gap: 7px;
        }

        .action-btn {
            width: 33px;
            height: 33px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .view-btn {
            background: #e7f1ff;
            color: #1762a5;
        }

        .edit-btn {
            background: #fff3d9;
            color: #a36a00;
        }

        .delete-btn {
            background: #fde8e8;
            color: #b42323;
        }

        .delete-form {
            display: inline;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .alert-success {
            background: #e8f7ed;
            color: #176b35;
        }

        .alert-error {
            background: #fdecec;
            color: #a12626;
        }

        .empty-state {
            text-align: center;
            padding: 45px 15px;
            color: #777;
        }

        .empty-state i {
            font-size: 42px;
            color: #c7a6d7;
            margin-bottom: 12px;
        }

        @media (max-width: 950px) {
            .room-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 550px) {
            .room-stats {
                grid-template-columns: 1fr;
            }

            .rooms-panel {
                padding: 15px;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-form input,
            .filter-form select {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php
    $activePage = 'rooms';
    require_once __DIR__ . '/includes/sidebar.php';
    ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <h2>Room Management</h2>

            <div>
                <i class="fas fa-user-shield"></i>
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1><i class="fas fa-bed"></i> Room Management</h1>
                <p>Manage rooms and self-contained apartments.</p>
            </div>

            <a href="add_room.php" class="add-room-btn">
                <i class="fas fa-plus"></i>
                Add New Room
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="room-stats">

            <div class="room-stat-card">
                <div class="stat-icon"><i class="fas fa-bed"></i></div>
                <h3><?= $totalRooms ?></h3>
                <p>Total Rooms</p>
            </div>

            <div class="room-stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <h3><?= $availableRooms ?></h3>
                <p>Available Rooms</p>
            </div>

            <div class="room-stat-card">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <h3><?= $occupiedRooms ?></h3>
                <p>Occupied Rooms</p>
            </div>

            <div class="room-stat-card">
                <div class="stat-icon"><i class="fas fa-tools"></i></div>
                <h3><?= $maintenanceRooms ?></h3>
                <p>Under Maintenance</p>
            </div>

        </div>

        <div class="rooms-panel">

            <form method="GET" class="filter-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search room number or name..."
                    value="<?= htmlspecialchars($search) ?>">

                <select name="type">
                    <option value="all">All Room Types</option>
                    <option value="luxury-room"
                        <?= $typeFilter === 'luxury-room' ? 'selected' : '' ?>>
                        Luxury Room
                    </option>
                    <option value="self-contained-apartment"
                        <?= $typeFilter === 'self-contained-apartment' ? 'selected' : '' ?>>
                        Self-Contained Apartment
                    </option>
                </select>

                <select name="status">
                    <option value="all">All Statuses</option>
                    <option value="available"
                        <?= $statusFilter === 'available' ? 'selected' : '' ?>>
                        Available
                    </option>
                    <option value="occupied"
                        <?= $statusFilter === 'occupied' ? 'selected' : '' ?>>
                        Occupied
                    </option>
                    <option value="maintenance"
                        <?= $statusFilter === 'maintenance' ? 'selected' : '' ?>>
                        Maintenance
                    </option>
                </select>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Search
                </button>

                <a href="rooms.php" class="reset-btn">
                    <i class="fas fa-redo"></i> Reset
                </a>

            </form>

            <div class="table-responsive">

                <table class="rooms-table">

                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Room Number</th>
                            <th>Room Name</th>
                            <th>Room Type</th>
                            <th>Price / Night</th>
                            <th>Capacity</th>
                            <th>Current Status</th>
                            <th>Update Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($rooms) > 0): ?>

                        <?php foreach ($rooms as $room): ?>

                            <tr>
                                <td>
                                    <?php if (!empty($room['photo'])): ?>
                                        <img
                                            src="../uploads/rooms/<?= rawurlencode(basename($room['photo'])) ?>"
                                            alt="Room photo"
                                            class="room-photo">
                                    <?php else: ?>
                                        <div class="no-photo">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($room['room_number']) ?></strong>
                                </td>

                                <td><?= htmlspecialchars($room['room_name']) ?></td>

                                <td><?= htmlspecialchars(roomTypeLabel($room['room_type'])) ?></td>

                                <td>
                                    <strong><?= number_format((float)$room['price_per_night'], 2) ?></strong>
                                </td>

                                <td>
                                    <?= (int)$room['capacity'] ?> Guests
                                </td>

                                <td>
                                    <span class="status-badge <?= roomStatusClass($room['status']) ?>">
                                        <?= htmlspecialchars($room['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="update_room_status.php"
                                        class="status-update-form"
                                        onsubmit="return confirm('Are you sure you want to update this room status?');">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                        <input
                                            type="hidden"
                                            name="room_id"
                                            value="<?= (int)$room['id'] ?>">

                                        <select name="status" required>
                                            <option value="available"
                                                <?= $room['status'] === 'available' ? 'selected' : '' ?>>
                                                Available
                                            </option>

                                            <option value="occupied"
                                                <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>
                                                Occupied
                                            </option>

                                            <option value="maintenance"
                                                <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>
                                                Maintenance
                                            </option>
                                        </select>

                                        <button type="submit" class="status-update-btn">
                                            <i class="fas fa-sync-alt"></i> Update
                                        </button>

                                    </form>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="view_room.php?id=<?= (int)$room['id'] ?>"
                                            class="action-btn view-btn"
                                            title="View Room">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a
                                            href="edit_room.php?id=<?= (int)$room['id'] ?>"
                                            class="action-btn edit-btn"
                                            title="Edit Room">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form
                                            method="POST"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this room? This action cannot be undone.');">

                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                            <input type="hidden" name="room_id"
                                                   value="<?= (int)$room['id'] ?>">

                                            <input type="hidden" name="action"
                                                   value="delete_room">

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                                title="Delete Room">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-bed"></i>
                                    <h3>No Rooms Found</h3>
                                    <p>No rooms match your search. Add a new room to get started.</p>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <p style="margin-top: 18px; color: #777;">
                Showing <?= count($rooms) ?> room(s).
            </p>

        </div>

    </main>

</div>

</body>
</html>
