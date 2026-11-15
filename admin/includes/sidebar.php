<?php
$activePage = $activePage ?? 'dashboard';
?>

<aside class="admin-sidebar">

    <div class="sidebar-brand">

        <img
            src="../assets/images/logo.jpeg"
            alt="Violet-Lue Logo"
        >

        <h2>VIOLET-LUE</h2>
        <p>ADMINISTRATION</p>

    </div>

    <nav class="sidebar-navigation">

        <p class="sidebar-heading">MAIN MENU</p>

        <a href="dashboard.php"
           class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="reservations.php"
           class="<?= $activePage === 'reservations' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Reservations</span>
        </a>

        <a href="rooms.php"
           class="<?= $activePage === 'rooms' ? 'active' : '' ?>">
            <i class="fas fa-bed"></i>
            <span>Rooms & Apartments</span>
        </a>

        <a href="guests.php"
           class="<?= $activePage === 'guests' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Guests</span>
        </a>

        <p class="sidebar-heading">MANAGEMENT</p>

        <a href="payments.php"
           class="<?= $activePage === 'payments' ? 'active' : '' ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Payments</span>
        </a>

        <a href="reports.php"
           class="<?= $activePage === 'reports' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>

        <a href="settings.php"
           class="<?= $activePage === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>

    </nav>

    <div class="sidebar-bottom">

        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>
