<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Redirect if administrator is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Invalid request. Please refresh the page and try again.';

    } elseif ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';

    } else {
        $stmt = $pdo->prepare("
            SELECT id, full_name, username, password_hash
            FROM admin_users
            WHERE username = :username
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_username'] = $admin['username'];

            unset($_SESSION['csrf_token']);

            header('Location: dashboard.php');
            exit;

        } else {
            $error = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | VIOLET-LUE Suites & Apartments</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFDF5;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
            padding: 35px;
            margin: 20px;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(38, 0, 51, 0.12);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-logo img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .login-logo h2 {
            color: #4B006E;
            margin: 0;
        }

        .login-logo p {
            color: #777777;
            margin-top: 6px;
        }

        .login-container label {
            display: block;
            margin: 15px 0 7px;
            font-weight: 600;
        }

        .login-container input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dddddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            background: #4B006E;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-button:hover {
            background: #260033;
        }

        .error-message {
            padding: 12px;
            background: #ffe8e8;
            color: #a00000;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4B006E;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-container">

    <div class="login-logo">
        <img src="../assets/images/logo.jpeg"
             alt="Violet-Lue Logo">

        <h2>VIOLET-LUE</h2>
        <p>Administrator Login</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input type="hidden"
               name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Enter administrator username"
            required
            autocomplete="username"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
            autocomplete="current-password"
        >

        <button type="submit" class="login-button">
            LOGIN TO ADMIN PANEL
        </button>

    </form>

    <a href="../index.php" class="back-link">
        Return to Website
    </a>

</div>

</body>
</html>
