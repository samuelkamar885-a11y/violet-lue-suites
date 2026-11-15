<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gallery | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Explore the rooms, apartments and facilities of VIOLET-LUE Suites & Apartments in Liberia.">

    <meta name="author" content="Violet-Lue Suites & Apartments">

    <!-- WEBSITE CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- GALLERY PAGE CSS -->
    <style>

        /* ==============================
           GALLERY HERO
        ============================== */

        .gallery-hero {
            min-height: 350px;
            background:
                linear-gradient(rgba(45, 0, 65, 0.78), rgba(45, 0, 65, 0.78)),
                url('../assets/images/hotel-exterior.jpeg') center/cover no-repeat;

            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
            color: #ffffff;
        }

        .gallery-hero-content {
            max-width: 800px;
        }

        .gallery-hero-content .section-label {
            color: #D4AF37;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
        }

        .gallery-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            margin: 15px 0;
        }

        .gallery-hero p {
            font-size: 16px;
            line-height: 1.8;
        }

        /* ==============================
           GALLERY INTRODUCTION
        ============================== */

        .gallery-intro {
            text-align: center;
            padding: 70px 20px 35px;
        }

        .gallery-intro .section-label {
            color: #D4AF37;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .gallery-intro h2 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            margin: 12px 0;
        }

        .gallery-intro p {
            max-width: 750px;
            margin: auto;
            color: #666666;
            line-height: 1.8;
            font-size: 15px;
        }

        /* ==============================
           GALLERY FILTER BUTTONS
        ============================== */

        .gallery-filters {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 25px 20px 35px;
        }

        .gallery-filter {
            border: 1px solid #4B006E;
            background: #ffffff;
            color: #4B006E;
            padding: 10px 22px;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: .3s ease;
        }

        .gallery-filter:hover,
        .gallery-filter.active {
            background: #4B006E;
            color: #ffffff;
        }

        /* ==============================
           GALLERY GRID
        ============================== */

        .gallery-container {
            max-width: 1250px;
            margin: auto;
            padding: 0 25px 80px;

            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .gallery-item {
            position: relative;
            height: 290px;
            overflow: hidden;
            border-radius: 12px;
            background: #f2edf4;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.10);
            transition: .3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.16);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;

            padding: 35px 18px 18px;

            color: #ffffff;
            background: linear-gradient(
                transparent,
                rgba(0, 0, 0, 0.85)
            );
        }

        .gallery-caption h3 {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            margin-bottom: 5px;
        }

        .gallery-caption p {
            font-size: 12px;
            margin: 0;
        }

        .gallery-item[hidden] {
            display: none;
        }

        /* ==============================
           GALLERY CTA
        ============================== */

        .gallery-cta {
            background: #f8f4fa;
            padding: 70px 20px;
            text-align: center;
        }

        .gallery-cta h2 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .gallery-cta p {
            color: #666666;
            line-height: 1.8;
        }

        .gallery-cta .btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
        }

        /* ==============================
           RESPONSIVE DESIGN
        ============================== */

        @media (max-width: 900px) {

            .gallery-container {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .gallery-hero {
                min-height: 280px;
            }

            .gallery-hero h1 {
                font-size: 34px;
            }

            .gallery-intro h2 {
                font-size: 27px;
            }

            .gallery-container {
                grid-template-columns: 1fr;
                padding-left: 18px;
                padding-right: 18px;
            }

            .gallery-item {
                height: 260px;
            }

            .gallery-cta h2 {
                font-size: 26px;
            }

        }

    </style>

</head>

<body>

    <!-- ==================================
         TOP CONTACT BAR
    =================================== -->

    <div class="top-bar">

        <div class="top-contact">

            <span>
                <i class="fas fa-location-dot"></i>
                Monrovia, Liberia
            </span>

            <span>
                <i class="fas fa-phone"></i>
                +231 777 123 456
            </span>

            <span>
                <i class="fas fa-envelope"></i>
                info@violetlue.com
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

    <!-- ==================================
         MAIN HEADER
    =================================== -->

    <header class="main-header">

        <div class="header-container">

            <!-- LOGO -->

            <a href="../index.php" class="logo">

                <img src="../assets/images/logo.jpeg"
                    alt="Violet-Lue Suites & Apartments Logo">

                <div class="logo-text">

                    <h1>VIOLET-LUE</h1>

                    <h2>SUITES & APARTMENTS</h2>

                    <p>Your Comfort, Our Priority.</p>

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
                        <a href="../index.php">Home</a>
                    </li>

                    <li>
                        <a href="about.php">About Us</a>
                    </li>

                    <li>
                        <a href="rooms.php">Rooms & Apartments</a>
                    </li>

                    <li>
                        <a href="services.php">Our Services</a>
                    </li>

                    <li>
                        <a href="gallery.php" class="active">Gallery</a>
                    </li>

                    <li>
                        <a href="reservations.php">Online Reservations</a>
                    </li>

                    <li>
                        <a href="contact.php">Contact Us</a>
                    </li>

                </ul>

            </nav>

        </div>

    </header>

    <!-- ==================================
         GALLERY HERO
    =================================== -->

    <section class="gallery-hero">

        <div class="gallery-hero-content">

            <span class="section-label">DISCOVER VIOLET-LUE</span>

            <h1>Our Photo Gallery</h1>

            <p>
                Take a look inside VIOLET-LUE Suites & Apartments.
                Discover comfort, elegance and a welcoming
                atmosphere designed around you.
            </p>

        </div>

    </section>

    <!-- ==================================
         GALLERY INTRODUCTION
    =================================== -->

    <section class="gallery-intro">

        <span class="section-label">OUR PROPERTY</span>

        <h2>Stay Relaxed. Feel at Home.</h2>

        <p>
            Explore our accommodation and facilities.
            From comfortable guest rooms to furnished apartments,
            our goal is to make every stay enjoyable and memorable.
        </p>

    </section>

    <!-- ==================================
         GALLERY FILTERS
    =================================== -->

    <div class="gallery-filters">

        <button class="gallery-filter active"
            type="button"
            data-filter="all">

            All Photos

        </button>

        <button class="gallery-filter"
            type="button"
            data-filter="rooms">

            Guest Rooms

        </button>

        <button class="gallery-filter"
            type="button"
            data-filter="apartments">

            Apartments

        </button>

        <button class="gallery-filter"
            type="button"
            data-filter="facilities">

            Facilities

        </button>

        <button class="gallery-filter"
            type="button"
            data-filter="exterior">

            Exterior

        </button>

    </div>

    <!-- ==================================
         PHOTO GALLERY
    =================================== -->

    <section class="gallery-container">

        <!-- PROPERTY EXTERIOR -->

        <div class="gallery-item" data-category="exterior">

            <img src="../assets/images/hotel-exterior.jpeg"
                alt="VIOLET-LUE property exterior"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Our Property</h3>

                <p>A welcoming place to stay</p>

            </div>

        </div>

        <!-- LUXURY BEDROOM -->

        <div class="gallery-item" data-category="rooms">

            <img src="../assets/images/luxury-bedroom.jpeg"
                alt="Luxury guest bedroom"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Luxury Bedrooms</h3>

                <p>Comfort and relaxation</p>

            </div>

        </div>

        <!-- GUEST ROOM -->

        <div class="gallery-item" data-category="rooms">

            <img src="../assets/images/luxury-bedroom.jpeg"
                alt="Comfortable guest room"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Comfortable Rooms</h3>

                <p>A relaxing environment</p>

            </div>

        </div>

        <!-- APARTMENTS -->

        <div class="gallery-item" data-category="apartments">

            <img src="../assets/images/luxury-bedroom.jpeg"
                alt="Furnished apartment accommodation"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Furnished Apartments</h3>

                <p>Space and comfort for your stay</p>

            </div>

        </div>

        <!-- RELAXATION -->

        <div class="gallery-item" data-category="facilities">

            <img src="../assets/images/luxury-bedroom.jpeg"
                alt="Guest relaxation area"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Relaxation</h3>

                <p>Enjoy your time with us</p>

            </div>

        </div>

        <!-- BUILDING ENVIRONMENT -->

        <div class="gallery-item" data-category="exterior">

            <img src="../assets/images/hotel-exterior.jpeg"
                alt="VIOLET-LUE surroundings"
                loading="lazy">

            <div class="gallery-caption">

                <h3>Our Environment</h3>

                <p>A welcoming atmosphere</p>

            </div>

        </div>

    </section>

    <!-- ==================================
         CALL TO ACTION
    =================================== -->

    <section class="gallery-cta">

        <span class="section-label">YOUR NEXT STAY STARTS HERE</span>

        <h2>Ready to Experience VIOLET-LUE?</h2>

        <p>
            Make your reservation today and enjoy comfort
            with a touch of elegance.
        </p>

        <a href="reservations.php" class="btn btn-purple">

            Book Your Stay

            <i class="fas fa-chevron-right"></i>

        </a>

    </section>

    <!-- ==================================
         FOOTER
    =================================== -->

    <footer class="main-footer">

        <div class="footer-container">

            <div class="footer-brand">

                <h2>VIOLET-LUE</h2>

                <p>SUITES & APARTMENTS</p>

            </div>

            <div class="footer-slogan">

                <h2>Stay Relaxed. Feel at Home.</h2>

            </div>

            <div class="footer-copyright">

                <p>
                    &copy; <span id="current-year">2026</span>
                    Violet-Lue Suites & Apartments.
                    All Rights Reserved.
                </p>

            </div>

        </div>

    </footer>

    <!-- WEBSITE JAVASCRIPT -->
    <script src="../assets/js/script.js" defer></script>

    <!-- GALLERY FILTER JAVASCRIPT -->

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const filterButtons =
                document.querySelectorAll('.gallery-filter');

            const galleryItems =
                document.querySelectorAll('.gallery-item');

            filterButtons.forEach(function (button) {

                button.addEventListener('click', function () {

                    const selectedFilter =
                        this.getAttribute('data-filter');

                    // Update active button

                    filterButtons.forEach(function (btn) {

                        btn.classList.remove('active');

                    });

                    this.classList.add('active');

                    // Filter gallery images

                    galleryItems.forEach(function (item) {

                        const category =
                            item.getAttribute('data-category');

                        if (
                            selectedFilter === 'all' ||
                            category === selectedFilter
                        ) {

                            item.hidden = false;

                        } else {

                            item.hidden = true;

                        }

                    });

                });

            });

        });

    </script>

</body>

</html>