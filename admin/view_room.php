<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Validate room ID
$roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$roomId || $roomId < 1) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: rooms.php?error=room_not_found');
    exit;
}

$roomType = $room['room_type'] === 'self-contained-apartment'
    ? 'Self-Contained Apartment'
    : 'Luxury Room';

$statusClass = match ($room['status']) {
    'available' => 'available',
    'occupied' => 'occupied',
    'maintenance' => 'maintenance',
    default => ''
};

$statusLabel = match ($room['status']) {
    'available' => 'Available',
    'occupied' => 'Occupied',
    'maintenance' => 'Under Maintenance',
    default => ucfirst($room['status'])
};

$photoPath = null;

if (!empty($room['photo'])) {
    $photoFile = __DIR__ . '/../uploads/rooms/' . basename($room['photo']);

    if (is_file($photoFile)) {
        $photoPath = '../uploads/rooms/' . rawurlencode(basename($room['photo']));
    }
}

$createdAt = !empty($room['created_at'])
    ? date('F j, Y - h:i A', strtotime($room['created_at']))
    : 'Not available';

$updatedAt = !empty($room['updated_at'])
    ? date('F j, Y - h:i A', strtotime($room['updated_at']))
    : 'Not available';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Room | VIOLET-LUE Admin</title>

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

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .header-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn {
            background: #eee;
            color: #333;
        }

        .edit-btn {
            background: #4B006E;
            color: #fff;
        }

        .edit-btn:hover {
            background: #260033;
            color: #fff;
        }

        .room-details-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }

        .room-details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
            gap: 30px;
        }

        .room-image-container {
            width: 100%;
            height: 350px;
            background: #f5eef8;
            border-radius: 9px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .room-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-image-placeholder {
            text-align: center;
            color: #7A219E;
        }

        .room-image-placeholder i {
            font-size: 65px;
            margin-bottom: 12px;
        }

        .room-heading {
            margin-bottom: 22px;
        }

        .room-heading h2 {
            color: #260033;
            font-size: 25px;
            margin: 0 0 8px;
        }

        .room-heading p {
            color: #777;
            margin: 0;
        }

        .room-status {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
        }

        .room-status.available {
            background: #e4f7eb;
            color: #18743a;
        }

        .room-status.occupied {
            background: #fff0d8;
            color: #9a5b00;
        }

        .room-status.maintenance {
            background: #fde8e8;
            color: #a12626;
        }

        .room-price {
            font-size: 27px;
            color: #4B006E;
            font-weight: 700;
            margin: 20px 0;
        }

        .room-price small {
            font-size: 13px;
            color: #777;
            font-weight: 400;
        }

        .details-section-title {
            font-size: 17px;
            color: #4B006E;
            border-bottom: 1px solid #eee;
            padding-bottom: 12px;
            margin: 25px 0 15px;
        }

        .details-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .detail-item label {
            display: block;
            color: #888;
            font-size: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .detail-item strong {
            color: #333;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .description-box {
            background: #faf8fb;
            padding: 17px;
            border-radius: 7px;
            color: #555;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .record-information {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #eee;
        }

        @media (max-width: 850px) {
            .room-details-grid {
                grid-template-columns: 1fr;
            }

            .room-image-container {
                height: 300px;
            }
        }

        @media (max-width: 550px) {
            .room-details-card {
                padding: 16px;
            }

            .details-list,
            .record-information {
                grid-template-columns: 1fr;
            }

            .room-image-container {
                height: 230px;
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
            <h2>Room Details</h2>

            <div>
                <i class="fas fa-user-shield"></i>
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </div>

        <div class="page-header">

            <div>
                <h1><i class="fas fa-bed"></i> Room Details</h1>
                <p>View complete information about this room.</p>
            </div>

            <div class="header-actions">

                <a href="rooms.php" class="header-btn back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Rooms
                </a>

                <a href="edit_room.php?id=<?= (int)$room['id'] ?>"
                   class="header-btn edit-btn">
                    <i class="fas fa-edit"></i>
                    Edit Room
                </a>

            </div>

        </div>

        <div class="room-details-card">

            <div class="room-details-grid">

                <div>

                    <div class="room-image-container">

                        <?php if ($photoPath): ?>

                            <img src="<?= htmlspecialchars($photoPath) ?>"
                                 alt="<?= htmlspecialchars($room['room_name']) ?>">

                        <?php else: ?>

                            <div class="room-image-placeholder">
                                <i class="fas fa-image"></i>
                                <p>No room photo uploaded</p>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <div>

                    <div class="room-heading">

                        <h2><?= htmlspecialchars($room['room_name']) ?></h2>

                        <p>
                            Room Number:
                            <strong><?= htmlspecialchars($room['room_number']) ?></strong>
                        </p>

                        <span class="room-status <?= $statusClass ?>">
                            <?= htmlspecialchars($statusLabel) ?>
                        </span>

                    </div>

                    <div class="room-price">
                        <?= number_format((float)$room['price_per_night'], 2) ?>
                        <small>per night</small>
                    </div>

                    <h3 class="details-section-title">Room Information</h3>

                    <div class="details-list">

                        <div class="detail-item">
                            <label>Room Number</label>
                            <strong><?= htmlspecialchars($room['room_number']) ?></strong>
                        </div>

                        <div class="detail-item">
                            <label>Room Type</label>
                            <strong><?= htmlspecialchars($roomType) ?></strong>
                        </div>

                        <div class="detail-item">
                            <label>Guest Capacity</label>
                            <strong><?= (int)$room['capacity'] ?> Guests</strong>
                        </div>

                        <div class="detail-item">
                            <label>Availability Status</label>
                            <strong><?= htmlspecialchars($statusLabel) ?></strong>
                        </div>

                    </div>

                </div>

            </div>

            <h3 class="details-section-title">Room Description</h3>

            <div class="description-box">
                <?php if (!empty($room['description'])): ?>
                    <?= htmlspecialchars($room['description']) ?>
                <?php else: ?>
                    No description has been added for this room.
                <?php endif; ?>
            </div>

            <h3 class="details-section-title">Record Information</h3>

            <div class="record-information">

                <div class="detail-item">
                    <label>Created On</label>
                    <strong><?= htmlspecialchars($createdAt) ?></strong>
                </div>

                <div class="detail-item">
                    <label>Last Updated</label>
                    <strong><?= htmlspecialchars($updatedAt) ?></strong>
                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>
