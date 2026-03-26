document.addEventListener("DOMContentLoaded", () => {
    // Birth Chart
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

    // Synastry Chart
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

    // Transit Chart
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

    // Composite Chart
    if (document.getElementById("astrologerWpCompositeChartForm")) {
        init_searchCityOnInput(
            "astrologerWpCompositeChartForm",
            "astrologerWpCompositeChartFirstCityInput",
            "astrologerWpCompositeChartFirstNationInput",
            "astrologerWpCompositeChartFirstLatitudeInput",
            "astrologerWpCompositeChartFirstLongitudeInput",
            "astrologerWpCompositeChartFirstTimezoneInput",
            "astrologerWpCompositeChartFirstCitySuggestions",
        );

        init_searchCityOnInput(
            "astrologerWpCompositeChartForm",
            "astrologerWpCompositeChartSecondCityInput",
            "astrologerWpCompositeChartSecondNationInput",
            "astrologerWpCompositeChartSecondLatitudeInput",
            "astrologerWpCompositeChartSecondLongitudeInput",
            "astrologerWpCompositeChartSecondTimezoneInput",
            "astrologerWpCompositeChartSecondCitySuggestions",
        );
    }

    // Solar Return Chart
    if (document.getElementById("astrologerWpSolarReturnChartForm")) {
        init_searchCityOnInput(
            "astrologerWpSolarReturnChartForm",
            "astrologerWpSolarReturnChartCityInput",
            "astrologerWpSolarReturnChartNationInput",
            "astrologerWpSolarReturnChartLatitudeInput",
            "astrologerWpSolarReturnChartLongitudeInput",
            "astrologerWpSolarReturnChartTimezoneInput",
            "astrologerWpSolarReturnChartCitySuggestions",
        );

        init_searchCityOnInput(
            "astrologerWpSolarReturnChartForm",
            "astrologerWpSolarReturnChartReturnCityInput",
            "astrologerWpSolarReturnChartReturnNationInput",
            "astrologerWpSolarReturnChartReturnLatitudeInput",
            "astrologerWpSolarReturnChartReturnLongitudeInput",
            "astrologerWpSolarReturnChartReturnTimezoneInput",
            "astrologerWpSolarReturnChartReturnCitySuggestions",
        );
    }

    // Lunar Return Chart
    if (document.getElementById("astrologerWpLunarReturnChartForm")) {
        init_searchCityOnInput(
            "astrologerWpLunarReturnChartForm",
            "astrologerWpLunarReturnChartCityInput",
            "astrologerWpLunarReturnChartNationInput",
            "astrologerWpLunarReturnChartLatitudeInput",
            "astrologerWpLunarReturnChartLongitudeInput",
            "astrologerWpLunarReturnChartTimezoneInput",
            "astrologerWpLunarReturnChartCitySuggestions",
        );

        init_searchCityOnInput(
            "astrologerWpLunarReturnChartForm",
            "astrologerWpLunarReturnChartReturnCityInput",
            "astrologerWpLunarReturnChartReturnNationInput",
            "astrologerWpLunarReturnChartReturnLatitudeInput",
            "astrologerWpLunarReturnChartReturnLongitudeInput",
            "astrologerWpLunarReturnChartReturnTimezoneInput",
            "astrologerWpLunarReturnChartReturnCitySuggestions",
        );
    }

    // Moon Phase
    if (document.getElementById("astrologerWpMoonPhaseForm")) {
        init_searchCityOnInput(
            "astrologerWpMoonPhaseForm",
            "astrologerWpMoonPhaseCityInput",
            "astrologerWpMoonPhaseNationInput",
            "astrologerWpMoonPhaseLatitudeInput",
            "astrologerWpMoonPhaseLongitudeInput",
            "astrologerWpMoonPhaseTimezoneInput",
            "astrologerWpMoonPhaseCitySuggestions",
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

    if (!cityInput || !suggestions) {
        return;
    }

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

            suggestions.innerHTML = suggestionsHtml;
            suggestions.style.display = "block";

            const dropdownItems = suggestions.querySelectorAll(".dropdown-item");
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
                        dropdownItems.forEach((el) => el.setAttribute("aria-selected", "false"));
                        item.setAttribute("aria-selected", "true");

                        latitudeInput.value = event.target.getAttribute("data-value-lat");
                        longitudeInput.value = event.target.getAttribute("data-value-lng");
                        cityInput.value = event.target.getAttribute("data-value-city");
                        timezoneInput.value = event.target.getAttribute("data-value-timezone");
                        nationInput.value = event.target.getAttribute("data-value-country-code");
                        cityInput.value = `${event.target.getAttribute("data-value-city")}, ${event.target.getAttribute("data-value-country-code")}`;
                    } else if (event.key === "Enter") {
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
                `${astrologerWpAjax.ajaxurl}?action=search_city&city=${encodeURIComponent(city)}&nation=${encodeURIComponent(nation)}`,
            );
            data = await response.json();
        } catch (error) {
            console.error("Error fetching city data:", error);
        }

        return data;
    }

    return data;
}
