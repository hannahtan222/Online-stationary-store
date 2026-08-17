document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector(".contact-form form");

    if (form) {

        form.addEventListener("submit", function (event) {

            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const message = document.getElementById("message").value.trim();

            if (name === "" || email === "" || message === "") {

                alert("Please complete all fields.");

                event.preventDefault();
            }

        });

    }

});

function toggleMenu() {
    document.querySelector('.nav-menu').classList.toggle('active');
}
