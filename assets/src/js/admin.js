document.addEventListener("DOMContentLoaded", () => {
    init_siderealModeValidator();
    init_adminShortcodeCopyOnClick();
});

function init_siderealModeValidator() {
    const zodiacTypeSelect = document.getElementById("astrologer_wp__zodiac_type");
    const siderealModeSelect = document.getElementById("astrologer_wp__sidereal_mode");
    const siderealModeDisabledMessage = document.getElementById("siderealModeDisabledMessage");
    const siderealModeSelectWrapper = document.getElementById("siderealModeSelectWrapper");

    function updateSiderealModeSelect() {
        if (zodiacTypeSelect.value === "Tropical") {
            siderealModeSelect.value = "none";
            siderealModeSelect.disabled = true;
            siderealModeSelect.style.display = "none";
            siderealModeSelectWrapper.style.display = "none";
        } else {
            siderealModeSelect.disabled = false;
            siderealModeSelectWrapper.style.display = "block";
            siderealModeSelect.style.display = "block";
            siderealModeDisabledMessage.style.display = "none";
        }
    }

    if (zodiacTypeSelect && siderealModeSelect) {
        zodiacTypeSelect.addEventListener("change", () => {
            updateSiderealModeSelect();
        });
    }

    updateSiderealModeSelect();
}

function init_adminShortcodeCopyOnClick() {
    const shortcodes = document.querySelectorAll(".astrologer-wp-admin-shortcode");

    shortcodes.forEach((shortcode) => {
        shortcode.style.cursor = "pointer";
        shortcode.addEventListener("click", () => {
            navigator.clipboard.writeText(shortcode.innerText).then(() => {
                shortcode.classList.add("copied");
                setTimeout(() => {
                    shortcode.classList.remove("copied");
                }, 1000);
            });
        });
    });
}
