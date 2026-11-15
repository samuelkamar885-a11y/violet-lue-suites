<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Our Services | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Discover the accommodation, hospitality and guest services offered by VIOLET-LUE Suites & Apartments in Liberia.">

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


    <!-- SERVICES PAGE STYLES -->

    <style>

        /* ==============================
           SERVICES HERO
        ============================== */

        .services-hero {
            min-height: 350px;

            background:
                linear-gradient(
                    rgba(45, 0, 65, 0.80),
                    rgba(45, 0, 65, 0.80)
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

        .services-hero-content {
            max-width: 800px;
        }

        .services-hero .section-label {
            color: #D4AF37;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 3px;
        }

        .services-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 700;
            margin: 15px 0;
        }

        .services-hero p {
            font-size: 16px;
            line-height: 1.8;
            max-width: 650px;
            margin: auto;
        }


        /* ==============================
           SERVICES INTRODUCTION
        ============================== */

        .services-intro {
            max-width: 850px;
            margin: auto;
            padding: 75px 20px 40px;
            text-align: center;
        }

        .services-intro .section-label {
            color: #D4AF37;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .services-intro h2 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            margin: 12px 0 18px;
        }

        .services-intro p {
            color: #666666;
            font-size: 15px;
            line-height: 1.9;
        }


        /* ==============================
           SERVICES GRID
        ============================== */

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 25px 80px;

            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 25px;
        }


        /* ==============================
           SERVICE CARD
        ============================== */

        .service-card {
            background: #ffffff;
            padding: 38px 28px;
            text-align: center;

            border-radius: 12px;
            border: 1px solid #eee;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-7px);

            box-shadow:
                0 12px 28px rgba(75, 0, 110, 0.13);
        }


        /* SERVICE ICON */

        .service-icon {
            width: 78px;
            height: 78px;

            margin: 0 auto 22px;

            border-radius: 50%;

            background: #FFF8E5;
            color: #4B006E;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 30px;

            transition: .3s ease;
        }

        .service-card:hover .service-icon {
            background: #4B006E;
            color: #D4AF37;
        }


        /* SERVICE TITLE */

        .service-card h3 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            margin-bottom: 14px;
        }


        /* SERVICE DESCRIPTION */

        .service-card p {
            color: #666666;
            font-size: 14px;
            line-height: 1.9;
            margin: 0;
        }


        /* ==============================
           SERVICE INFORMATION
        ============================== */

        .service-note {
            max-width: 1100px;
            margin: 0 auto 70px;
            padding: 25px 30px;

            background: #FFFDF5;
            border-left: 4px solid #D4AF37;

            border-radius: 6px;
        }

        .service-note h3 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
        }

        .service-note p {
            color: #666666;
            font-size: 14px;
            line-height: 1.8;
            margin: 0;
        }


        /* ==============================
           CALL TO ACTION
        ============================== */

        .service-cta {
            background: #FFFDF5;
            border-top: 3px solid #D4AF37;

            padding: 70px 20px;
            text-align: center;
        }

        .service-cta .section-label {
            color: #D4AF37;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .service-cta h2 {
            color: #4B006E;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin: 15px 0;
        }

        .service-cta p {
            color: #666666;
            line-height: 1.8;
            max-width: 650px;
            margin: 0 auto 25px;
        }

        .service-cta .btn {
            display: inline-block;
            text-decoration: none;
        }


        /* ==============================
           RESPONSIVE DESIGN
        ============================== */

        @media (max-width: 1000px) {

            .services-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .service-note {
                margin-left: 25px;
                margin-right: 25px;
            }

        }


        @media (max-width: 768px) {

            .services-hero {
                min-height: 300px;
            }

            .services-hero h1 {
                font-size: 36px;
            }

            .services-intro h2 {
                font-size: 29px;
            }

        }


        @media (max-width: 550px) {

            .services-container {
                grid-template-columns: 1fr;
                padding-left: 18px;
                padding-right: 18px;
            }

            .services-hero {
                min-height: 270px;
            }

            .services-hero h1 {
                font-size: 31px;
            }

            .services-intro {
                padding-top: 55px;
            }

            .services-intro h2 {
                font-size: 26px;
            }

            .service-card {
                padding: 32px 22px;
            }

            .service-cta h2 {
                font-size: 27px;
            }

            .service-note {
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
                        <a href="rooms.php">Rooms & Apartments</a>
                    </li>

                    <li>
                        <a href="services.php" class="active">
                            Our Services
                        </a>
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
         SERVICES HERO
    =================================== -->

    <section class="services-hero">

        <div class="services-hero-content">

            <span class="section-label">
                WELCOME TO VIOLET-LUE
            </span>

            <h1>Our Services</h1>

            <p>
                Exceptional hospitality, comfort and convenience
                throughout your stay.
            </p>

        </div>

    </section>


    <!-- ==================================
         SERVICES INTRODUCTION
    =================================== -->

    <section class="services-intro">

        <span class="section-label">WHAT WE OFFER</span>

        <h2>Designed Around Your Comfort</h2>

        <p>
            At VIOLET-LUE Suites & Apartments, we aim to make
            every guest feel welcome and relaxed. Explore our
            hospitality services and discover how we work to
            make your stay comfortable and convenient.
        </p>

    </section>


    <!-- ==================================
         SERVICES LIST
    =================================== -->

    <section class="services-container">


        <!-- ACCOMMODATION -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-bed"></i>
            </div>

            <h3>Luxury Accommodation</h3>

            <p>
                Comfortable guest rooms and furnished
                apartments designed for relaxation,
                privacy and convenience.
            </p>

        </article>


        <!-- WIFI -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-wifi"></i>
            </div>

            <h3>Free Wi-Fi</h3>

            <p>
                Stay connected with internet access
                for communication, work and entertainment.
            </p>

        </article>


        <!-- PARKING -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-car"></i>
            </div>

            <h3>Secure Parking</h3>

            <p>
                Parking facilities intended to provide
                convenience and peace of mind for guests
                arriving with vehicles.
            </p>

        </article>


        <!-- RESTAURANT -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-utensils"></i>
            </div>

            <h3>Restaurant & Dining</h3>

            <p>
                Dining services for guests who want
                convenient meal options during their stay.
            </p>

        </article>


        <!-- SECURITY -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-shield-halved"></i>
            </div>

            <h3>24-Hour Security</h3>

            <p>
                Security arrangements focused on helping
                guests enjoy a comfortable and peaceful stay.
            </p>

        </article>


        <!-- AIRPORT PICKUP -->

        <article class="service-card">

            <div class="service-icon">
                <i class="fas fa-plane"></i>
            </div>

            <h3>Airport Pickup</h3>

            <p>
                Airport transportation assistance can be
                arranged for guests who need help getting
                to the property.
            </p>

        </article>


    </section>


    <!-- ==================================
         SERVICE INFORMATION
    =================================== -->

    <section class="service-note">

        <h3>
            <i class="fas fa-info-circle"></i>
            Need More Information?
        </h3>

        <p>
            Please contact our team to confirm service availability,
            arrangements and any additional charges before booking.
            We will be happy to assist you with your accommodation needs.
        </p>

    </section>


    <!-- ==================================
         CALL TO ACTION
    =================================== -->

    <section class="service-cta">

        <span class="section-label">
            YOUR COMFORT, OUR PRIORITY
        </span>

        <h2>Experience Comfort With Us</h2>

        <p>
            Planning a visit? Make your reservation today
            and look forward to a welcoming stay at
            VIOLET-LUE Suites & Apartments.
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


</body>

</html>
