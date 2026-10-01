/* =========================================================
   INFINEX - MAIN JAVASCRIPT
   Location Picker + General Page Functions
========================================================= */


/* =========================================================
   AUTO HIDE MESSAGES
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    document
        .querySelectorAll("[data-auto-hide]")
        .forEach(function (element) {

            setTimeout(function () {
                element.remove();
            }, 4000);

        });

});


/* =========================================================
   LOCATION PICKER VARIABLES
========================================================= */

let locationMap = null;
let locationMarker = null;
let locationGeocoder = null;
let locationAutocomplete = null;

let selectedLatitude = null;
let selectedLongitude = null;


/* =========================================================
   INITIALIZE GOOGLE MAP
========================================================= */

async function initLocationPicker() {

    console.log("INFINEX: Initializing Google Maps...");

    /* -----------------------------------------
       Check required HTML elements
    ----------------------------------------- */

    const mapElement =
        document.getElementById("locationMap");

    const searchInput =
        document.getElementById("locationSearch");

    const currentLocationButton =
        document.getElementById("currentLocationBtn");

    if (!mapElement) {

        console.error(
            "INFINEX: #locationMap element was not found."
        );

        return;
    }

    if (!searchInput) {

        console.error(
            "INFINEX: #locationSearch element was not found."
        );

        return;
    }


    /* -----------------------------------------
       Default location - Sri Lanka
    ----------------------------------------- */

    const defaultLocation = {
        lat: 7.8731,
        lng: 80.7718
    };


    /* -----------------------------------------
       Create Google Map
    ----------------------------------------- */

    try {

        locationMap = new google.maps.Map(
            mapElement,
            {
                center: defaultLocation,
                zoom: 7,

                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,

                zoomControl: true,

                gestureHandling: "greedy"
            }
        );

    } catch (error) {

        console.error(
            "INFINEX: Failed to create Google Map.",
            error
        );

        return;
    }


    /* -----------------------------------------
       Create Geocoder
    ----------------------------------------- */

    locationGeocoder =
        new google.maps.Geocoder();


    /* -----------------------------------------
       Create draggable marker
    ----------------------------------------- */

    locationMarker =
        new google.maps.Marker({

            map: locationMap,

            position: defaultLocation,

            draggable: true,

            title: "Select your location"

        });


    /* =====================================================
       GOOGLE PLACES AUTOCOMPLETE
    ===================================================== */

    try {

        locationAutocomplete =
            new google.maps.places.Autocomplete(
                searchInput,
                {

                    componentRestrictions: {
                        country: "lk"
                    },

                    fields: [
                        "formatted_address",
                        "geometry",
                        "name"
                    ]

                }
            );


        /* -----------------------------------------
           Place selected from search
        ----------------------------------------- */

        locationAutocomplete.addListener(
            "place_changed",
            function () {

                const place =
                    locationAutocomplete.getPlace();


                if (
                    !place ||
                    !place.geometry ||
                    !place.geometry.location
                ) {

                    console.warn(
                        "INFINEX: No valid place was selected."
                    );

                    return;
                }


                const latitude =
                    place.geometry.location.lat();

                const longitude =
                    place.geometry.location.lng();


                const address =
                    place.formatted_address ||
                    place.name ||
                    "Selected location";


                /* Save selected location */

                setSelectedLocation(
                    latitude,
                    longitude,
                    address
                );


                /* Move map */

                locationMap.setCenter({

                    lat: latitude,
                    lng: longitude

                });


                locationMap.setZoom(16);


                console.log(
                    "INFINEX: Location selected:",
                    latitude,
                    longitude,
                    address
                );

            }
        );


    } catch (error) {

        console.error(
            "INFINEX: Places Autocomplete failed.",
            error
        );

    }


    /* =====================================================
       MARKER DRAGGING
    ===================================================== */

    locationMarker.addListener(
        "dragend",
        function () {

            const position =
                locationMarker.getPosition();


            if (!position) {
                return;
            }


            const latitude =
                position.lat();

            const longitude =
                position.lng();


            console.log(
                "INFINEX: Marker moved:",
                latitude,
                longitude
            );


            /* Reverse geocode the new position */

            reverseGeocode(
                latitude,
                longitude
            );

        }
    );


    /* =====================================================
       CURRENT LOCATION BUTTON
    ===================================================== */

    if (currentLocationButton) {

        currentLocationButton.addEventListener(
            "click",
            getCurrentLocation
        );

    }


    console.log(
        "INFINEX: Google Maps initialized successfully."
    );

}


/* =========================================================
   GET USER'S CURRENT LOCATION
========================================================= */

function getCurrentLocation() {

    const status =
        document.getElementById(
            "locationStatus"
        );


    /* -----------------------------------------
       Check browser support
    ----------------------------------------- */

    if (!navigator.geolocation) {

        if (status) {

            status.textContent =
                "Geolocation is not supported by your browser.";

        }

        return;
    }


    /* -----------------------------------------
       Show loading status
    ----------------------------------------- */

    if (status) {

        status.textContent =
            "Detecting your current location...";

    }


    /* -----------------------------------------
       Check if Google Map is ready
    ----------------------------------------- */

    if (!locationMap || !locationMarker) {

        if (status) {

            status.textContent =
                "Map is still loading. Please try again.";

        }

        console.error(
            "INFINEX: Google Map is not initialized."
        );

        return;
    }


    /* =====================================================
       BROWSER GEOLOCATION
    ===================================================== */

    navigator.geolocation.getCurrentPosition(

        function (position) {

            const latitude =
                position.coords.latitude;

            const longitude =
                position.coords.longitude;

            const accuracy =
                position.coords.accuracy;


            console.log(
                "INFINEX: Current location detected:",
                latitude,
                longitude,
                "Accuracy:",
                accuracy
            );


            /* -----------------------------------------
               Save accuracy
            ----------------------------------------- */

            const accuracyInput =
                document.getElementById(
                    "location_accuracy"
                );


            if (accuracyInput) {

                accuracyInput.value =
                    accuracy;

            }


            /* -----------------------------------------
               Move map
            ----------------------------------------- */

            locationMap.setCenter({

                lat: latitude,
                lng: longitude

            });


            locationMap.setZoom(17);


            /* -----------------------------------------
               Move marker
            ----------------------------------------- */

            locationMarker.setPosition({

                lat: latitude,
                lng: longitude

            });


            /* -----------------------------------------
               Reverse geocode
            ----------------------------------------- */

            reverseGeocode(
                latitude,
                longitude
            );


            /* -----------------------------------------
               Status
            ----------------------------------------- */

            if (status) {

                status.textContent =
                    "Current location detected.";

            }

        },


        function (error) {

            console.error(
                "INFINEX: Geolocation error:",
                error
            );


            if (!status) {
                return;
            }


            switch (error.code) {

                case error.PERMISSION_DENIED:

                    status.textContent =
                        "Location permission was denied.";

                    break;


                case error.POSITION_UNAVAILABLE:

                    status.textContent =
                        "Location information is unavailable.";

                    break;


                case error.TIMEOUT:

                    status.textContent =
                        "Location request timed out.";

                    break;


                default:

                    status.textContent =
                        "Unable to detect your location.";

                    break;

            }

        },


        {
            enableHighAccuracy: true,

            timeout: 15000,

            maximumAge: 0
        }

    );

}


/* =========================================================
   REVERSE GEOCODING
========================================================= */

function reverseGeocode(
    latitude,
    longitude
) {

    if (!locationGeocoder) {

        console.error(
            "INFINEX: Geocoder is not initialized."
        );

        return;
    }


    locationGeocoder.geocode(

        {
            location: {

                lat: latitude,

                lng: longitude

            }
        },


        function (
            results,
            status
        ) {


            console.log(
                "INFINEX: Reverse geocoding status:",
                status
            );


            if (
                status === "OK" &&
                results &&
                results.length > 0
            ) {

                const address =
                    results[0].formatted_address;


                setSelectedLocation(

                    latitude,

                    longitude,

                    address

                );


            } else {

                setSelectedLocation(

                    latitude,

                    longitude,

                    "Selected map location"

                );

            }

        }

    );

}


/* =========================================================
   SAVE SELECTED LOCATION
========================================================= */

function setSelectedLocation(
    latitude,
    longitude,
    address
) {

    /* -----------------------------------------
       Save JavaScript values
    ----------------------------------------- */

    selectedLatitude =
        latitude;

    selectedLongitude =
        longitude;


    /* -----------------------------------------
       Move marker
    ----------------------------------------- */

    if (locationMarker) {

        locationMarker.setPosition({

            lat: latitude,

            lng: longitude

        });

    }


    /* =====================================================
       SAVE HIDDEN FORM VALUES
    ===================================================== */

    const latitudeInput =
        document.getElementById(
            "latitude"
        );


    const longitudeInput =
        document.getElementById(
            "longitude"
        );


    const addressInput =
        document.getElementById(
            "address"
        );


    const searchInput =
        document.getElementById(
            "locationSearch"
        );


    if (latitudeInput) {

        latitudeInput.value =
            latitude;

    }


    if (longitudeInput) {

        longitudeInput.value =
            longitude;

    }


    if (addressInput) {

        addressInput.value =
            address;

    }


    if (searchInput) {

        searchInput.value =
            address;

    }


    /* =====================================================
       UPDATE SELECTED LOCATION BOX
    ===================================================== */

    const selectedBox =
        document.getElementById(
            "selectedLocation"
        );


    if (selectedBox) {

        selectedBox.innerHTML = `

            <i class="fa-solid fa-location-dot"></i>

            <div>

                <strong>
                    Location selected
                </strong>

                <small>
                    ${escapeHtml(address)}
                </small>

            </div>

        `;

    }


    console.log(
        "INFINEX: Selected location saved:",
        {
            latitude: latitude,

            longitude: longitude,

            address: address
        }
    );

}


/* =========================================================
   ESCAPE HTML
   Prevent address text from being interpreted as HTML
========================================================= */

function escapeHtml(text) {

    const div =
        document.createElement("div");

    div.textContent =
        text || "";

    return div.innerHTML;

}


/* =========================================================
   MAKE GOOGLE MAPS CALLBACK GLOBAL
========================================================= */

window.initLocationPicker =
    initLocationPicker;


/* =========================================================
   DEBUG MESSAGE
========================================================= */

console.log(
    "INFINEX main.js loaded successfully."
);