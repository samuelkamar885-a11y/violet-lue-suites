<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Us | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Contact Violet-Lue Suites & Apartments in Monrovia, Liberia, for accommodation inquiries and reservation assistance.">

    <meta name="author" content="Violet-Lue Suites & Apartments">

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        .contact-hero {
            min-height: 300px;
            background:
                linear-gradient(rgba(35, 17, 55, 0.75), rgba(35, 17, 55, 0.75)),
                url("../assets/images/hotel-exterior.jpeg") center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 70px 20px;
        }

        .contact-hero h1 {
            color: #fff;
            font-family: "Playfair Display", serif;
            font-size: 42px;
            margin-bottom: 12px;
        }

        .contact-hero p {
            color: #f5e8c8;
            font-size: 16px;
        }

        .contact-section {
            padding: 75px 20px;
            background: #faf8fc;
        }

        .contact-intro {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 45px;
        }

        .contact-intro h2 {
            color: #351653;
            font-family: "Playfair Display", serif;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .contact-intro p {
            color: #666;
            line-height: 1.8;
        }

        .contact-container {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: 0.9fr 1.3fr;
            gap: 30px;
            align-items: start;
        }

        .contact-information {
            background: #351653;
            color: #fff;
            padding: 35px;
            border-radius: 12px;
        }

        .contact-information h2 {
            font-family: "Playfair Display", serif;
            font-size: 27px;
            margin-bottom: 15px;
        }

        .contact-information > p {
            color: #eee5f4;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .contact-detail {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }

        .contact-detail i {
            color: #d6b66f;
            font-size: 19px;
            width: 22px;
            margin-top: 4px;
        }

        .contact-detail h3 {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .contact-detail p,
        .contact-detail a {
            color: #eee5f4;
            font-size: 14px;
            line-height: 1.7;
            text-decoration: none;
            overflow-wrap: anywhere;
        }

        .contact-detail a:hover {
            color: #d6b66f;
        }

        .contact-social {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 22px;
            margin-top: 30px;
        }

        .contact-social h3 {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .contact-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-radius: 50%;
            margin-right: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .contact-social a:hover {
            background: #d6b66f;
            color: #351653;
        }

        .contact-form-card {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07);
        }

        .contact-form-card h2 {
            color: #351653;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .contact-form-card > p {
            color: #666;
            margin-bottom: 28px;
            line-height: 1.7;
        }

        .contact-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: #351653;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #351653;
            box-shadow: 0 0 0 3px rgba(53, 22, 83, 0.08);
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .contact-submit {
            grid-column: 1 / -1;
            width: 100%;
            padding: 15px;
            background: #351653;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: "Poppins", sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .contact-submit:hover {
            background: #c5a45e;
            color: #251333;
        }

        .contact-map-section {
            padding: 0 20px 75px;
            background: #faf8fc;
        }

        .contact-map-container {
            max-width: 1100px;
            margin: auto;
            text-align: center;
        }

        .contact-map-container h2 {
            color: #351653;
            font-family: "Playfair Display", serif;
            font-size: 30px;
            margin-bottom: 12px;
        }

        .contact-map-container p {
            color: #666;
            margin-bottom: 25px;
        }

        .map-placeholder {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            background: #eee8f3;
            border-radius: 12px;
            color: #351653;
            padding: 30px;
        }

        .map-placeholder i {
            font-size: 35px;
            color: #c5a45e;
        }

        .map-placeholder p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .contact-hero h1 {
                font-size: 32px;
            }

            .contact-container {
                grid-template-columns: 1fr;
            }

            .contact-information,
            .contact-form-card {
                padding: 25px;
            }
        }

        @media (max-width: 500px) {
            .contact-form {
                grid-template-columns: 1fr;
            }

            .form-group.full-width,
            .contact-submit {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>

    <!-- TOP CONTACT BAR -->
    <div class="top-bar">
        <div class="top-contact">
            <span><i class="fas fa-location-dot"></i> Monrovia, Liberia</span>
            <span><i class="fas fa-phone"></i> +231 777 123 456</span>
            <span><i class="fas fa-envelope"></i> info@violetlue.com</span>
        </div>

        <div class="top-social">
            <span>Follow Us:</span>
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <!-- HEADER -->
    <header class="main-header">
        <div class="header-container">

            <a href="../index.php" class="logo">
                <img src="../assets/images/logo.jpeg"
                    alt="Violet-Lue Suites & Apartments Logo">

                <div class="logo-text">
                    <h1>VIOLET-LUE</h1>
                    <h2>SUITES & APARTMENTS</h2>
                    <p>Your Comfort, Our Priority.</p>
                </div>
            </a>

            <button class="menu-toggle" type="button"
                aria-label="Open navigation menu"
                aria-controls="primary-navigation"
                aria-expanded="false">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <nav class="navbar" aria-label="Main navigation">
                <ul id="primary-navigation">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="rooms.php">Rooms & Apartments</a></li>
                    <li><a href="services.php">Our Services</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="reservations.php">Online Reservations</a></li>
                    <li><a href="contact.php" class="active"
                            aria-current="page">Contact Us</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- CONTACT HERO -->
    <section class="contact-hero">
        <div>
            <h1>Contact Us</h1>
            <p>We would love to hear from you. Get in touch with our team.</p>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-section">

        <div class="contact-intro">
            <h2>Get In Touch With Us</h2>
            <p>
                Have a question about our rooms, apartments or services?
                Contact Violet-Lue Suites & Apartments. Our team will be
                happy to assist you.
            </p>
        </div>

        <div class="contact-container">

            <!-- CONTACT INFORMATION -->
            <div class="contact-information">

                <h2>Contact Information</h2>

                <p>
                    Reach out to us using the contact details below.
                    We welcome your questions and accommodation inquiries.
                </p>

                <div class="contact-detail">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <h3>Our Location</h3>
                        <p>Monrovia, Liberia</p>
                    </div>
                </div>

                <div class="contact-detail">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Phone Number</h3>
                        <p>
                            <a href="tel:+231777123456">
                                +231 777 123 456
                            </a>
                        </p>
                    </div>
                </div>

                <div class="contact-detail">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email Address</h3>
                        <p>
                            <a href="mailto:info@violetlue.com">
                                info@violetlue.com
                            </a>
                        </p>
                    </div>
                </div>

                <div class="contact-detail">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Availability</h3>
                        <p>
                            Contact us for reservation inquiries and
                            service information.
                        </p>
                    </div>
                </div>

                <div class="contact-social">
                    <h3>Connect With Us</h3>

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

            <!-- CONTACT FORM -->
            <div class="contact-form-card">

                <h2>Send Us a Message</h2>

                <p>
                    Complete the form below and let us know how we can help.
                </p>

                <form class="contact-form" id="contactForm">

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            autocomplete="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email"
                            name="email"
                            placeholder="Enter your email address"
                            autocomplete="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            autocomplete="tel">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="reservation">Room Reservation</option>
                            <option value="apartments">Apartment Inquiry</option>
                            <option value="services">Services Inquiry</option>
                            <option value="complaint">Complaint or Feedback</option>
                            <option value="other">Other Inquiry</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message"
                            placeholder="Write your message here..."
                            required></textarea>
                    </div>

                    <button type="submit" class="contact-submit">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>

                </form>

            </div>

        </div>

    </section>

    <!-- LOCATION SECTION -->
    <section class="contact-map-section">

        <div class="contact-map-container">

            <h2>Find Us</h2>

            <p>
                We look forward to welcoming you to Violet-Lue Suites & Apartments.
            </p>

            <div class="map-placeholder">
                <i class="fas fa-map-location-dot"></i>
                <h3>Monrovia, Liberia</h3>
                <p>
                    Our exact business address and interactive map will be
                    added once the location is confirmed.
                </p>
            </div>

        </div>

    </section>

    <!-- FOOTER -->
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
                    Violet-Lue Suites & Apartments. All Rights Reserved.
                </p>
            </div>

        </div>
    </footer>

    <script src="../assets/js/script.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const contactForm = document.getElementById("contactForm");

            contactForm.addEventListener("submit", function (event) {
                event.preventDefault();

                alert(
                    "Thank you for contacting Violet-Lue Suites & Apartments. " +
                    "The contact form is not connected to the server yet. " +
                    "Please contact us directly using the phone number or email displayed on this page."
                );
            });

        });
    </script>

</body>
</html>
