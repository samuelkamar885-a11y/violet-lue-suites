<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

// Validate room ID
$roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$roomId || $roomId < 1) {
    header('Location: rooms.php');
    exit;
}

// Load room
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: rooms.php?error=room_not_found');
    exit;
}

// Form values
$roomNumber = $room['room_number'];
$roomName = $room['room_name'];
$roomType = $room['room_type'];
$description = $room['description'] ?? '';
$price = $room['price_per_night'];
$capacity = $room['capacity'];
$status = $room['status'];

$allowedTypes = [
    'luxury-room',
    'self-contained-apartment'
];

$allowedStatuses = [
    'available',
    'occupied',
    'maintenance'
];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid form submission. Please refresh and try again.';
    } else {

        $roomNumber = trim($_POST['room_number'] ?? '');
        $roomName = trim($_POST['room_name'] ?? '');
        $roomType = $_POST['room_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price_per_night'] ?? '');
        $capacity = filter_var($_POST['capacity'] ?? '', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';

        if (
            $roomNumber === '' ||
            $roomName === '' ||
            strlen($roomNumber) > 30 ||
            strlen($roomName) > 150 ||
            !in_array($roomType, $allowedTypes, true) ||
            !is_numeric($price) ||
            !is_finite((float)$price) ||
            (float)$price < 0 ||
            (float)$price > 99999999.99 ||
            $capacity === false ||
            $capacity < 1 ||
            !in_array($status, $allowedStatuses, true)
        ) {
            $error = 'Please enter valid information in all required fields.';
        } else {

            $uploadDirectory = __DIR__ . '/../uploads/rooms/';
            $newPhotoName = null;
            $oldPhotoName = $room['photo'];

            // Handle optional replacement photo
            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $file = $_FILES['photo'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Photo upload failed. Please try again.';
                } elseif ($file['size'] > 3 * 1024 * 1024) {
                    $error = 'Photo must not exceed 3 MB.';
                } elseif (!is_uploaded_file($file['tmp_name'])) {
                    $error = 'Invalid photo upload.';
                } else {
                    $allowedMimeTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);

                    if (!isset($allowedMimeTypes[$mimeType])) {
                        $error = 'Only JPG, PNG, and WEBP images are allowed.';
                    } elseif (!is_dir($uploadDirectory)) {
                        $error = 'Room photo directory does not exist.';
                    } else {
                        $extension = $allowedMimeTypes[$mimeType];
                        $newPhotoName = 'room_' . bin2hex(random_bytes(12)) . '.' . $extension;
                    }
                }
            }

            if ($error === '') {
                try {
                    $pdo->beginTransaction();

                    // Save the new photo before updating its database reference
                    if ($newPhotoName !== null) {
                        if (!move_uploaded_file(
                            $_FILES['photo']['tmp_name'],
                            $uploadDirectory . $newPhotoName
                        )) {
                            throw new RuntimeException('Unable to save uploaded photo.');
                        }
                    }

                    $photoToSave = $newPhotoName ?? $oldPhotoName;

                    $sql = "UPDATE rooms SET
                                room_number = :room_number,
                                room_name = :room_name,
                                room_type = :room_type,
                                description = :description,
                                price_per_night = :price,
                                capacity = :capacity,
                                photo = :photo,
                                status = :status
                            WHERE id = :id";

                    $update = $pdo->prepare($sql);

                    $update->execute([
                        ':room_number' => $roomNumber,
                        ':room_name' => $roomName,
                        ':room_type' => $roomType,
                        ':description' => $description,
                        ':price' => $price,
                        ':capacity' => $capacity,
                        ':photo' => $photoToSave,
                        ':status' => $status,
                        ':id' => $roomId
                    ]);

                    $pdo->commit();

                    // Delete old photo only after successful database update
                    if ($newPhotoName !== null && !empty($oldPhotoName)) {
                        $oldPhotoPath = $uploadDirectory . basename($oldPhotoName);

                        if (is_file($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }

                    $success = 'Room information updated successfully!';

                    // Reload updated room
                    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
                    $stmt->execute([':id' => $roomId]);
                    $room = $stmt->fetch();

                    $roomNumber = $room['room_number'];
                    $roomName = $room['room_name'];
                    $roomType = $room['room_type'];
                    $description = $room['description'] ?? '';
                    $price = $room['price_per_night'];
                    $capacity = $room['capacity'];
                    $status = $room['status'];

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    if ($newPhotoName !== null && is_file($uploadDirectory . $newPhotoName)) {
                        unlink($uploadDirectory . $newPhotoName);
                    }

                    if ($e->getCode() === '23000') {
                        $error = 'That room number is already assigned to another room.';
                    } else {
                        error_log('Edit room error: ' . $e->getMessage());
                        $error = 'Unable to update room information.';
                    }

                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    if ($newPhotoName !== null && is_file($uploadDirectory . $newPhotoName)) {
                        unlink($uploadDirectory . $newPhotoName);
                    }

                    error_log('Edit room upload error: ' . $e->getMessage());
                    $error = 'Unable to save room changes. Please try again.';
                }
            }
        }
    }
}

// Current photo
$currentPhoto = null;

if (!empty($room['photo'])) {
    $photoFile = __DIR__ . '/../uploads/rooms/' . basename($room['photo']);

    if (is_file($photoFile)) {
        $currentPhoto = '../uploads/rooms/' . rawurlencode(basename($room['photo']));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Room | VIOLET-LUE Admin</title>

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

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 17px;
            background: #4B006E;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        .back-btn:hover {
            background: #260033;
            color: #fff;
        }

        .room-form-card {
            background: #fff;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.06);
            max-width: 950px;
        }

        .form-section-title {
            color: #4B006E;
            font-size: 18px;
            margin: 0 0 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #7A219E;
            box-shadow: 0 0 0 3px rgba(122,33,158,0.10);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group small {
            display: block;
            color: #777;
            margin-top: 6px;
        }

        .current-photo {
            margin-bottom: 15px;
        }

        .current-photo img {
            width: 180px;
            height: 130px;
            object-fit: cover;
            border-radius: 7px;
            border: 1px solid #eee;
        }

        .no-photo {
            color: #777;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .submit-btn {
            border: none;
            background: #4B006E;
            color: #fff;
            padding: 13px 22px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #260033;
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            padding: 13px 22px;
            border-radius: 6px;
            background: #eee;
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #e8f7ed;
            color: #176b35;
            border: 1px solid #b9e6c7;
        }

        .alert-error {
            background: #fdecec;
            color: #a12626;
            border: 1px solid #f3c1c1;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .room-form-card {
                padding: 18px;
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
            <h2>Edit Room</h2>

            <div>
                <i class="fas fa-user-shield"></i>
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Room</h1>
                <p>Update room information and availability.</p>
            </div>

            <a href="rooms.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Room Management
            </a>
        </div>

        <div class="room-form-card">

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

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i>
                    Room Information
                </h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="room_number">Room Number *</label>
                        <input
                            type="text"
                            id="room_number"
                            name="room_number"
                            maxlength="30"
                            value="<?= htmlspecialchars($roomNumber) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="room_name">Room Name *</label>
                        <input
                            type="text"
                            id="room_name"
                            name="room_name"
                            maxlength="150"
                            value="<?= htmlspecialchars($roomName) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="room_type">Room Type *</label>
                        <select id="room_type" name="room_type" required>
                            <option value="luxury-room"
                                <?= $roomType === 'luxury-room' ? 'selected' : '' ?>>
                                Luxury Room
                            </option>

                            <option value="self-contained-apartment"
                                <?= $roomType === 'self-contained-apartment' ? 'selected' : '' ?>>
                                Self-Contained Apartment
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price_per_night">Price Per Night *</label>
                        <input
                            type="number"
                            id="price_per_night"
                            name="price_per_night"
                            min="0"
                            max="99999999.99"
                            step="0.01"
                            value="<?= htmlspecialchars((string)$price) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Guest Capacity *</label>
                        <input
                            type="number"
                            id="capacity"
                            name="capacity"
                            min="1"
                            value="<?= htmlspecialchars((string)$capacity) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="status">Room Status *</label>
                        <select id="status" name="status" required>
                            <option value="available"
                                <?= $status === 'available' ? 'selected' : '' ?>>
                                Available
                            </option>

                            <option value="occupied"
                                <?= $status === 'occupied' ? 'selected' : '' ?>>
                                Occupied
                            </option>

                            <option value="maintenance"
                                <?= $status === 'maintenance' ? 'selected' : '' ?>>
                                Under Maintenance
                            </option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Room Description</label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Describe the room and its facilities..."><?= htmlspecialchars($description) ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label>Current Room Photo</label>

                        <div class="current-photo">
                            <?php if ($currentPhoto): ?>
                                <img
                                    src="<?= htmlspecialchars($currentPhoto) ?>"
                                    alt="Current room photo">
                            <?php else: ?>
                                <div class="no-photo">
                                    <i class="fas fa-image"></i>
                                    No photo uploaded.
                                </div>
                            <?php endif; ?>
                        </div>

                        <label for="photo">Replace Room Photo</label>
                        <input
                            type="file"
                            id="photo"
                            name="photo"
                            accept=".jpg,.jpeg,.png,.webp">

                        <small>
                            Leave empty to keep the current photo.
                            Accepted: JPG, PNG, WEBP. Maximum size: 3 MB.
                        </small>
                    </div>

                </div>

                <div class="form-actions">

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i>
                        Update Room
                    </button>

                    <a href="rooms.php" class="cancel-btn">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>
