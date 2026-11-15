<?php

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Display Messages
|--------------------------------------------------------------------------
*/

function showMessage($title, $message, $success = false)
{
    $color = $success ? '#198754' : '#dc3545';

    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>

        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f8f5fa;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }

            .message {
                background: white;
                padding: 35px;
                border-radius: 12px;
                text-align: center;
                max-width: 500px;
                width: calc(100% - 40px);
                box-shadow: 0 5px 25px rgba(0,0,0,.1);
            }

            h1 {
                color: ' . $color . ';
            }

            a {
                display: inline-block;
                background: #4B006E;
                color: white;
                padding: 12px 22px;
                text-decoration: none;
                border-radius: 6px;
                margin-top: 15px;
            }

            a:hover {
                background: #260033;
            }
        </style>
    </head>

    <body>
        <div class="message">
            <h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>
            <p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>
            <a href="../pages/reservations.php">Return to Reservations</a>
        </div>
    </body>
    </html>';

    exit;
}

/*
|--------------------------------------------------------------------------
| Accept Only POST Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showMessage(
        'Invalid Request',
        'Please submit the reservation form.'
    );
}

/*
|--------------------------------------------------------------------------
| Safely Read Form Values
|--------------------------------------------------------------------------
*/

function getPostValue($key)
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

$roomType = getPostValue('room_type');
$checkIn = getPostValue('check_in');
$checkOut = getPostValue('check_out');

$guests = filter_var(
    $_POST['guests'] ?? null,
    FILTER_VALIDATE_INT
);

$fullName = getPostValue('full_name');
$email = getPostValue('email');
$phone = getPostValue('phone');
$specialRequests = getPostValue('special_requests');

/*
|--------------------------------------------------------------------------
| Allowed Room Types
|--------------------------------------------------------------------------
*/

$allowedRooms = [
    'luxury-room',
    'self-contained-apartment'
];

/*
|--------------------------------------------------------------------------
| Validate Date Format
|--------------------------------------------------------------------------
*/

function validDate($date)
{
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    return $dateObject &&
        $dateObject->format('Y-m-d') === $date;
}

/*
|--------------------------------------------------------------------------
| Form Validation
|--------------------------------------------------------------------------
*/

// Validate room type
if (!in_array($roomType, $allowedRooms, true)) {
    showMessage(
        'Invalid Room',
        'Please select a valid room type.'
    );
}

// Validate dates
if (!validDate($checkIn) || !validDate($checkOut)) {
    showMessage(
        'Invalid Date',
        'Please enter valid check-in and check-out dates.'
    );
}

// Check-out must be after check-in
if ($checkOut <= $checkIn) {
    showMessage(
        'Invalid Dates',
        'Check-out must be after check-in.'
    );
}

// Check-in cannot be in the past
if ($checkIn < date('Y-m-d')) {
    showMessage(
        'Invalid Check-in Date',
        'Check-in date cannot be in the past.'
    );
}

// Validate number of guests
if ($guests === false || $guests < 1) {
    showMessage(
        'Invalid Guests',
        'Please select a valid number of guests.'
    );
}

// Validate full name
if ($fullName === '' || strlen($fullName) > 150) {
    showMessage(
        'Invalid Name',
        'Please enter your full name.'
    );
}

// Validate email
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150
) {
    showMessage(
        'Invalid Email',
        'Please enter a valid email address.'
    );
}

// Validate phone
if ($phone === '' || strlen($phone) > 30) {
    showMessage(
        'Invalid Phone',
        'Please enter a valid phone number.'
    );
}

// Validate special requests
if (strlen($specialRequests) > 5000) {
    showMessage(
        'Request Too Long',
        'Special requests cannot exceed 5000 characters.'
    );
}

/*
|--------------------------------------------------------------------------
| Reservation Processing
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | STEP 1: Start Database Transaction
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Find Existing Guest
    |--------------------------------------------------------------------------
    */

    $guestStmt = $pdo->prepare("
        SELECT
            id,
            full_name,
            email,
            phone
        FROM guests
        WHERE email = :email
           OR phone = :phone
        FOR UPDATE
    ");

    $guestStmt->execute([
        ':email' => $email,
        ':phone' => $phone
    ]);

    $matchedGuests = $guestStmt->fetchAll(PDO::FETCH_ASSOC);

    // Collect unique matching guest IDs
    $matchedGuestIds = [];

    foreach ($matchedGuests as $matchedGuest) {
        $matchedGuestIds[] = (int) $matchedGuest['id'];
    }

    $matchedGuestIds = array_unique($matchedGuestIds);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Check for Conflicting Guest Records
    |--------------------------------------------------------------------------
    */

    if (count($matchedGuestIds) > 1) {

        $pdo->rollBack();

        showMessage(
            'Guest Verification Required',
            'The email address and phone number match different registered guests. Please contact the hotel administrator to verify your details before submitting this reservation.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 4: Reuse or Create Guest
    |--------------------------------------------------------------------------
    */

    if (count($matchedGuestIds) === 1) {

        // Existing guest found
        $guestId = (int) reset($matchedGuestIds);

        // Use the official registered guest information
        foreach ($matchedGuests as $matchedGuest) {

            if ((int) $matchedGuest['id'] === $guestId) {

                $fullName = $matchedGuest['full_name'];
                $email = $matchedGuest['email'];
                $phone = $matchedGuest['phone'];

                break;
            }
        }

    } else {

        // New guest

        $guestReference = 'VLG-' . date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(4)));

        $insertGuest = $pdo->prepare("
            INSERT INTO guests (
                guest_reference,
                full_name,
                email,
                phone
            ) VALUES (
                :guest_reference,
                :full_name,
                :email,
                :phone
            )
        ");

        $insertGuest->execute([
            ':guest_reference' => $guestReference,
            ':full_name' => $fullName,
            ':email' => $email,
            ':phone' => $phone
        ]);

        $guestId = (int) $pdo->lastInsertId();
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 5: Generate Booking Reference
    |--------------------------------------------------------------------------
    */

    $bookingReference = 'VLS-' . date('Ymd') . '-' .
        strtoupper(bin2hex(random_bytes(4)));

    /*
    |--------------------------------------------------------------------------
    | STEP 6: Save Reservation
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO reservations (
            guest_id,
            booking_reference,
            room_type,
            check_in,
            check_out,
            guests,
            full_name,
            email,
            phone,
            special_requests
        ) VALUES (
            :guest_id,
            :booking_reference,
            :room_type,
            :check_in,
            :check_out,
            :guests,
            :full_name,
            :email,
            :phone,
            :special_requests
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':guest_id' => $guestId,
        ':booking_reference' => $bookingReference,
        ':room_type' => $roomType,
        ':check_in' => $checkIn,
        ':check_out' => $checkOut,
        ':guests' => $guests,
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone,
        ':special_requests' => $specialRequests
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 7: Commit Database Transaction
    |--------------------------------------------------------------------------
    |
    | The reservation is now safely stored.
    | Email delivery happens after this point.
    |
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | STEP 8: Send Reservation Acknowledgement Email
    |--------------------------------------------------------------------------
    |
    | This email informs the guest that the request was received.
    | It does NOT mean the reservation is confirmed.
    |
    */

    $emailSent = false;

    try {

        // Load the email helper if it exists
        $mailerFile = __DIR__ . '/../includes/mailer.php';

        if (!file_exists($mailerFile)) {
            throw new RuntimeException(
                'Email helper file was not found.'
            );
        }

        require_once $mailerFile;

        if (!function_exists('sendHotelEmail')) {
            throw new RuntimeException(
                'The sendHotelEmail function is unavailable.'
            );
        }

        // Format room name
        $roomName = $roomType === 'luxury-room'
            ? 'Luxury Room'
            : 'Self-Contained Apartment';

        // Safely prepare HTML email values
        $safeName = htmlspecialchars(
            $fullName,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeReference = htmlspecialchars(
            $bookingReference,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeRoom = htmlspecialchars(
            $roomName,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeCheckIn = htmlspecialchars(
            $checkIn,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeCheckOut = htmlspecialchars(
            $checkOut,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeGuests = htmlspecialchars(
            (string) $guests,
            ENT_QUOTES,
            'UTF-8'
        );

        /*
        |--------------------------------------------------------------------------
        | Email Subject
        |--------------------------------------------------------------------------
        */

        $subject = 'Reservation Request Received - ' .
            $bookingReference;

        /*
        |--------------------------------------------------------------------------
        | HTML Email
        |--------------------------------------------------------------------------
        */

        $htmlBody = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reservation Received</title>
        </head>

        <body style="
            margin:0;
            padding:25px;
            background:#f8f5fa;
            font-family:Arial,sans-serif;
            color:#333;
        ">

            <div style="
                max-width:600px;
                margin:auto;
                background:#ffffff;
                border-radius:12px;
                overflow:hidden;
                box-shadow:0 5px 20px rgba(0,0,0,0.08);
            ">

                <div style="
                    background:#4B006E;
                    padding:30px 20px;
                    text-align:center;
                    color:#ffffff;
                ">

                    <h1 style="margin:0;">
                        VIOLET-LUE
                    </h1>

                    <p style="margin:8px 0 0;">
                        SUITES &amp; APARTMENTS
                    </p>

                </div>

                <div style="padding:30px;">

                    <h2 style="color:#4B006E;">
                        Reservation Request Received!
                    </h2>

                    <p>
                        Dear ' . $safeName . ',
                    </p>

                    <p>
                        Thank you for choosing
                        <strong>VIOLET-LUE SUITES &amp; APARTMENTS</strong>.
                    </p>

                    <p>
                        We have successfully received your reservation request.
                        Our team will review your booking and contact you
                        regarding confirmation.
                    </p>

                    <div style="
                        background:#fff8e7;
                        border-left:4px solid #c49a42;
                        padding:15px;
                        margin:20px 0;
                    ">

                        <strong>Reservation Status:</strong>

                        <span style="
                            color:#b7791f;
                            font-weight:bold;
                        ">
                            PENDING CONFIRMATION
                        </span>

                    </div>

                    <h3 style="color:#4B006E;">
                        Your Reservation Details
                    </h3>

                    <table style="
                        width:100%;
                        border-collapse:collapse;
                    ">

                        <tr>
                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                Booking Reference
                            </td>

                            <td style="
                                padding:10px;
                                border-bottom:1px solid #eee;
                                font-weight:bold;
                            ">
                                ' . $safeReference . '
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                Accommodation
                            </td>

                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                ' . $safeRoom . '
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                Check-in
                            </td>

                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                ' . $safeCheckIn . '
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                Check-out
                            </td>

                            <td style="padding:10px;border-bottom:1px solid #eee;">
                                ' . $safeCheckOut . '
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:10px;">
                                Number of Guests
                            </td>

                            <td style="padding:10px;">
                                ' . $safeGuests . '
                            </td>
                        </tr>

                    </table>

                    <p style="margin-top:25px;">
                        Please keep your booking reference for future
                        communication with our team.
                    </p>

                    <p>
                        We look forward to welcoming you.
                    </p>

                    <p>
                        Kind regards,<br>
                        <strong>VIOLET-LUE SUITES &amp; APARTMENTS</strong>
                    </p>

                </div>

                <div style="
                    background:#f3edf6;
                    padding:18px;
                    text-align:center;
                    font-size:13px;
                    color:#666;
                ">
                    Thank you for choosing VIOLET-LUE.
                    <br>
                    This is an automated acknowledgement email.
                </div>

            </div>

        </body>
        </html>
        ';

        /*
        |--------------------------------------------------------------------------
        | Plain Text Email
        |--------------------------------------------------------------------------
        */

        $plainBody =
            "Dear {$fullName},\n\n" .
            "Thank you for choosing VIOLET-LUE SUITES & APARTMENTS.\n\n" .
            "We have received your reservation request.\n" .
            "Your reservation is currently PENDING CONFIRMATION.\n\n" .
            "Booking Reference: {$bookingReference}\n" .
            "Accommodation: {$roomName}\n" .
            "Check-in: {$checkIn}\n" .
            "Check-out: {$checkOut}\n" .
            "Guests: {$guests}\n\n" .
            "Our team will contact you regarding confirmation.\n\n" .
            "Kind regards,\n" .
            "VIOLET-LUE SUITES & APARTMENTS";

        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $emailSent = sendHotelEmail(
            $email,
            $fullName,
            $subject,
            $htmlBody,
            $plainBody
        );

        if (!$emailSent) {
            error_log(
                'Reservation acknowledgement email was not sent for booking: ' .
                $bookingReference
            );
        }

    } catch (Throwable $mailError) {

        // Email problems must not undo a saved reservation
        error_log(
            'Reservation acknowledgement email error for ' .
            $bookingReference . ': ' .
            $mailError->getMessage()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 9: Display Reservation Result
    |--------------------------------------------------------------------------
    */

    $successMessage =
        'Your reservation request has been received. ' .
        'Your booking reference is: ' . $bookingReference . '. ' .
        'Your reservation is currently pending confirmation.';

    if ($emailSent) {

        $successMessage .=
            ' An acknowledgement email has been sent to your email address.';

    } else {

        $successMessage .=
            ' However, we could not send the acknowledgement email at this time. ' .
            'Please keep your booking reference for future communication.';
    }

    showMessage(
        'Reservation Submitted Successfully!',
        $successMessage,
        true
    );

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | STEP 10: Handle Database Errors
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Record technical error privately
    error_log(
        'Reservation processing error: ' . $e->getMessage()
    );

    showMessage(
        'Reservation Error',
        'We could not process your reservation. Please try again later.'
    );
}
