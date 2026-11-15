<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Learn about VIOLET-LUE Suites & Apartments in Liberia. Discover our commitment to comfort, hospitality and exceptional guest experiences.">

    <meta name="author" content="Violet-Lue Suites & Apartments">

    <!-- Website CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- ABOUT PAGE STYLES -->
    <style>

        /* ==================================
           ABOUT PAGE HERO
        =================================== */

        .about-hero {
            min-height: 430px;

            background:
                linear-gradient(
                    rgba(35, 0, 55, 0.76),
                    rgba(35, 0, 55, 0.76)
                ),
                url("../assets/images/hotel-exterior.jpeg")
                center/cover no-repeat;

            display: flex;
            align-items: center;
            justify-content: center;

            text-align: center;

            padding: 80px 20px;

            color: #ffffff;
        }

        .about-hero-content {
            max-width: 850px;
        }

        .about-hero .section-label {
            color: #D8B56D;

            font-size: 13px;
            font-weight: 600;

            letter-spacing: 3px;

            display: inline-block;

            margin-bottom: 18px;
        }

        .about-hero h1 {
            font-family: 'Playfair Display', serif;

            color: #D8B56D;

            font-size: clamp(36px, 5vw, 55px);

            margin-bottom: 20px;
        }

        .about-hero p {
            color: #ffffff;

            font-size: 16px;

            line-height: 1.9;

            max-width: 700px;

            margin: auto;
        }


        /* ==================================
           GENERAL ABOUT SECTION
        =================================== */

        .about-main-section {
            padding: 85px 7%;
        }

        .about-container {
            max-width: 1200px;
            margin: auto;
        }

        .about-section-heading {
            text-align: center;
            max-width: 750px;
            margin: 0 auto 50px;
        }

        .about-section-heading .section-label {
            color: #A47A32;

            font-size: 12px;
            font-weight: 700;

            letter-spacing: 2px;

            display: block;

            margin-bottom: 12px;
        }

        .about-section-heading h2 {
            font-family: 'Playfair Display', serif;

            color: #4B006E;

            font-size: 36px;

            margin-bottom: 15px;
        }

        .about-section-heading p {
            color: #666666;

            font-size: 14px;

            line-height: 1.9;
        }


        /* ==================================
           OUR STORY
        =================================== */

        .story-container {
            display: grid;

            grid-template-columns: 1fr 1fr;

            align-items: center;

            gap: 55px;
        }

        .story-image {
            position: relative;
        }

        .story-image img {
            width: 100%;
            height: 440px;

            object-fit: cover;

            display: block;

            border-radius: 10px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .story-content .gold-line {
            width: 65px;
            height: 4px;

            background: #D8B56D;

            border-radius: 5px;

            margin-bottom: 20px;
        }

        .story-content .section-label {
            color: #A47A32;

            font-size: 12px;
            font-weight: 700;

            letter-spacing: 2px;
        }

        .story-content h2 {
            font-family: 'Playfair Display', serif;

            color: #4B006E;

            font-size: 36px;

            margin: 12px 0 22px;
        }

        .story-content p {
            color: #555555;

            font-size: 14px;

            line-height: 2;

            margin-bottom: 16px;
        }

        .story-highlight {
            border-left: 4px solid #D8B56D;

            background: #FFFDF5;

            padding: 18px 22px;

            margin-top: 22px;

            color: #4B006E;

            font-family: 'Playfair Display', serif;

            font-size: 18px;

            font-weight: 600;
        }


        /* ==================================
           MISSION AND VISION
        =================================== */

        .mission-section {
            background: #FFFDF5;
        }

        .mission-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 30px;
        }

        .mission-card {
            background: #ffffff;

            padding: 40px 30px;

            text-align: center;

            border-radius: 10px;

            border-bottom: 4px solid #D8B56D;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);

            transition: 0.3s ease;
        }

        .mission-card:hover {
            transform: translateY(-6px);

            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10);
        }

        .mission-card .mission-icon {
            width: 75px;
            height: 75px;

            background: rgba(75, 0, 110, 0.08);

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            margin: 0 auto 22px;
        }

        .mission-card .mission-icon i {
            color: #4B006E;

            font-size: 30px;
        }

        .mission-card h3 {
            font-family: 'Playfair Display', serif;

            color: #4B006E;

            font-size: 25px;

            margin-bottom: 15px;
        }

        .mission-card p {
            color: #666666;

            font-size: 14px;

            line-height: 1.9;
        }


        /* ==================================
           WHY CHOOSE US
        =================================== */

        .why-section {
            background: #ffffff;
        }

        .why-grid {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 25px;
        }

        .why-card {
            background: #ffffff;

            padding: 32px 22px;

            text-align: center;

            border: 1px solid #eeeeee;

            border-radius: 10px;

            transition: 0.3s ease;
        }

        .why-card:hover {
            transform: translateY(-7px);

            border-color: #D8B56D;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .why-card .why-icon {
            width: 68px;
            height: 68px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #FFFDF5;

            border-radius: 50%;

            margin: 0 auto 18px;
        }

        .why-card .why-icon i {
            color: #A47A32;

            font-size: 27px;
        }

        .why-card h3 {
            color: #4B006E;

            font-size: 17px;

            margin-bottom: 12px;
        }

        .why-card p {
            color: #666666;

            font-size: 13px;

            line-height: 1.9;
        }


        /* ==================================
           BRAND VALUES
        =================================== */

        .about-values-section {
            background: #FFFDF5;

            padding: 65px 20px;

            text-align: center;
        }

        .about-values-section h2 {
            font-family: 'Playfair Display', serif;

            color: #4B006E;

            font-size: 34px;

            margin-bottom: 15px;
        }

        .flower-divider {
            color: #D8B56D;

            font-size: 27px;

            margin-bottom: 35px;
        }

        .about-values-grid {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 25px;

            max-width: 850px;

            margin: auto;
        }

        .about-value-item {
            padding: 20px;
        }

        .about-value-item i {
            color: #A47A32;

            font-size: 32px;

            margin-bottom: 15px;
        }

        .about-value-item h3 {
            color: #4B006E;

            font-size: 17px;
        }


        /* ==================================
           CALL TO ACTION
        =================================== */

        .about-cta {
            background:
                linear-gradient(
                    rgba(48, 16, 68, 0.94),
                    rgba(48, 16, 68, 0.94)
                ),
                url("../assets/images/hotel-exterior.jpeg")
                center/cover no-repeat;

            padding: 75px 20px;

            text-align: center;
        }

        .about-cta h2 {
            font-family: 'Playfair Display', serif;

            color: #D8B56D;

            font-size: 36px;

            margin-bottom: 18px;
        }

        .about-cta p {
            color: #ffffff;

            max-width: 700px;

            margin: 0 auto 28px;

            font-size: 14px;

            line-height: 1.9;
        }

        .about-cta .btn {
            display: inline-flex;

            align-items: center;

            gap: 10px;

            text-decoration: none;

            padding: 14px 30px;

            border-radius: 5px;

            background: #D8B56D;

            color: #301044;

            font-size: 14px;

            font-weight: 700;

            transition: 0.3s ease;
        }

        .about-cta .btn:hover {
            background: #ffffff;

            color: #4B006E;

            transform: translateY(-3px);
        }


        /* ==================================
           RESPONSIVE DESIGN
        =================================== */

        @media (max-width: 900px) {

            .story-container {
                gap: 35px;
            }

            .story-image img {
                height: 380px;
            }

            .why-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 768px) {

            .about-hero {
                min-height: 350px;
                padding: 65px 20px;
            }

            .about-main-section {
                padding: 60px 5%;
            }

            .story-container {
                grid-template-columns: 1fr;
            }

            .story-image img {
                height: 350px;
            }

            .mission-grid {
                grid-template-columns: 1fr;
            }

            .about-section-heading h2,
            .story-content h2 {
                font-size: 30px;
            }

            .about-cta h2 {
                font-size: 29px;
            }

        }


        @media (max-width: 520px) {

            .about-hero {
                min-height: 320px;
            }

            .about-hero p {
                font-size: 14px;
            }

            .story-image img {
                height: 270px;
            }

            .why-grid {
                grid-template-columns: 1fr;
            }

            .about-values-grid {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .about-values-section h2 {
                font-size: 28px;
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
                        <a href="about.php" class="active" aria-current="page">
                            About Us
                        </a>
                    </li>

                    <li>
                        <a href="rooms.php">
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


    <main>


        <!-- ==================================
             ABOUT HERO SECTION
        =================================== -->

        <section class="about-hero">

            <div class="about-hero-content">

                <span class="section-label">
                    WELCOME TO VIOLET-LUE
                </span>

                <h1>About VIOLET-LUE Suites & Apartments</h1>

                <p>
                    Experience a place where comfort, relaxation
                    and warm hospitality come together. We are
                    dedicated to making every stay memorable.
                </p>

            </div>

        </section>


        <!-- ==================================
             OUR STORY
        =================================== -->

        <section class="about-main-section">

            <div class="about-container story-container">

                <div class="story-image">

                    <img src="../assets/images/luxury-bedroom.jpeg"
                        alt="Luxury guest room at Violet-Lue Suites">

                </div>


                <div class="story-content">

                    <div class="gold-line"></div>

                    <span class="section-label">
                        WHO WE ARE
                    </span>

                    <h2>A Home Away From Home</h2>

                    <p>
                        VIOLET-LUE Suites & Apartments is a hospitality
                        brand in Liberia, offering luxurious rooms and
                        fully furnished apartments for families,
                        tourists and business travelers.
                    </p>

                    <p>
                        We understand that a great stay is about more
                        than just having a room. It is about feeling
                        welcomed, enjoying a peaceful environment and
                        receiving quality hospitality.
                    </p>

                    <p>
                        Whether you are visiting for business, leisure
                        or an extended stay, our goal is to make your
                        experience comfortable and memorable.
                    </p>

                    <div class="story-highlight">
                        Your Comfort, Our Priority.
                    </div>

                </div>

            </div>

        </section>


        <!-- ==================================
             MISSION AND VISION
        =================================== -->

        <section class="about-main-section mission-section">

            <div class="about-container">

                <div class="about-section-heading">

                    <span class="section-label">
                        OUR PURPOSE
                    </span>

                    <h2>Our Mission & Vision</h2>

                    <p>
                        Our commitment is to provide comfortable,
                        secure and quality accommodation with
                        excellent customer service.
                    </p>

                </div>


                <div class="mission-grid">

                    <!-- MISSION -->

                    <div class="mission-card">

                        <div class="mission-icon">

                            <i class="fas fa-bullseye"></i>

                        </div>

                        <h3>Our Mission</h3>

                        <p>
                            To provide comfortable accommodation,
                            dependable hospitality and quality guest
                            services while making every guest feel
                            welcome, relaxed and valued.
                        </p>

                    </div>


                    <!-- VISION -->

                    <div class="mission-card">

                        <div class="mission-icon">

                            <i class="fas fa-eye"></i>

                        </div>

                        <h3>Our Vision</h3>

                        <p>
                            To become a trusted accommodation destination
                            recognized for comfort, excellent hospitality,
                            cleanliness and outstanding guest experiences.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- ==================================
             WHY CHOOSE US
        =================================== -->

        <section class="about-main-section why-section">

            <div class="about-container">

                <div class="about-section-heading">

                    <span class="section-label">
                        THE VIOLET-LUE EXPERIENCE
                    </span>

                    <h2>Why Choose VIOLET-LUE?</h2>

                    <p>
                        We want every guest to enjoy a comfortable,
                        welcoming and stress-free accommodation experience.
                    </p>

                </div>


                <div class="why-grid">


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-bed"></i>
                        </div>

                        <h3>Comfortable Accommodation</h3>

                        <p>
                            Enjoy thoughtfully prepared rooms and
                            apartments designed for relaxation.
                        </p>

                    </div>


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-wifi"></i>
                        </div>

                        <h3>Free Wi-Fi</h3>

                        <p>
                            Stay connected while enjoying your
                            time with us.
                        </p>

                    </div>


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <h3>24-Hour Security</h3>

                        <p>
                            We value guest safety and a peaceful
                            environment.
                        </p>

                    </div>


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-car"></i>
                        </div>

                        <h3>Secure Parking</h3>

                        <p>
                            Enjoy convenient parking arrangements
                            during your stay.
                        </p>

                    </div>


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-utensils"></i>
                        </div>

                        <h3>Restaurant & Dining</h3>

                        <p>
                            Enjoy convenient dining options
                            during your visit.
                        </p>

                    </div>


                    <div class="why-card">

                        <div class="why-icon">
                            <i class="fas fa-concierge-bell"></i>
                        </div>

                        <h3>Warm Hospitality</h3>

                        <p>
                            We aim to make every guest feel welcome
                            and valued.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- ==================================
             BRAND VALUES
        =================================== -->

        <section class="about-values-section">

            <div class="about-container">

                <h2>Stay Relaxed. Feel at Home.</h2>

                <div class="flower-divider">

                    <i class="fas fa-spa"></i>

                </div>


                <div class="about-values-grid">


                    <div class="about-value-item">

                        <i class="fas fa-bed"></i>

                        <h3>Comfort</h3>

                    </div>


                    <div class="about-value-item">

                        <i class="fas fa-handshake"></i>

                        <h3>Hospitality</h3>

                    </div>


                    <div class="about-value-item">

                        <i class="fas fa-thumbs-up"></i>

                        <h3>Excellence</h3>

                    </div>

                </div>

            </div>

        </section>


        <!-- ==================================
             CALL TO ACTION
        =================================== -->

        <section class="about-cta">

            <div class="about-container">

                <h2>Experience Comfort at VIOLET-LUE Suites & Apartments</h2>

                <p>
                    Ready to enjoy a relaxing stay? Explore our
                    rooms and apartments or contact us to make
                    your reservation. We look forward to welcoming you.
                </p>

                <a href="reservations.php" class="btn">

                    Book Your Stay

                    <i class="fas fa-chevron-right"></i>

                </a>

            </div>

        </section>


    </main>


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


    <!-- Website JavaScript -->
    <script src="../assets/js/script.js" defer></script>

</body>

</html>
