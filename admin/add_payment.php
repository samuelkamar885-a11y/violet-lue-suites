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

$methods = [
    'cash',
    'bank-transfer',
    'mobile-money',
    'card',
    'other'
];

$statuses = [
    'pending',
    'paid',
    'failed',
    'refunded'
];

$selectedReservation = (int)($_GET['reservation_id'] ?? 0);

try {
    $stmt = $pdo->query("
        SELECT id, booking_reference, full_name,
               room_type, check_in, check_out, status
        FROM reservations
        ORDER BY created_at DESC
    ");

    $reservations = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $reservations = [];
    $error = 'Unable to load reservations.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {

        $reservationId = filter_input(
            INPUT_POST,
            'reservation_id',
            FILTER_VALIDATE_INT
        );

        $amountInput = trim($_POST['amount'] ?? '');
        $method = $_POST['payment_method'] ?? '';
        $status = $_POST['payment_status'] ?? 'pending';
        $transactionReference = trim($_POST['transaction_reference'] ?? '');
        $paymentDateInput = trim($_POST['payment_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$reservationId) {
            $error = 'Please select a valid reservation.';
        } elseif (
            $amountInput === '' ||
            !is_numeric($amountInput) ||
            (float)$amountInput <= 0 ||
            (float)$amountInput > 99999999.99
        ) {
            $error = 'Enter a valid payment amount.';
        } elseif (!in_array($method, $methods, true)) {
            $error = 'Please select a valid payment method.';
        } elseif (!in_array($status, $statuses, true)) {
            $error = 'Please select a valid payment status.';
        } elseif (strlen($transactionReference) > 100) {
            $error = 'Transaction reference must not exceed 100 characters.';
        } elseif (strlen($notes) > 5000) {
            $error = 'Payment notes must not exceed 5000 characters.';
        } else {

            $paymentDate = null;

            if ($paymentDateInput !== '') {
                $dateObject = DateTime::createFromFormat(
                    'Y-m-d\TH:i',
                    $paymentDateInput
                );

                if (
                    !$dateObject ||
                    $dateObject->format('Y-m-d\TH:i') !== $paymentDateInput
                ) {
                    $error = 'Please enter a valid payment date.';
                } else {
                    $paymentDate = $dateObject->format('Y-m-d H:i:s');
                }
            }

            if (!$error && $status === 'paid' && !$paymentDate) {
                $paymentDate = date('Y-m-d H:i:s');
            }

            if (!$error) {
                try {
                    $pdo->beginTransaction();

                    $check = $pdo->prepare("
                        SELECT id
                        FROM reservations
                        WHERE id = :id
                        FOR UPDATE
                    ");

                    $check->execute(['id' => $reservationId]);

                    if (!$check->fetch()) {
                        throw new RuntimeException(
                            'The selected reservation does not exist.'
                        );
                    }

                    $paymentReference = 'VLP-' .
                        date('Ymd') . '-' .
                        strtoupper(bin2hex(random_bytes(4)));

                    $stmt = $pdo->prepare("
                        INSERT INTO payments (
                            reservation_id,
                            payment_reference,
                            amount,
                            payment_method,
                            payment_status,
                            transaction_reference,
                            payment_date,
                            notes
                        ) VALUES (
                            :reservation_id,
                            :payment_reference,
                            :amount,
                            :payment_method,
                            :payment_status,
                            :transaction_reference,
                            :payment_date,
                            :notes
                        )
                    ");

                    $stmt->execute([
                        'reservation_id' => $reservationId,
                        'payment_reference' => $paymentReference,
                        'amount' => number_format((float)$amountInput, 2, '.', ''),
                        'payment_method' => $method,
                        'payment_status' => $status,
                        'transaction_reference' => $transactionReference ?: null,
                        'payment_date' => $paymentDate,
                        'notes' => $notes ?: null
                    ]);

                    $pdo->commit();

                    $_SESSION['payment_success'] =
                        'Payment recorded successfully. Reference: ' .
                        $paymentReference;

                    header('Location: payments.php');
                    exit;

                } catch (Throwable $e) {

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    error_log($e->getMessage());

                    $error = $e instanceof RuntimeException
                        ? $e->getMessage()
                        : 'Unable to record payment. Please try again.';
                }
            }
        }

        $selectedReservation = (int)($_POST['reservation_id'] ?? 0);
    }
}

$oldAmount = $_POST['amount'] ?? '';
$oldMethod = $_POST['payment_method'] ?? '';
$oldStatus = $_POST['payment_status'] ?? 'paid';
$oldTransaction = $_POST['transaction_reference'] ?? '';
$oldDate = $_POST['payment_date'] ?? '';
$oldNotes = $_POST['notes'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Record Payment | VIOLET-LUE</title>

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
        }

        .payment-form-container {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
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
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
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
            min-height: 110px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4B006E;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #4B006E;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary {
            background: #eee;
            color: #333;
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .help-text {
            color: #777;
            font-size: 12px;
            margin-top: 5px;
        }

        @media(max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .payment-form-container {
                padding: 18px;
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
                    <i class="fas fa-plus-circle"></i>
                    Record New Payment
                </h1>
                <p>Enter the payment details for a reservation.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="payment-form-container">

            <form method="POST" action="add_payment.php">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-grid">

                    <div class="form-group full-width">

                        <label for="reservation_id">
                            Select Reservation *
                        </label>

                        <select name="reservation_id"
                                id="reservation_id"
                                required>

                            <option value="">-- Select Reservation --</option>

                            <?php foreach ($reservations as $reservation): ?>

                                <option
                                    value="<?= (int)$reservation['id'] ?>"
                                    <?= $selectedReservation === (int)$reservation['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        $reservation['booking_reference'] .
                                        ' - ' .
                                        $reservation['full_name'] .
                                        ' (' .
                                        $reservation['check_in'] .
                                        ' to ' .
                                        $reservation['check_out'] .
                                        ')'
                                    ) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <p class="help-text">
                            Choose the reservation associated with this payment.
                        </p>

                    </div>

                    <div class="form-group">

                        <label for="amount">Payment Amount *</label>

                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            min="0.01"
                            max="99999999.99"
                            step="0.01"
                            value="<?= htmlspecialchars($oldAmount) ?>"
                            placeholder="Enter amount"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="payment_method">Payment Method *</label>

                        <select name="payment_method"
                                id="payment_method"
                                required>

                            <option value="">-- Select Method --</option>

                            <option value="cash" <?= $oldMethod === 'cash' ? 'selected' : '' ?>>
                                Cash
                            </option>

                            <option value="bank-transfer" <?= $oldMethod === 'bank-transfer' ? 'selected' : '' ?>>
                                Bank Transfer
                            </option>

                            <option value="mobile-money" <?= $oldMethod === 'mobile-money' ? 'selected' : '' ?>>
                                Mobile Money
                            </option>

                            <option value="card" <?= $oldMethod === 'card' ? 'selected' : '' ?>>
                                Card
                            </option>

                            <option value="other" <?= $oldMethod === 'other' ? 'selected' : '' ?>>
                                Other
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="payment_status">Payment Status *</label>

                        <select name="payment_status"
                                id="payment_status"
                                required>

                            <?php foreach ($statuses as $status): ?>

                                <option value="<?= $status ?>"
                                    <?= $oldStatus === $status ? 'selected' : '' ?>>
                                    <?= ucfirst($status) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="transaction_reference">
                            Transaction Reference
                        </label>

                        <input
                            type="text"
                            name="transaction_reference"
                            id="transaction_reference"
                            maxlength="100"
                            value="<?= htmlspecialchars($oldTransaction) ?>"
                            placeholder="Enter transaction reference"
                        >

                    </div>

                    <div class="form-group full-width">

                        <label for="payment_date">Payment Date and Time</label>

                        <input
                            type="datetime-local"
                            name="payment_date"
                            id="payment_date"
                            value="<?= htmlspecialchars($oldDate) ?>"
                        >

                        <p class="help-text">
                            For paid payments, leaving this blank records the current date and time.
                        </p>

                    </div>

                    <div class="form-group full-width">

                        <label for="notes">Payment Notes</label>

                        <textarea
                            name="notes"
                            id="notes"
                            maxlength="5000"
                            placeholder="Additional payment information..."
                        ><?= htmlspecialchars($oldNotes) ?></textarea>

                    </div>

                </div>

                <div class="form-actions">

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Save Payment
                    </button>

                    <a href="payments.php" class="btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>