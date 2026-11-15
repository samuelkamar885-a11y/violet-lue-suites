<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rooms & Apartments | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Explore comfortable luxury rooms and self-contained apartments at VIOLET-LUE Suites & Apartments in Liberia.">

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


    <!-- ROOMS PAGE STYLES -->

    <style>

        /* ==============================
           ROOMS HERO
        ============================== */

        .rooms-hero {
            min-height: 350px;

            background:
                linear-gradient(
                    rgba(45, 0, 65, 0.82),
                    rgba(45, 0, 65, 0.78)
                ),
                url('../assets/images/hotel-exterior.jpeg')
                center/cover no-repeat;

            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;

            padding: 60px 20px;
            color: #ffffff;
        }

        .rooms-hero-content {
            max-width: 800px;
        }

        .rooms-hero .section-label {
            color: #D4AF37;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
        }

        .rooms-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 46px;
            font-weight: 700;
            margin: 15px 0;
        }

        .rooms-hero p {
            font-size: 16px;
            line-height: 1.8;
        }


        /* ==============================
           ROOMS INTRODUCTION
        ============================== */

        .rooms-intro {
            max-width: 850px;
            margin: auto;
            padding: 75px 20px 40px;
            text-align: center;
        }

        .rooms-intro .section-label {
            color: #D4AF37;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 2px;
        }

        .rooms-intro h2 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            margin: 12px 0 18px;
        }

        .rooms-intro p {
            color: #666666;
            font-size: 15px;
            line-height: 1.9;
        }


        /* ==============================
           ROOMS GRID
        ============================== */

        .rooms-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 25px 70px;

            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 30px;
        }


        /* ==============================
           ROOM CARD
        ============================== */

        .room-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 12px;
            overflow: hidden;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }

        .room-card:hover {
            transform: translateY(-6px);

            box-shadow:
                0 12px 28px rgba(75, 0, 110, 0.13);
        }


        /* ROOM IMAGE */

        .room-image {
            height: 300px;
            overflow: hidden;
            background: #f2edf4;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .room-card:hover .room-image img {
            transform: scale(1.06);
        }


        /* ROOM CONTENT */

        .room-card-content {
            padding: 30px;
        }

        .room-card-content .room-label {
            color: #B28A18;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .room-card-content h3 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 27px;
            margin: 10px 0 14px;
        }

        .room-card-content p {
            color: #666666;
            font-size: 14px;
            line-height: 1.9;
            margin-bottom: 22px;
        }


        /* ROOM FEATURES */

        .room-features-title {
            color: #4B006E;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .room-features {
            list-style: none;
            padding: 0;
            margin: 0 0 25px;

            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .room-features li {
            color: #444444;
            font-size: 13px;
            display: flex;
            align-items: center;
        }

        .room-features i {
            color: #D4AF37;
            margin-right: 9px;
            font-size: 15px;
        }


        /* RESERVATION BUTTON */

        .room-card .btn {
            display: inline-block;
            text-decoration: none;
        }


        /* ==============================
           ROOM INFORMATION
        ============================== */

        .room-note {
            max-width: 1100px;
            margin: 0 auto 75px;
            padding: 30px;

            background: #FFFDF5;
            border-left: 4px solid #D4AF37;
            border-radius: 6px;
        }

        .room-note h3 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .room-note p {
            color: #666666;
            font-size: 14px;
            line-height: 1.9;
            margin-bottom: 20px;
        }

        .room-note .btn {
            display: inline-block;
            text-decoration: none;
        }


        /* ==============================
           RESPONSIVE DESIGN
        ============================== */

        @media (max-width: 900px) {

            .rooms-container {
                gap: 22px;
            }

            .room-card-content {
                padding: 24px;
            }

            .room-image {
                height: 260px;
            }

        }


        @media (max-width: 768px) {

            .rooms-hero {
                min-height: 300px;
            }

            .rooms-hero h1 {
                font-size: 36px;
            }

            .rooms-container {
                grid-template-columns: 1fr;
                max-width: 650px;
            }

            .rooms-intro h2 {
                font-size: 29px;
            }

            .room-image {
                height: 300px;
            }

            .room-note {
                margin-left: 20px;
                margin-right: 20px;
            }

        }


        @media (max-width: 520px) {

            .rooms-hero {
                min-height: 270px;
            }

            .rooms-hero h1 {
                font-size: 31px;
            }

            .rooms-intro {
                padding-top: 55px;
            }

            .rooms-intro h2 {
                font-size: 26px;
            }

            .rooms-container {
                padding-left: 18px;
                padding-right: 18px;
            }

            .room-image {
                height: 230px;
            }

            .room-card-content {
                padding: 22px;
            }

            .room-card-content h3 {
                font-size: 24px;
            }

            .room-features {
                grid-template-columns: 1fr;
            }

            .room-note {
                margin-left: 18px;
                margin-right: 18px;
                padding: 22px;
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
                        <a href="rooms.php" class="active">
                            Rooms & Apartments
                        </a>
                    </li>

                    <li>
                        <a href="services.php">Our Services</a>
                    </li>

                    <li>
                        <a href="gallery.php">Gallery</a>
                    </li>

                    <li>
                        <a href="reservations.php">
                            Online Reservations
                        </a>
                    </li>

                    <li>
                        <a href="contact.php">Contact Us</a>
                    </li>

                </ul>

            </nav>

        </div>

    </header>


    <!-- ==================================
         ROOMS HERO
    =================================== -->

    <section class="rooms-hero">

        <div class="rooms-hero-content">

            <span class="section-label">
                COMFORT AND HOSPITALITY
            </span>

            <h1>Our Rooms & Apartments</h1>

            <p>
                Discover comfort, privacy and hospitality
                at VIOLET-LUE Suites & Apartments.
            </p>

        </div>

    </section>


    <!-- ==================================
         INTRODUCTION
    =================================== -->

    <section class="rooms-intro">

        <span class="section-label">ACCOMMODATION</span>

        <h2>Find Your Perfect Place to Stay</h2>

        <p>
            Whether you are visiting for business, vacation
            or an extended stay, we aim to provide a relaxing
            and welcoming accommodation experience.
            Explore our available accommodation options below.
        </p>

    </section>


    <!-- ==================================
         ROOM OPTIONS
    =================================== -->

    <section class="rooms-container">


        <!-- LUXURY ROOM -->

        <article class="room-card">

            <div class="room-image">

                <img src="../assets/images/luxury-bedroom.jpeg"
                    alt="Luxury guest room at VIOLET-LUE"
                    loading="lazy">

            </div>


            <div class="room-card-content">

                <span class="room-label">
                    PRIVATE ACCOMMODATION
                </span>

                <h3>Luxury Room</h3>

                <p>
                    Enjoy a comfortable and relaxing private
                    room designed to make your stay pleasant.
                    Ideal for guests looking for comfort
                    and convenience.
                </p>


                <h4 class="room-features-title">
                    Room Features
                </h4>


                <ul class="room-features">

                    <li>
                        <i class="fas fa-bed"></i>
                        Comfortable Bed
                    </li>

                    <li>
                        <i class="fas fa-wifi"></i>
                        Free Wi-Fi
                    </li>

                    <li>
                        <i class="fas fa-shield-halved"></i>
                        Secure Environment
                    </li>

                    <li>
                        <i class="fas fa-broom"></i>
                        Housekeeping
                    </li>

                </ul>


                <a href="reservations.php?room_type=luxury-room"
                    class="btn btn-purple">

                    Reserve This Room

                    <i class="fas fa-arrow-right"></i>

                </a>

            </div>

        </article>


        <!-- SELF-CONTAINED APARTMENT -->

        <article class="room-card">

            <div class="room-image">

                <img src="../assets/images/Self-Contained Apartment.jpeg"
                    alt="Self-contained apartment accommodation"
                    loading="lazy">

            </div>


            <div class="room-card-content">

                <span class="room-label">
                    INDEPENDENT LIVING
                </span>

                <h3>Self-Contained Apartment</h3>

                <p>
                    Experience greater privacy and independence
                    in a furnished apartment suitable for
                    short-term and long-term accommodation.
                </p>


                <h4 class="room-features-title">
                    Apartment Features
                </h4>


                <ul class="room-features">

                    <li>
                        <i class="fas fa-couch"></i>
                        Furnished Living Space
                    </li>

                    <li>
                        <i class="fas fa-kitchen-set"></i>
                        Private Kitchen
                    </li>

                    <li>
                        <i class="fas fa-wifi"></i>
                        Free Wi-Fi
                    </li>

                    <li>
                        <i class="fas fa-shield-halved"></i>
                        Secure Environment
                    </li>

                </ul>


                <a href="reservations.php?room_type=self-contained-apartment"
                    class="btn btn-purple">

                    Reserve This Apartment

                    <i class="fas fa-arrow-right"></i>

                </a>

            </div>

        </article>


    </section>


    <!-- ==================================
         ADDITIONAL INFORMATION
    =================================== -->

    <section class="room-note">

        <h3>
            <i class="fas fa-circle-info"></i>
            Planning a Stay With Us?
        </h3>

        <p>
            Contact our team to ask about room availability,
            accommodation rates, special requests and
            long-term stays. Our team will be happy to assist you.
        </p>

        <a href="contact.php" class="btn btn-gold">

            Contact Us

            <i class="fas fa-arrow-right"></i>

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


</body>

</html>
