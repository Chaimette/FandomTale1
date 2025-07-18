document.addEventListener('DOMContentLoaded', function() {
    let cookieModal = document.getElementById('cookieConsentModal');
    let acceptBtn = document.getElementById('acceptCookiesBtn');
    let declineBtn = document.getElementById('declineCookiesBtn');

    // Vérifie si le cookie existe déjà
    function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }

    if (!getCookie('cookies_accepted') && !getCookie('cookies_declined')) {
        cookieModal.style.display = 'block';
        setTimeout(function() {
            cookieModal.classList.add('show');
        }, 100);
    }

    // Accepte les cookies
    acceptBtn.onclick = function() {
        document.cookie = "cookies_accepted=true; max-age=" + (60 * 60 * 24 * 365) + "; path=/";
        if (document.cookie) {
            cookieModal.classList.remove('show');
            setTimeout(function() {
                cookieModal.style.display = 'none';
            }, 500);
        } else {
            alert("Les cookies ne peuvent pas être définis ! Veuillez débloquer ce site dans les paramètres des cookies de votre navigateur.");
        }
    }

    // Refuse les cookies
    declineBtn.onclick = function() {
        document.cookie = "cookies_declined=true; max-age=" + (60 * 60 * 24 * 365) + "; path=/";
        if (document.cookie) {
            cookieModal.classList.remove('show');
            setTimeout(function() {
                cookieModal.style.display = 'none';
            }, 500);
        } else {
            alert("Les cookies ne peuvent pas être définis ! Veuillez débloquer ce site dans les paramètres des cookies de votre navigateur.");
        }
    }
});
