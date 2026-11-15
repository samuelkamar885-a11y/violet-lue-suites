<?php
require_once __DIR__ . '/includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= websiteSetting('business_name') ?> | Liberia</title>

    <meta name="description"
        content="Experience luxury, comfort and exceptional hospitality at <?= websiteSetting('business_name') ?> in Liberia.">

    <meta name="author" content="<?= websiteSetting('business_name') ?>">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

    <!-- TOP CONTACT BAR -->

    <div class="top-bar">

        <div class="top-contact">

            <span>
                <i class="fas fa-location-dot"></i>
                <?= websiteSetting('address') ?>
            </span>

            <span>
                <i class="fas fa-phone"></i>
                <?= websiteSetting('phone') ?>
            </span>

            <span>
                <i class="fas fa-envelope"></i>
                <?= websiteSetting('email') ?>
            </span>

        </div>

        <div class="top-social">

            <span>Follow Us:</span>

            <a href="#" aria-label="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>

            <a href="#" aria-label="Twitter">
                <i class="fab fa-twitter"></i>
            </a>

            <a href="#" aria-label="Instagram">
                <i class="fab fa-instagram"></i>
            </a>

            <a href="#" aria-label="YouTube">
                <i class="fab fa-youtube"></i>
            </a>

        </div>

    </div>


    <!-- MAIN HEADER -->

    <header class="main-header">

        <div class="header-container">

            <!-- LOGO -->

            <a href="index.php" class="logo">

                <img src="assets/images/logo.jpeg"
                    alt="<?= websiteSetting('business_name') ?> Logo">

                <div class="logo-text">

                    <h1><?= websiteSetting('business_name') ?></h1>

                    <p><?= websiteSetting('tagline') ?></p>

                </div>

            </a>


            <!-- MOBILE MENU BUTTON -->

            <button class="menu-toggle"
                type="button"
                aria-label="Open navigation menu"
                aria-controls="primary-navigation"
                aria-expanded="false">

                <i class="fas fa-bars" aria-hidden="true"></i>

            </button>


            <!-- NAVIGATION -->

            <nav class="navbar" aria-label="Main navigation">

                <ul id="primary-navigation">

                    <li>
                        <a href="index.php" class="active">Home</a>
                    </li>

                    <li>
                        <a href="pages/about.php">About Us</a>
                    </li>

                    <li>
                        <a href="pages/rooms.php">Rooms & Apartments</a>
                    </li>

                    <li>
                        <a href="pages/services.php">Our Services</a>
                    </li>

                    <li>
                        <a href="pages/gallery.php">Gallery</a>
                    </li>

                    <li>
                        <a href="pages/reservations.php">Online Reservations</a>
                    </li>

                    <li>
                        <a href="pages/contact.php">Contact Us</a>
                    </li>

                    <!-- ADMIN LOGIN -->

                    <li>
                        <a href="admin/login.php">
                            <i class="fas fa-lock"></i>
                            Admin Login
                        </a>
                    </li>

                </ul>

            </nav>

        </div>

    </header>


    <!-- HERO / WELCOME SECTION -->

    <section class="hero">

        <div class="hero-overlay"></div>

        <div class="hero-container">

            <div class="hero-content">

                <span class="welcome-text">WELCOME TO</span>

                <h2>
                    <?= websiteSetting('business_name') ?>
                </h2>

                <h3><?= websiteSetting('tagline') ?></h3>

                <p>
                    Experience luxury, comfort and exceptional
                    hospitality in the heart of Liberia.
                </p>

                <div class="hero-buttons">
    
                    <a href="pages/rooms.php" class="btn btn-gold">
                        Explore Our Rooms
                        <i class="fas fa-chevron-right"></i>
                    </a>

                    <a href="pages/reservations.php" class="btn btn-purple">
                        Book Now
                        <i class="fas fa-chevron-right"></i>
                    </a>

                </div>

            </div>


            <!-- BOOKING FORM -->

            <div class="booking-box">

                <div class="booking-title">

                    <i class="fas fa-calendar-alt"></i>

                    <h3>Book Your Stay</h3>

                </div>

                <form action="pages/reservations.php" method="GET">
                    <button type="submit" class="availability-btn">
    <i class="fas fa-search"></i>
    Check Availability
</button>

                    <div class="form-group">

                        <label for="room_type">Room Type</label>

                        <select id="room_type" name="room_type" required>

                            <option value="">Select Room Type</option>

                            <option value="luxury-room">
                                Luxury Room
                            </option>

                            <option value="self-contained-apartment">
                                Self-Contained Apartment
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="check_in">Check-in Date</label>

                        <input type="date"
                            id="check_in"
                            name="check_in"
                            required>

                    </div>

                    <div class="form-group">

                        <label for="check_out">Check-out Date</label>

                        <input type="date"
                            id="check_out"
                            name="check_out"
                            required>

                    </div>

                    <div class="form-group">

                        <label for="guests">Number of Guests</label>

                        <select id="guests" name="guests" required>

                            <option value="1">1 Guest</option>
                            <option value="2" selected>2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5 Guests</option>
                            <option value="6">6 Guests</option>

                        </select>

                    </div>

                    <button type="submit" class="availability-btn">

                        <i class="fas fa-search"></i>

                        Check Availability

                    </button>

                </form>

            </div>

        </div>

    </section>


    <!-- FACILITIES SECTION -->

    <section class="facilities">

        <div class="facility-item">
            <i class="fas fa-gem"></i>
            <p>Luxury Rooms<br>& Apartments</p>
        </div>

        <div class="facility-item">
            <i class="fas fa-wifi"></i>
            <p>Free Wi-Fi<br>Internet</p>
        </div>

        <div class="facility-item">
            <i class="fas fa-car"></i>
            <p>Secure<br>Parking</p>
        </div>

        <div class="facility-item">
    <i class="fas fa-smoking-ban"></i>
    <p>No Smoking<br>In Rooms</p>
</div>

        <div class="facility-item">
            <i class="fas fa-shield-alt"></i>
            <p>24-Hour<br>Security</p>
        </div>

        <div class="facility-item">
            <i class="fas fa-plane"></i>
            <p>Airport<br>Pickup</p>
        </div>

    </section>


    <!-- ABOUT US SECTION -->

    <section class="about-section">

        <div class="about-image">

            <img src="assets/images/luxury-bedroom.jpeg"
                alt="Luxury guest room">

        </div>

        <div class="about-content">

            <span class="section-label">ABOUT US</span>

            <h2>A Home Away From Home</h2>

            <p>
                <?= websiteSetting('business_name') ?> is a hospitality
                brand in Liberia, offering luxurious rooms and
                fully furnished apartments for families,
                tourists and business travelers.
            </p>

            <p>
                Our commitment is to provide comfortable,
                secure, affordable and quality accommodation
                with excellent customer service.
            </p>

            <a href="pages/about.php" class="btn btn-purple">
                Learn More
                <i class="fas fa-chevron-right"></i>
            </a>

        </div>


        <!-- BRAND VALUES -->

        <div class="about-values">

            <h2>Stay Relaxed. Feel at Home.</h2>

            <div class="flower-divider">
                <i class="fas fa-spa"></i>
            </div>

            <div class="value-items">

                <div class="value-item">
                    <i class="fas fa-bed"></i>
                    <h3>Comfort</h3>
                </div>

                <div class="value-item">
                    <i class="fas fa-handshake"></i>
                    <h3>Hospitality</h3>
                </div>

                <div class="value-item">
                    <i class="fas fa-thumbs-up"></i>
                    <h3>Excellence</h3>
                </div>

            </div>

        </div>

    </section>


    <!-- FOOTER -->

    <footer class="main-footer">

        <div class="footer-container">

            <div class="footer-brand">

                <h2><?= websiteSetting('business_name') ?></h2>

                <p><?= websiteSetting('tagline') ?></p>

            </div>

            <div class="footer-slogan">

                <h2>Stay Relaxed. Feel at Home.</h2>

            </div>

            <div class="footer-copyright">

                <p>
                    &copy; <span id="current-year">2026</span>
                    <?= websiteSetting('business_name') ?>.
                    All Rights Reserved.
                </p>

                <p class="admin-login-link">
                    <a href="admin/login.php">
                        <i class="fas fa-lock"></i>
                        Admin Login
                    </a>
                </p>

            </div>

        </div>

    </footer>


    <!-- WEBSITE JAVASCRIPT -->

    <script src="assets/js/script.js" defer></script>

</body>
</html>
