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

$search = trim($_GET['search'] ?? '');
$error = '';
$success = '';

// Display messages
if (!empty($_SESSION['guest_success'])) {
    $success = $_SESSION['guest_success'];
    unset($_SESSION['guest_success']);
}

if (!empty($_SESSION['guest_error'])) {
    $error = $_SESSION['guest_error'];
    unset($_SESSION['guest_error']);
}

// Handle guest deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete_guest') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $guestId = filter_var($_POST['guest_id'] ?? '', FILTER_VALIDATE_INT);

        if (!$guestId || $guestId < 1) {
            $error = 'Invalid guest selected.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    "DELETE FROM guests WHERE id = :id"
                );

                $stmt->execute([':id' => $guestId]);

                if ($stmt->rowCount() > 0) {
                    $success = 'Guest deleted successfully.';
                } else {
                    $error = 'Guest not found.';
                }

            } catch (PDOException $e) {
                error_log('Delete guest error: ' . $e->getMessage());
                $error = 'Unable to delete this guest. Please check whether the record is linked to another transaction.';
            }
        }
    }
}

// Guest statistics
$totalGuests = (int)$pdo->query(
    "SELECT COUNT(*) FROM guests"
)->fetchColumn();

$todayGuests = (int)$pdo->query(
    "SELECT COUNT(*) FROM guests WHERE DATE(created_at) = CURDATE()"
)->fetchColumn();

// Search guests
$sql = "SELECT * FROM guests WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (
        guest_reference LIKE :search
        OR full_name LIKE :search
        OR email LIKE :search
        OR phone LIKE :search
    )";

    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$guests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guest Management | VIOLET-LUE Admin</title>

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

        .add-guest-btn {
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

        .add-guest-btn:hover {
            background: #260033;
            color: #fff;
        }

        .guest-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .guest-stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .guest-stat-card .stat-icon {
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

        .guest-stat-card h3 {
            font-size: 25px;
            color: #260033;
            margin: 0 0 5px;
        }

        .guest-stat-card p {
            color: #777;
            margin: 0;
            font-size: 14px;
        }

        .guests-panel {
            background: #fff;
            padding: 22px;
            border-radius: 9px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .filter-form {
            display: flex;
            gap: 12px;
            margin-bottom: 22px;
        }

        .filter-form input {
            flex: 1;
            min-width: 150px;
            padding: 11px 13px;
            border: 1px solid #ddd;
            border-radius: 6px;
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

        .guests-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .guests-table th,
        .guests-table td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .guests-table th {
            background: #f8f5fa;
            color: #4B006E;
            font-size: 13px;
        }

        .guest-reference {
            color: #4B006E;
            font-weight: 700;
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

        @media (max-width: 600px) {
            .guest-stats {
                grid-template-columns: 1fr;
            }

            .guests-panel {
                padding: 15px;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-form input {
                box-sizing: border-box;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php
    $activePage = 'guests';
    require_once __DIR__ . '/includes/sidebar.php';
    ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <h2>Guest Management</h2>

            <div>
                <i class="fas fa-user-shield"></i>
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h1><i class="fas fa-users"></i> Guest Management</h1>
                <p>Manage registered guests and their information.</p>
            </div>

            <a href="add_guest.php" class="add-guest-btn">
                <i class="fas fa-user-plus"></i>
                Register New Guest
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

        <div class="guest-stats">

            <div class="guest-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?= $totalGuests ?></h3>
                <p>Total Registered Guests</p>
            </div>

            <div class="guest-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3><?= $todayGuests ?></h3>
                <p>Guests Registered Today</p>
            </div>

        </div>

        <div class="guests-panel">

            <form method="GET" class="filter-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search reference, name, email or phone..."
                    value="<?= htmlspecialchars($search) ?>">

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Search
                </button>

                <a href="guests.php" class="reset-btn">
                    <i class="fas fa-redo"></i> Reset
                </a>

            </form>

            <div class="table-responsive">

                <table class="guests-table">

                    <thead>
                        <tr>
                            <th>Guest Reference</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Nationality</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($guests) > 0): ?>

                        <?php foreach ($guests as $guest): ?>

                            <tr>

                                <td>
                                    <span class="guest-reference">
                                        <?= htmlspecialchars($guest['guest_reference']) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($guest['full_name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guest['email'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guest['phone']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guest['nationality'] ?? 'N/A') ?>
                                </td>

                                <td>
                                    <?= date('d M Y', strtotime($guest['created_at'])) ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="view_guest.php?id=<?= (int)$guest['id'] ?>"
                                            class="action-btn view-btn"
                                            title="View Guest">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a
                                            href="edit_guest.php?id=<?= (int)$guest['id'] ?>"
                                            class="action-btn edit-btn"
                                            title="Edit Guest">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this guest? This action cannot be undone.');">

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                            <input
                                                type="hidden"
                                                name="guest_id"
                                                value="<?= (int)$guest['id'] ?>">

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete_guest">

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                                title="Delete Guest">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">

                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h3>No Guests Found</h3>
                                    <p>No guest records match your search.</p>
                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <p style="margin-top: 18px; color: #777;">
                Showing <?= count($guests) ?> guest(s).
            </p>

        </div>

    </main>

</div>

</body>
</html>
