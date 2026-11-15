document.addEventListener("DOMContentLoaded", function () {

    // 1. Booking date validation
    const checkIn = document.querySelector('[name="checkin"]');
    const checkOut = document.querySelector('[name="checkout"]');

    function getLocalDate() {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    if (checkIn && checkOut) {
        checkIn.min = getLocalDate();
        checkOut.min = getLocalDate();

        checkIn.addEventListener("change", function () {
            if (!checkIn.value) return;

            checkOut.min = checkIn.value;

            if (checkOut.value && checkOut.value <= checkIn.value) {
                checkOut.value = "";
            }
        });
    }

    // 2. Mobile navigation
    const menuButton = document.querySelector(".menu-toggle");
    const navigation = document.querySelector(".nav-links");

    if (menuButton && navigation) {
        menuButton.addEventListener("click", function () {
            const isOpen = navigation.classList.toggle("active");
            menuButton.setAttribute("aria-expanded", isOpen);
        });

        navigation.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                navigation.classList.remove("active");
                menuButton.setAttribute("aria-expanded", "false");
            });
        });
    }

    // 3. Automatically update footer year
    const year = document.getElementById("current-year");

    if (year) {
        year.textContent = new Date().getFullYear();
    }

});
