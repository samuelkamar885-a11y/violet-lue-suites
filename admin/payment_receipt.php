<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$paymentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$paymentId || $paymentId < 1) {
    header('Location: payments.php');
    exit;
}

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
    error_log('Payment receipt error: ' . $e->getMessage());
    exit('Unable to generate payment receipt.');
}

function receiptMethodLabel($method)
{
    return ucwords(str_replace('-', ' ', $method));
}

$receiptDate = $payment['payment_date']
    ? date('F d, Y h:i A', strtotime($payment['payment_date']))
    : 'Not recorded';

$roomType = $payment['room_type']
    ? ucwords(str_replace('-', ' ', $payment['room_type']))
    : 'N/A';

$guestName = $payment['full_name'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Payment Receipt - <?= htmlspecialchars($payment['payment_reference']) ?>
    </title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f4f1f5;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }

        .receipt-container {
            max-width: 750px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            border-bottom: 3px solid #D4AF37;
            padding-bottom: 22px;
            margin-bottom: 25px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand img {
            width: 85px;
            height: 85px;
            object-fit: contain;
            border-radius: 8px;
        }

        .brand h1 {
            color: #4B006E;
            font-size: 23px;
            margin: 0 0 5px;
        }

        .brand p {
            color: #777;
            margin: 0;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .receipt-title {
            text-align: right;
        }

        .receipt-title h2 {
            color: #4B006E;
            margin: 0 0 7px;
            font-size: 22px;
        }

        .receipt-title p {
            margin: 0;
            color: #777;
            font-size: 12px;
        }

        .receipt-status {
            display: inline-block;
            margin-top: 10px;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-status.paid {
            background: #d1e7dd;
            color: #0f5132;
        }

        .receipt-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .receipt-status.failed {
            background: #f8d7da;
            color: #842029;
        }

        .receipt-status.refunded {
            background: #cfe2ff;
            color: #084298;
        }

        .receipt-status.unknown {
            background: #e9ecef;
            color: #333;
        }

        .receipt-reference {
            background: #f8f5fa;
            border-left: 5px solid #D4AF37;
            padding: 17px;
            margin-bottom: 25px;
            border-radius: 6px;
        }

        .receipt-reference small {
            color: #777;
            display: block;
            margin-bottom: 7px;
        }

        .receipt-reference strong {
            color: #4B006E;
            font-size: 18px;
            overflow-wrap: anywhere;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h3 {
            color: #4B006E;
            font-size: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .detail label {
            display: block;
            color: #888;
            font-size: 11px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .detail p {
            margin: 0;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .amount-box {
            background: #4B006E;
            color: #fff;
            padding: 22px;
            border-radius: 9px;
            text-align: center;
            margin: 25px 0;
        }

        .amount-box small {
            display: block;
            opacity: .85;
            margin-bottom: 8px;
        }

        .amount-box h2 {
            font-size: 32px;
            margin: 0;
            color: #F8D568;
        }

        .receipt-footer {
            border-top: 1px dashed #ccc;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: #777;
            font-size: 12px;
        }

        .receipt-footer h3 {
            color: #4B006E;
            margin-bottom: 7px;
        }

        .receipt-actions {
            max-width: 750px;
            margin: 20px auto 0;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .receipt-actions a,
        .receipt-actions button {
            padding: 12px 20px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .print-btn {
            background: #D4AF37;
            color: #260033;
        }

        .back-btn {
            background: #4B006E;
            color: #fff;
        }

        @media(max-width: 600px) {

            body {
                padding: 12px;
            }

            .receipt-container {
                padding: 20px;
            }

            .receipt-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .receipt-title {
                text-align: left;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .brand img {
                width: 65px;
                height: 65px;
            }
        }

        @media print {

            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .receipt-container {
                max-width: 100%;
                padding: 15px;
                box-shadow: none;
                border-radius: 0;
            }

            .receipt-actions {
                display: none !important;
            }

            .amount-box {
                background: #4B006E !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt-status,
            .receipt-reference {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

    </style>

</head>

<body>

    <div class="receipt-container">

        <div class="receipt-header">

            <div class="brand">

                <img src="../assets/images/logo.jpeg"
                     alt="Violet-Lue Logo">

                <div>
                    <h1>VIOLET-LUE</h1>
                    <p>SUITES & APARTMENTS</p>
                    <p>Your Comfort, Our Priority.</p>
                </div>

            </div>

            <div class="receipt-title">

                <h2>PAYMENT RECEIPT</h2>

                <p>Official Payment Record</p>

                <span class="receipt-status <?= htmlspecialchars(in_array($payment['payment_status'], ['paid', 'pending', 'failed', 'refunded'], true) ? $payment['payment_status'] : 'unknown') ?>">
                    <?= htmlspecialchars($payment['payment_status']) ?>
                </span>

            </div>

        </div>

        <div class="receipt-reference">

            <small>PAYMENT REFERENCE</small>

            <strong>
                <?= htmlspecialchars($payment['payment_reference']) ?>
            </strong>

        </div>

        <div class="amount-box">

            <small>PAYMENT AMOUNT</small>

            <h2>
                $<?= number_format((float)$payment['amount'], 2) ?>
            </h2>

        </div>

        <div class="section">

            <h3>
                <i class="fas fa-user"></i>
                Guest Information
            </h3>

            <div class="details-grid">

                <div class="detail">
                    <label>Guest Name</label>
                    <p><?= htmlspecialchars($guestName) ?></p>
                </div>

                <div class="detail">
                    <label>Email Address</label>
                    <p><?= htmlspecialchars($payment['email'] ?? 'N/A') ?></p>
                </div>

                <div class="detail">
                    <label>Phone Number</label>
                    <p><?= htmlspecialchars($payment['phone'] ?? 'N/A') ?></p>
                </div>

                <div class="detail">
                    <label>Booking Reference</label>
                    <p><?= htmlspecialchars($payment['booking_reference'] ?? 'N/A') ?></p>
                </div>

            </div>

        </div>

        <div class="section">

            <h3>
                <i class="fas fa-bed"></i>
                Reservation Information
            </h3>

            <div class="details-grid">

                <div class="detail">
                    <label>Room Type</label>
                    <p><?= htmlspecialchars($roomType) ?></p>
                </div>

                <div class="detail">
                    <label>Number of Guests</label>
                    <p><?= htmlspecialchars((string)($payment['reservation_guests'] ?? 'N/A')) ?></p>
                </div>

                <div class="detail">
                    <label>Check-in</label>
                    <p>
                        <?= !empty($payment['check_in'])
                            ? date('F d, Y', strtotime($payment['check_in']))
                            : 'N/A' ?>
                    </p>
                </div>

                <div class="detail">
                    <label>Check-out</label>
                    <p>
                        <?= !empty($payment['check_out'])
                            ? date('F d, Y', strtotime($payment['check_out']))
                            : 'N/A' ?>
                    </p>
                </div>

            </div>

        </div>

        <div class="section">

            <h3>
                <i class="fas fa-money-check-alt"></i>
                Payment Information
            </h3>

            <div class="details-grid">

                <div class="detail">
                    <label>Payment Method</label>
                    <p><?= htmlspecialchars(receiptMethodLabel($payment['payment_method'])) ?></p>
                </div>

                <div class="detail">
                    <label>Transaction Reference</label>
                    <p><?= htmlspecialchars($payment['transaction_reference'] ?: 'Not provided') ?></p>
                </div>

                <div class="detail">
                    <label>Payment Date</label>
                    <p><?= htmlspecialchars($receiptDate) ?></p>
                </div>

                <div class="detail">
                    <label>Receipt Generated</label>
                    <p><?= date('F d, Y h:i A') ?></p>
                </div>

            </div>

        </div>

        <?php if (!empty($payment['notes'])): ?>

            <div class="section">

                <h3>
                    <i class="fas fa-sticky-note"></i>
                    Payment Notes
                </h3>

                <p><?= nl2br(htmlspecialchars($payment['notes'])) ?></p>

            </div>

        <?php endif; ?>

        <div class="receipt-footer">

            <h3>Thank You for Choosing VIOLET-LUE!</h3>

            <p>Your Comfort, Our Priority.</p>

            <p>
                Monrovia, Liberia |
                +231 777 123 456 |
                info@violetlue.com
            </p>

            <p>
                This receipt is an official record of the payment information
                stored in our system.
            </p>

            <p>
                &copy; <?= date('Y') ?> VIOLET-LUE Suites & Apartments.
                All Rights Reserved.
            </p>

        </div>

    </div>

    <div class="receipt-actions">

        <a href="view_payment.php?id=<?= (int)$paymentId ?>"
           class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Payment
        </a>

        <button type="button"
                class="print-btn"
                onclick="window.print()">
            <i class="fas fa-print"></i>
            Print Receipt
        </button>

    </div>

</body>
</html>
