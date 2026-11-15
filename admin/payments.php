<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'payments';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = $_SESSION['payment_success'] ?? '';
unset($_SESSION['payment_success']);

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

$allowedStatuses = ['all', 'pending', 'paid', 'failed', 'refunded'];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

/* Delete payment */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'delete'
) {
    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $paymentId = filter_input(
            INPUT_POST,
            'payment_id',
            FILTER_VALIDATE_INT
        );

        if ($paymentId) {
            try {
                $stmt = $pdo->prepare(
                    "DELETE FROM payments WHERE id = :id"
                );

                $stmt->execute(['id' => $paymentId]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['payment_success'] =
                        'Payment record deleted successfully.';
                } else {
                    $_SESSION['payment_success'] =
                        'No matching payment record was found.';
                }

                header('Location: payments.php');
                exit;

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Unable to delete this payment record.';
            }
        } else {
            $error = 'Invalid payment record.';
        }
    }
}

/* Payment statistics */
$stats = [
    'total' => 0,
    'pending' => 0,
    'paid' => 0,
    'failed' => 0,
    'refunded' => 0,
    'revenue' => 0
];

$payments = [];

try {
    $stmt = $pdo->query("
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
            ) AS revenue
        FROM payments
    ");

    $stats = $stmt->fetch() ?: $stats;

    $sql = "
        SELECT
            p.*,
            r.booking_reference,
            r.full_name,
            r.room_type
        FROM payments p
        LEFT JOIN reservations r
            ON p.reservation_id = r.id
        WHERE 1 = 1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                p.payment_reference LIKE :search1
                OR p.transaction_reference LIKE :search2
                OR r.booking_reference LIKE :search3
                OR r.full_name LIKE :search4
            )
        ";

        $term = '%' . $search . '%';

        $params['search1'] = $term;
        $params['search2'] = $term;
        $params['search3'] = $term;
        $params['search4'] = $term;
    }

    if ($statusFilter !== 'all') {
        $sql .= " AND p.payment_status = :status";
        $params['status'] = $statusFilter;
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $payments = [];
    $error = 'Unable to load payment records. Please check the database.';
}

function paymentStatusClass($status)
{
    return match ($status) {
        'paid' => 'paid',
        'pending' => 'pending',
        'failed' => 'failed',
        'refunded' => 'refunded',
        default => 'unknown'
    };
}

function paymentMethodLabel($method)
{
    return ucwords(str_replace('-', ' ', $method));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payments Management | VIOLET-LUE</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-heading h1 {
            color: #4B006E;
            margin-bottom: 5px;
        }

        .page-heading p {
            color: #777;
            margin: 0;
        }

        .payment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .payment-stat {
            background: #fff;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
            border-left: 4px solid #4B006E;
        }

        .payment-stat h3 {
            color: #777;
            font-size: 14px;
            margin: 0 0 12px;
        }

        .payment-stat strong {
            font-size: 25px;
            color: #333;
        }

        .payment-stat i {
            color: #D4AF37;
            margin-right: 8px;
        }

        .payment-toolbar {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .payment-toolbar form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            width: 100%;
        }

        .payment-toolbar input,
        .payment-toolbar select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .payment-toolbar input {
            flex: 1;
            min-width: 220px;
        }

        .btn-purple {
            background: #4B006E;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .btn-purple:hover {
            background: #260033;
            color: #fff;
        }

        .add-payment-btn {
            background: #4B006E;
            color: #fff;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: .3s;
        }

        .add-payment-btn:hover {
            background: #260033;
            color: #fff;
            transform: translateY(-2px);
        }

        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        .payment-table th,
        .payment-table td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 13px;
        }

        .payment-table th {
            background: #f8f5fa;
            color: #4B006E;
        }

        .status-badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-badge.paid {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.failed {
            background: #f8d7da;
            color: #842029;
        }

        .status-badge.refunded {
            background: #cfe2ff;
            color: #084298;
        }

        .action-btn {
            border: none;
            padding: 7px 9px;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
            text-decoration: none;
            display: inline-block;
        }

        .view-btn {
            background: #e7ddf0;
            color: #4B006E;
        }

        .delete-btn {
            background: #f8d7da;
            color: #842029;
        }

        .alert {
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        @media(max-width: 600px) {
            .payment-toolbar form {
                width: 100%;
            }

            .payment-toolbar input,
            .payment-toolbar select {
                width: 100%;
                min-width: 100%;
            }

            .add-payment-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <div class="page-heading">
            <div>
                <h1>
                    <i class="fas fa-money-bill-wave"></i>
                    Payments Management
                </h1>
                <p>Manage and monitor guest payments.</p>
            </div>

            <a href="add_payment.php" class="add-payment-btn">
                <i class="fas fa-plus"></i>
                Record New Payment
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="payment-stats">

            <div class="payment-stat">
                <h3><i class="fas fa-receipt"></i> Total Payments</h3>
                <strong><?= number_format((int)$stats['total']) ?></strong>
            </div>

            <div class="payment-stat">
                <h3><i class="fas fa-clock"></i> Pending</h3>
                <strong><?= number_format((int)$stats['pending']) ?></strong>
            </div>

            <div class="payment-stat">
                <h3><i class="fas fa-check-circle"></i> Paid</h3>
                <strong><?= number_format((int)$stats['paid']) ?></strong>
            </div>

            <div class="payment-stat">
                <h3><i class="fas fa-times-circle"></i> Failed</h3>
                <strong><?= number_format((int)$stats['failed']) ?></strong>
            </div>

            <div class="payment-stat">
                <h3><i class="fas fa-undo"></i> Refunded</h3>
                <strong><?= number_format((int)$stats['refunded']) ?></strong>
            </div>

            <div class="payment-stat">
                <h3><i class="fas fa-wallet"></i> Total Revenue</h3>
                <strong>$<?= number_format((float)$stats['revenue'], 2) ?></strong>
            </div>

        </section>

        <section class="payment-toolbar">

            <form method="GET" action="payments.php">

                <input
                    type="text"
                    name="search"
                    placeholder="Search payment, booking or guest..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="status">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>
                        All Statuses
                    </option>

                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>
                        Paid
                    </option>

                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>
                        Failed
                    </option>

                    <option value="refunded" <?= $statusFilter === 'refunded' ? 'selected' : '' ?>>
                        Refunded
                    </option>
                </select>

                <button type="submit" class="btn-purple">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <a href="payments.php" class="btn-purple">
                    <i class="fas fa-undo"></i>
                    Reset
                </a>

            </form>

        </section>

        <section class="table-container">

            <h3 style="color:#4B006E; margin-bottom:18px;">
                Payment Records
            </h3>

            <table class="payment-table">

                <thead>
                    <tr>
                        <th>Payment Reference</th>
                        <th>Booking Reference</th>
                        <th>Guest Name</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($payments)): ?>

                    <?php foreach ($payments as $payment): ?>

                        <tr>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($payment['payment_reference']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($payment['booking_reference'] ?? 'N/A') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($payment['full_name'] ?? 'N/A') ?>
                            </td>

                            <td>
                                $<?= number_format((float)$payment['amount'], 2) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(paymentMethodLabel($payment['payment_method'])) ?>
                            </td>

                            <td>
                                <?= $payment['payment_date']
                                    ? date('M d, Y H:i', strtotime($payment['payment_date']))
                                    : 'Not recorded' ?>
                            </td>

                            <td>
                                <span class="status-badge <?= paymentStatusClass($payment['payment_status']) ?>">
                                    <?= htmlspecialchars($payment['payment_status']) ?>
                                </span>
                            </td>

                            <td>

                                <a
                                    href="view_payment.php?id=<?= (int)$payment['id'] ?>"
                                    class="action-btn view-btn"
                                    title="View Payment"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>

                                <form
                                    method="POST"
                                    action="payments.php"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this payment record?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="payment_id"
                                        value="<?= (int)$payment['id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <button
                                        type="submit"
                                        class="action-btn delete-btn"
                                        title="Delete Payment"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                            <p>No payment records found.</p>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </section>

    </main>

</div>

</body>
</html>
