<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$activePage = 'payments';

$paymentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$paymentId || $paymentId < 1) {
    header('Location: payments.php');
    exit;
}

// Display success message after editing
$success = $_SESSION['payment_success'] ?? '';
unset($_SESSION['payment_success']);

try {
    $stmt = $pdo->prepare("
        SELECT
            p.*,
            r.booking_reference,
            r.full_name,
            r.email,
            r.phone,
            r.room_type,
            r.check_in,
            r.check_out,
            r.guests AS reservation_guests
        FROM payments p
        LEFT JOIN reservations r
            ON p.reservation_id = r.id
        WHERE p.id = :id
        LIMIT 1
    ");

    $stmt->execute(['id' => $paymentId]);
    $payment = $stmt->fetch();

    if (!$payment) {
        header('Location: payments.php');
        exit;
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Unable to load payment details.');
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

    <title>View Payment | VIOLET-LUE</title>

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

        .payment-details-container {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
            max-width: 950px;
        }

        .payment-reference {
            background: #f8f5fa;
            border-left: 5px solid #D4AF37;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .payment-reference h2 {
            color: #4B006E;
            margin: 8px 0;
            font-size: 22px;
            overflow-wrap: anywhere;
        }

        .details-section {
            margin-bottom: 28px;
        }

        .details-section h3 {
            color: #4B006E;
            border-bottom: 1px solid #eee;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .detail-item label {
            display: block;
            color: #888;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .detail-item p {
            color: #333;
            font-weight: 600;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 12px;
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

        .status-badge.unknown {
            background: #e9ecef;
            color: #333;
        }

        .payment-amount {
            color: #198754 !important;
            font-size: 24px;
        }

        .payment-notes {
            background: #f8f5fa;
            padding: 15px;
            border-radius: 7px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn-purple,
        .btn-print,
        .btn-edit {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-purple {
            background: #4B006E;
            color: #fff;
        }

        .btn-purple:hover {
            background: #35004e;
            color: #fff;
        }

        .btn-edit {
            background: #198754;
            color: #fff;
        }

        .btn-edit:hover {
            background: #146c43;
            color: #fff;
        }

        .btn-print {
            background: #D4AF37;
            color: #260033;
        }

        .success-message {
            background: #d1e7dd;
            color: #0f5132;
            padding: 14px 18px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        @media(max-width: 650px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .payment-details-container {
                padding: 18px;
            }
        }

        @media print {
            .admin-sidebar,
            .admin-topbar,
            .action-buttons,
            .page-heading,
            .success-message {
                display: none !important;
            }

            .admin-main {
                margin: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .payment-details-container {
                box-shadow: none;
                max-width: 100%;
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
                    <i class="fas fa-file-invoice-dollar"></i>
                    Payment Details
                </h1>
                <p>Complete payment information and reservation details.</p>
            </div>
        </div>

        <section class="payment-details-container">

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="payment-reference">
                <small>PAYMENT REFERENCE</small>

                <h2>
                    <?= htmlspecialchars($payment['payment_reference']) ?>
                </h2>

                <span class="status-badge <?= paymentStatusClass($payment['payment_status']) ?>">
                    <?= htmlspecialchars($payment['payment_status']) ?>
                </span>
            </div>

            <div class="details-section">

                <h3>
                    <i class="fas fa-money-bill-wave"></i>
                    Payment Information
                </h3>

                <div class="details-grid">

                    <div class="detail-item">
                        <label>Payment Amount</label>
                        <p class="payment-amount">
                            $<?= number_format((float)$payment['amount'], 2) ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Payment Method</label>
                        <p>
                            <?= htmlspecialchars(paymentMethodLabel($payment['payment_method'])) ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Payment Status</label>
                        <p>
                            <?= htmlspecialchars(ucfirst($payment['payment_status'])) ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Transaction Reference</label>
                        <p>
                            <?= htmlspecialchars($payment['transaction_reference'] ?: 'Not provided') ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Payment Date</label>
                        <p>
                            <?= $payment['payment_date']
                                ? date('F d, Y h:i A', strtotime($payment['payment_date']))
                                : 'Not recorded' ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Record Created</label>
                        <p>
                            <?= date('F d, Y h:i A', strtotime($payment['created_at'])) ?>
                        </p>
                    </div>

                </div>
            </div>

            <div class="details-section">

                <h3>
                    <i class="fas fa-calendar-check"></i>
                    Reservation Information
                </h3>

                <div class="details-grid">

                    <div class="detail-item">
                        <label>Booking Reference</label>
                        <p>
                            <?= htmlspecialchars($payment['booking_reference'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Guest Name</label>
                        <p>
                            <?= htmlspecialchars($payment['full_name'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Guest Email</label>
                        <p>
                            <?= htmlspecialchars($payment['email'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Guest Phone</label>
                        <p>
                            <?= htmlspecialchars($payment['phone'] ?? 'N/A') ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Room Type</label>
                        <p>
                            <?= htmlspecialchars(
                                ucwords(str_replace('-', ' ', $payment['room_type'] ?? 'N/A'))
                            ) ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Number of Guests</label>
                        <p>
                            <?= htmlspecialchars((string)($payment['reservation_guests'] ?? 'N/A')) ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Check-in Date</label>
                        <p>
                            <?= !empty($payment['check_in'])
                                ? date('F d, Y', strtotime($payment['check_in']))
                                : 'N/A' ?>
                        </p>
                    </div>

                    <div class="detail-item">
                        <label>Check-out Date</label>
                        <p>
                            <?= !empty($payment['check_out'])
                                ? date('F d, Y', strtotime($payment['check_out']))
                                : 'N/A' ?>
                        </p>
                    </div>

                </div>
            </div>

            <div class="details-section">

                <h3>
                    <i class="fas fa-sticky-note"></i>
                    Payment Notes
                </h3>

                <div class="payment-notes">
                    <?= htmlspecialchars($payment['notes'] ?: 'No additional notes.') ?>
                </div>

            </div>

            <div class="action-buttons">

                <a href="payments.php" class="btn-purple">
                    <i class="fas fa-arrow-left"></i>
                    Back to Payments
                </a>

                <a href="edit_payment.php?id=<?= (int)$paymentId ?>"
                   class="btn-edit">
                    <i class="fas fa-edit"></i>
                    Edit Payment
                </a>
                <a href="payment_receipt.php?id=<?= (int)$paymentId ?>"
   class="btn-edit"
   target="_blank">
    <i class="fas fa-receipt"></i>
    Print Receipt
</a>


            </div>

        </section>

    </main>

</div>

</body>
</html>
