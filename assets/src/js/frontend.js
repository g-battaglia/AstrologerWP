document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("astrologerWpBirthChartForm")) {
        init_searchCityOnInput(
            "astrologerWpBirthChartForm",
            "astrologerWpBirthChartCityInput",
            "astrologerWpBirthChartNationInput",
            "astrologerWpBirthChartLatitudeInput",
            "astrologerWpBirthChartLongitudeInput",
            "astrologerWpBirthChartTimezoneInput",
            "astrologerWpBirthChartCitySuggestions",
        );
    }

    if (document.getElementById("astrologerWpSynastryChartForm")) {
        init_searchCityOnInput(
            "astrologerWpSynastryChartForm",
            "astrologerWpSynastryChartFirstCityInput",
            "astrologerWpSynastryChartFirstNationInput",
            "astrologerWpSynastryChartFirstLatitudeInput",
            "astrologerWpSynastryChartFirstLongitudeInput",
            "astrologerWpSynastryChartFirstTimezoneInput",
            "astrologerWpSynastryChartFirstCitySuggestions",
        );

        init_searchCityOnInput(
            "astrologerWpSynastryChartForm",
            "astrologerWpSynastryChartSecondCityInput",
            "astrologerWpSynastryChartSecondNationInput",
            "astrologerWpSynastryChartSecondLatitudeInput",
            "astrologerWpSynastryChartSecondLongitudeInput",
            "astrologerWpSynastryChartSecondTimezoneInput",
            "astrologerWpSynastryChartSecondCitySuggestions",
        );
    }

    if (document.getElementById("astrologerWpTransitChartForm")) {
        init_searchCityOnInput(
            "astrologerWpTransitChartForm",
            "astrologerWpTransitChartSubjectCityInput",
            "astrologerWpTransitChartSubjectNationInput",
            "astrologerWpTransitChartSubjectLatitudeInput",
            "astrologerWpTransitChartSubjectLongitudeInput",
            "astrologerWpTransitChartSubjectTimezoneInput",
            "astrologerWpTransitChartSubjectCitySuggestions",
        );

        init_searchCityOnInput(
            "astrologerWpTransitChartForm",
            "astrologerWpTransitChartTransitCityInput",
            "astrologerWpTransitChartTransitNationInput",
            "astrologerWpTransitChartTransitLatitudeInput",
            "astrologerWpTransitChartTransitLongitudeInput",
            "astrologerWpTransitChartTransitTimezoneInput",
            "astrologerWpTransitChartTransitCitySuggestions",
        );
    }
});

async function init_searchCityOnInput(
    formId,
    cityInputId,
    nationInputId,
    latitudeInputId,
    longitudeInputId,
    timezoneInputId,
    suggestionsId,
) {
    const form = document.getElementById(formId);
    const cityInput = document.getElementById(cityInputId);
    const nationInput = document.getElementById(nationInputId);
    const latitudeInput = document.getElementById(latitudeInputId);
    const longitudeInput = document.getElementById(longitudeInputId);
    const timezoneInput = document.getElementById(timezoneInputId);
    const suggestions = document.getElementById(suggestionsId);

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    cityInput.addEventListener(
        "input",
        debounce(async () => {
            const data = await searchCity(cityInput.value, "");
            if (!data) {
                return;
            }
            const suggestionsHtml = data.data
                .map(
                    (city) => /*HTML*/ `
                    <li
                        role="option"
                        class="dropdown-item"
                        data-value-country-code="${city.countryCode}"
                        data-value-city="${city.name}"
                        data-value-lat="${city.lat}"
                        data-value-lng="${city.lng}"
                        data-value-timezone="${city.timezonestr}"
                        tabindex="0">
                        ${city.name}, ${city.countryCode}
                    </li>`,
                )
                .join("");

            // Append the suggestions to the form
            suggestions.innerHTML = suggestionsHtml;
            suggestions.style.display = "block";

            // Add event listeners to the new dropdown items
            const dropdownItems = form.querySelectorAll(".dropdown-item");
            dropdownItems.forEach((item) => {
                item.addEventListener("click", (event) => {
                    event.preventDefault();
                    latitudeInput.value = event.target.getAttribute("data-value-lat");
                    longitudeInput.value = event.target.getAttribute("data-value-lng");
                    cityInput.value = event.target.getAttribute("data-value-city");
                    timezoneInput.value = event.target.getAttribute("data-value-timezone");
                    nationInput.value = event.target.getAttribute("data-value-country-code");
                    suggestions.style.display = "none";
                    cityInput.value = `${event.target.getAttribute("data-value-city")}, ${event.target.getAttribute("data-value-country-code")}`;
                });
            });

            dropdownItems.forEach((item) => {
                item.addEventListener("keydown", (event) => {
                    if (event.key === "Tab") {
                        // Remove aria-selected from all items
                        dropdownItems.forEach((el) => el.setAttribute("aria-selected", "false"));
                        // Set aria-selected to true for the focused item
                        item.setAttribute("aria-selected", "true");

                        latitudeInput.value = event.target.getAttribute("data-value-lat");
                        longitudeInput.value = event.target.getAttribute("data-value-lng");
                        cityInput.value = event.target.getAttribute("data-value-city");
                        timezoneInput.value = event.target.getAttribute("data-value-timezone");
                        nationInput.value = event.target.getAttribute("data-value-country-code");
                        cityInput.value = `${event.target.getAttribute("data-value-city")}, ${event.target.getAttribute("data-value-country-code")}`;
                    } else if (event.key === "Enter") {
                        // Hide the suggestions menu when "Enter" is pressed
                        suggestions.style.display = "none";
                    }
                });
            });
        }),
        500,
    );
}

async function searchCity(city, nation) {
    let data = null;

    if (city.length > 2) {
        try {
            const response = await fetch(
                `${astrologerWpAjax.ajaxurl}?action=search_city&city=${city}&nation=${nation}`,
            );
            data = await response.json();
        } catch (error) {
            console.error("Error fetching city data:", error);
        }

        return data;
    }

    return data;
}
