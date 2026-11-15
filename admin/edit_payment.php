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
$success = '';

$paymentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$paymentId || $paymentId < 1) {
    header('Location: payments.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM payments
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $paymentId]);

$payment = $stmt->fetch();

if (!$payment) {
    header('Location: payments.php');
    exit;
}

$oldAmount = $payment['amount'];
$oldMethod = $payment['payment_method'];
$oldStatus = $payment['payment_status'];
$oldTransactionReference = $payment['transaction_reference'] ?? '';
$oldPaymentDate = $payment['payment_date']
    ? date('Y-m-d\TH:i', strtotime($payment['payment_date']))
    : '';
$oldNotes = $payment['notes'] ?? '';

$allowedMethods = [
    'cash',
    'bank-transfer',
    'mobile-money',
    'card',
    'other'
];

$allowedStatuses = [
    'pending',
    'paid',
    'failed',
    'refunded'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {

        $oldAmount = trim($_POST['amount'] ?? '');
        $oldMethod = trim($_POST['payment_method'] ?? '');
        $oldStatus = trim($_POST['payment_status'] ?? '');
        $oldTransactionReference = trim($_POST['transaction_reference'] ?? '');
        $oldPaymentDate = trim($_POST['payment_date'] ?? '');
        $oldNotes = trim($_POST['notes'] ?? '');

        if (!is_numeric($oldAmount) || (float)$oldAmount <= 0) {
            $error = 'Please enter a valid payment amount.';
        } elseif ((float)$oldAmount > 99999999.99) {
            $error = 'The payment amount is too large.';
        } elseif (!in_array($oldMethod, $allowedMethods, true)) {
            $error = 'Please select a valid payment method.';
        } elseif (!in_array($oldStatus, $allowedStatuses, true)) {
            $error = 'Please select a valid payment status.';
        } elseif (strlen($oldTransactionReference) > 100) {
            $error = 'Transaction reference must not exceed 100 characters.';
        } elseif (strlen($oldNotes) > 5000) {
            $error = 'Payment notes must not exceed 5000 characters.';
        } else {

            $paymentDate = null;

            if ($oldPaymentDate !== '') {
                $date = DateTime::createFromFormat(
                    'Y-m-d\TH:i',
                    $oldPaymentDate
                );

                if (!$date || $date->format('Y-m-d\TH:i') !== $oldPaymentDate) {
                    $error = 'Please enter a valid payment date.';
                } else {
                    $paymentDate = $date->format('Y-m-d H:i:s');
                }
            }

            if ($error === '' && $oldStatus === 'paid' && $paymentDate === null) {
                $paymentDate = date('Y-m-d H:i:s');
                $oldPaymentDate = date('Y-m-d\TH:i');
            }

            if ($error === '') {
                try {
                    $update = $pdo->prepare("
                        UPDATE payments
                        SET
                            amount = :amount,
                            payment_method = :payment_method,
                            payment_status = :payment_status,
                            transaction_reference = :transaction_reference,
                            payment_date = :payment_date,
                            notes = :notes
                        WHERE id = :id
                    ");

                    $update->execute([
                        'amount' => $oldAmount,
                        'payment_method' => $oldMethod,
                        'payment_status' => $oldStatus,
                        'transaction_reference' =>
                            $oldTransactionReference !== ''
                                ? $oldTransactionReference
                                : null,
                        'payment_date' => $paymentDate,
                        'notes' => $oldNotes !== ''
                            ? $oldNotes
                            : null,
                        'id' => $paymentId
                    ]);

                    $_SESSION['payment_success'] =
                        'Payment updated successfully.';

                    header('Location: view_payment.php?id=' . $paymentId);
                    exit;

                } catch (PDOException $e) {
                    error_log('Payment update error: ' . $e->getMessage());
                    $error = 'Unable to update payment. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Payment | Violet-Lue Admin</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .admin-main {
            padding: 30px;
        }

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
        }

        .payment-form-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.07);
            max-width: 850px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 110px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4B006E;
        }

        .readonly-field {
            background: #f3f3f3;
            color: #666;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .save-btn,
        .cancel-btn {
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .save-btn {
            background: #4B006E;
            color: white;
        }

        .save-btn:hover {
            background: #35004e;
        }

        .cancel-btn {
            background: #e9ecef;
            color: #333;
        }

        .error-message {
            background: #f8d7da;
            color: #842029;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        @media(max-width: 650px) {
            .admin-main {
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .payment-form-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="admin-main">

        <div class="page-heading">
            <div>
                <h1>
                    <i class="fas fa-edit"></i>
                    Edit Payment
                </h1>
                <p>Update payment information and transaction details.</p>
            </div>
        </div>

        <div class="payment-form-card">

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Payment Reference</label>
                        <input type="text"
                               value="<?= htmlspecialchars($payment['payment_reference']) ?>"
                               class="readonly-field"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label>Reservation ID</label>
                        <input type="text"
                               value="<?= (int)$payment['reservation_id'] ?>"
                               class="readonly-field"
                               readonly>
                    </div>

                    <div class="form-group">
                        <label for="amount">Payment Amount</label>
                        <input type="number"
                               name="amount"
                               id="amount"
                               min="0.01"
                               max="99999999.99"
                               step="0.01"
                               value="<?= htmlspecialchars((string)$oldAmount) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method"
                                id="payment_method"
                                required>
                            <option value="cash" <?= $oldMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="bank-transfer" <?= $oldMethod === 'bank-transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="mobile-money" <?= $oldMethod === 'mobile-money' ? 'selected' : '' ?>>Mobile Money</option>
                            <option value="card" <?= $oldMethod === 'card' ? 'selected' : '' ?>>Card</option>
                            <option value="other" <?= $oldMethod === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_status">Payment Status</label>
                        <select name="payment_status"
                                id="payment_status"
                                required>
                            <option value="pending" <?= $oldStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $oldStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="failed" <?= $oldStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $oldStatus === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="transaction_reference">Transaction Reference</label>
                        <input type="text"
                               name="transaction_reference"
                               id="transaction_reference"
                               maxlength="100"
                               value="<?= htmlspecialchars($oldTransactionReference) ?>">
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="datetime-local"
                               name="payment_date"
                               id="payment_date"
                               value="<?= htmlspecialchars($oldPaymentDate) ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Payment Notes</label>
                        <textarea name="notes"
                                  id="notes"
                                  maxlength="5000"><?= htmlspecialchars($oldNotes) ?></textarea>
                    </div>

                </div>

                <div class="form-actions">

                    <button type="submit" class="save-btn">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>

                    <a href="view_payment.php?id=<?= (int)$paymentId ?>"
                       class="cancel-btn">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>