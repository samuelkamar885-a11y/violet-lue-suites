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
        <title>' . htmlspecialchars($title) . '</title>

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
            <h1>' . htmlspecialchars($title) . '</h1>
            <p>' . nl2br(htmlspecialchars($message)) . '</p>
            <a href="../pages/reservations.html">Return to Reservations</a>
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

if ($checkOut <= $checkIn) {
    showMessage(
        'Invalid Dates',
        'Check-out must be after check-in.'
    );
}

// Validate number of guests
if (!$guests || $guests < 1) {
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

    // Start database transaction
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | STEP 1: Find Existing Guest
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

    $matchedGuests = $guestStmt->fetchAll();

    // Collect unique matching guest IDs
    $matchedGuestIds = [];

    foreach ($matchedGuests as $matchedGuest) {
        $matchedGuestIds[] = (int)$matchedGuest['id'];
    }

    $matchedGuestIds = array_unique($matchedGuestIds);

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Check for Conflicting Guest Records
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
    | STEP 3: Reuse or Create Guest
    |--------------------------------------------------------------------------
    */

    if (count($matchedGuestIds) === 1) {

        /*
         * EXISTING GUEST FOUND
         */

        $guestId = (int)reset($matchedGuestIds);

        // Use the official registered guest information
        foreach ($matchedGuests as $matchedGuest) {

            if ((int)$matchedGuest['id'] === $guestId) {

                $fullName = $matchedGuest['full_name'];
                $email = $matchedGuest['email'];
                $phone = $matchedGuest['phone'];

                break;
            }
        }

    } else {

        /*
         * NEW GUEST
         */

        // Generate unique guest reference
        $guestReference = 'VLG-' . date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(4)));

        // Register new guest
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

        // Get newly created guest ID
        $guestId = (int)$pdo->lastInsertId();
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 4: Generate Booking Reference
    |--------------------------------------------------------------------------
    */

    $bookingReference = 'VLS-' . date('Ymd') . '-' .
        strtoupper(bin2hex(random_bytes(4)));

    /*
    |--------------------------------------------------------------------------
    | STEP 5: Save Reservation
    |--------------------------------------------------------------------------
    |
    | Existing guest details are now synchronized.
    | The reservation receives the correct guest_id.
    |
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
    | STEP 6: Commit Transaction
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | STEP 7: Display Success
    |--------------------------------------------------------------------------
    */

    showMessage(
        'Reservation Submitted Successfully!',
        'Your reservation request has been received. Your booking reference is: ' .
        $bookingReference .
        '. Your reservation is pending confirmation.',
        true
    );

} catch (Throwable $e) {

    // Roll back incomplete database operations
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
