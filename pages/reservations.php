<?php
require_once __DIR__ . '/../includes/website_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Reservations | VIOLET-LUE Suites & Apartments</title>

    <meta name="description"
        content="Request a reservation at Violet-Lue Suites & Apartments in Monrovia, Liberia. Choose your accommodation, dates and number of guests.">

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
        .reservation-hero {
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

        .reservation-hero h1 {
            color: #fff;
            font-family: "Playfair Display", serif;
            font-size: 42px;
            margin-bottom: 12px;
        }

        .reservation-hero p {
            color: #f5e8c8;
            font-size: 16px;
        }

        .reservation-section {
            padding: 75px 20px;
            background: #faf8fc;
        }

        .reservation-container {
            max-width: 1050px;
            margin: auto;
            display: grid;
            grid-template-columns: 0.8fr 1.5fr;
            gap: 35px;
            align-items: start;
        }

        .reservation-info {
            background: #351653;
            color: #fff;
            padding: 35px;
            border-radius: 12px;
        }

        .reservation-info h2 {
            font-family: "Playfair Display", serif;
            font-size: 27px;
            margin-bottom: 18px;
        }

        .reservation-info p {
            color: #eee5f4;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .reservation-info ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .reservation-info li {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .reservation-info li i {
            color: #d6b66f;
            margin-top: 5px;
        }

        .reservation-form-card {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07);
        }

        .reservation-form-card h2 {
            color: #351653;
            font-family: "Playfair Display", serif;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-description {
            color: #666;
            margin-bottom: 28px;
            line-height: 1.7;
        }

        .reservation-form {
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
            font-weight: 600;
            font-size: 14px;
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
            min-height: 120px;
            resize: vertical;
        }

        .reservation-submit {
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

        .reservation-submit:hover {
            background: #c5a45e;
            color: #251333;
        }

        .reservation-notice {
            grid-column: 1 / -1;
            background: #fff8e8;
            border-left: 4px solid #c5a45e;
            padding: 14px;
            color: #66532d;
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 768px) {
            .reservation-hero h1 {
                font-size: 32px;
            }

            .reservation-container {
                grid-template-columns: 1fr;
            }

            .reservation-form-card,
            .reservation-info {
                padding: 25px;
            }
        }

        @media (max-width: 500px) {
            .reservation-form {
                grid-template-columns: 1fr;
            }

            .form-group.full-width,
            .reservation-submit,
            .reservation-notice {
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
                    <li>
                        <a href="reservations.php" class="active"
                            aria-current="page">Online Reservations</a>
                    </li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- RESERVATION HERO -->
    <section class="reservation-hero">
        <div>
            <h1>Online Reservations</h1>
            <p>Reserve your comfortable stay with Violet-Lue Suites & Apartments.</p>
        </div>
    </section>

    <!-- RESERVATION SECTION -->
    <section class="reservation-section">

        <div class="reservation-container">

            <!-- RESERVATION INFORMATION -->
            <div class="reservation-info">

                <h2>Plan Your Stay</h2>

                <p>
                    We look forward to welcoming you. Send us your reservation
                    request and our team will contact you to discuss availability
                    and confirm the details.
                </p>

                <ul>
                    <li>
                        <i class="fas fa-bed"></i>
                        <span>Comfortable luxury rooms and furnished apartments.</span>
                    </li>

                    <li>
                        <i class="fas fa-calendar-check"></i>
                        <span>Accommodation options for short and long stays.</span>
                    </li>

                    <li>
                        <i class="fas fa-wifi"></i>
                        <span>Free Wi-Fi for a connected and convenient stay.</span>
                    </li>

                    <li>
                        <i class="fas fa-shield-halved"></i>
                        <span>A welcoming environment with security arrangements.</span>
                    </li>

                    <li>
                        <i class="fas fa-phone"></i>
                        <span>Contact us if you need help with your reservation.</span>
                    </li>
                </ul>

            </div>

            <!-- RESERVATION FORM -->
            <div class="reservation-form-card">

                <h2>Request a Reservation</h2>

                <p class="form-description">
                    Please complete the form below. All fields marked with *
                    are required.
                </p>

                <form action="../backend/process_reservation.php"
      method="POST"
      id="reservationForm"
      class="reservation-form">

                    <div class="form-group full-width">
                        <label for="room_type">Accommodation Type *</label>

                        <select id="room_type" name="room_type" required>
                            <option value="">Select accommodation</option>
                            <option value="luxury-room">Luxury Room</option>
                            <option value="self-contained-apartment">
                                Self-Contained Apartment
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="check_in">Check-in Date *</label>
                        <input type="date" id="check_in"
                            name="check_in" required>
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check-out Date *</label>
                        <input type="date" id="check_out"
                            name="check_out" required>
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests *</label>

                        <select id="guests" name="guests" required>
                            <option value="">Select guests</option>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5 Guests</option>
                            <option value="6">6 or More Guests</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name"
                            name="full_name"
                            maxlength="150"
                            placeholder="Enter your full name"
                            autocomplete="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email"
                            name="email"
                            maxlength="150"
                            placeholder="Enter your email"
                            autocomplete="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone"
                            name="phone"
                            maxlength="30"
                            placeholder="Enter your phone number"
                            autocomplete="tel" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests"
                            name="special_requests"
                            maxlength="5000"
                            placeholder="Tell us about any special requests or requirements..."></textarea>
                    </div>

                    <div class="reservation-notice">
                        <i class="fas fa-circle-info"></i>
                        Submitting this form is a reservation request only.
                        Your accommodation is not confirmed until our team
                        contacts you and confirms availability.
                    </div>

                    <button type="submit" class="reservation-submit">
                        <i class="fas fa-paper-plane"></i>
                        Submit Reservation Request
                    </button>

                </form>

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

    <!-- RESERVATION DATE VALIDATION -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const checkIn = document.getElementById("check_in");
            const checkOut = document.getElementById("check_out");

            function getLocalDateString() {
                const today = new Date();

                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, "0");
                const day = String(today.getDate()).padStart(2, "0");

                return `${year}-${month}-${day}`;
            }

            const today = getLocalDateString();

            checkIn.min = today;
            checkOut.min = today;

            checkIn.addEventListener("change", function () {

                checkOut.min = checkIn.value || today;

                if (checkOut.value && checkOut.value <= checkIn.value) {
                    checkOut.value = "";
                }

            });

            checkOut.addEventListener("change", function () {

                if (checkOut.value && checkOut.value <= checkIn.value) {
                    alert("Check-out date must be later than check-in date.");
                    checkOut.value = "";
                    checkOut.focus();
                }

            });

        });
    </script>

</body>
</html>
