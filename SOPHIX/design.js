let currentUser = null;

let screenHistory = [];

let currentScreen = "onboarding";


/* ==========================================
   API
========================================== */

async function api(
    action,
    data = {},
    method = "POST"
) {

    const options = {
        method: method
    };


    if (method !== "GET") {

        options.headers = {
            "Content-Type":
                "application/json"
        };

        options.body =
            JSON.stringify(data);

    }


    const response =
        await fetch(
            `api.php?action=${action}`,
            options
        );


    const result =
        await response.json();


    if (!result.success) {

        throw new Error(
            result.message
        );

    }


    return result;

}


/* ==========================================
   SCREEN NAVIGATION
========================================== */

function showScreen(
    screenId,
    saveHistory = true
) {

    const current =
        document.querySelector(
            ".screen.active"
        );


    if (
        current &&
        current.id !== screenId &&
        saveHistory
    ) {

        screenHistory.push(
            current.id
        );

    }


    document
        .querySelectorAll(".screen")
        .forEach(screen => {

            screen.classList.remove(
                "active"
            );

        });


    const target =
        document.getElementById(
            screenId
        );


    if (!target) {

        return;

    }


    target.classList.add("active");

    currentScreen =
        screenId;


    const bottomNav =
        document.getElementById(
            "bottomNav"
        );


    if (
        screenId === "login" ||
        screenId === "register" ||
        screenId === "onboarding"
    ) {

        bottomNav.style.display =
            "none";

    } else {

        bottomNav.style.display =
            "flex";

    }


    if (
        screenId === "dashboard"
    ) {

        loadDashboard();

    }


    if (
        screenId === "profile"
    ) {

        loadProfile();

    }


    if (
        screenId === "bookings"
    ) {

        loadBookings();

    }


    if (
        screenId === "notifications"
    ) {

        loadNotifications();

    }


    if (
        screenId === "travel"
    ) {

        loadServices("travel");

    }


    if (
        screenId === "tours"
    ) {

        loadServices("tour");

    }


    if (
        screenId === "driverDashboard"
    ) {

        loadDriverJobs();

    }


    if (
        screenId === "driverVehicles"
    ) {

        loadDriverVehicles();

    }

}


/* ==========================================
   BACK BUTTON
========================================== */

function goBack() {

    if (screenHistory.length === 0) {

        if (currentUser) {

            showScreen(
                currentUser.role === "driver"
                    ? "driverDashboard"
                    : "dashboard",
                false
            );

        } else {

            showScreen(
                "login",
                false
            );

        }

        return;

    }


    const previous =
        screenHistory.pop();


    showScreen(
        previous,
        false
    );

}


/* ==========================================
   MENU
========================================== */

function toggleMenu() {

    const menu =
        document.getElementById(
            "popupMenu"
        );


    if (menu) {

        menu.classList.toggle(
            "show"
        );

    }

}


/* ==========================================
   SOCIAL BUTTONS
========================================== */

function socialMessage(
    provider
) {

    alert(
        `${provider} sign-in requires OAuth configuration. ` +
        `The email/password account system is already active.`
    );

}


/* ==========================================
   LOGIN
========================================== */

document
    .getElementById("loginForm")
    .addEventListener(
        "submit",
        async function(e) {

            e.preventDefault();


            const data =
                Object.fromEntries(
                    new FormData(this)
                );


            try {

                const result =
                    await api(
                        "login",
                        data
                    );


                currentUser =
                    result.data.user;


                screenHistory = [];


                if (
                    currentUser.role ===
                    "driver"
                ) {

                    showScreen(
                        "driverDashboard",
                        false
                    );

                } else {

                    showScreen(
                        "dashboard",
                        false
                    );

                }


            } catch(error) {

                alert(
                    error.message
                );

            }

        }
    );


/* ==========================================
   REGISTER
========================================== */

document
    .getElementById("registerForm")
    .addEventListener(
        "submit",
        async function(e) {

            e.preventDefault();


            const data =
                Object.fromEntries(
                    new FormData(this)
                );


            try {

                const result =
                    await api(
                        "register",
                        data
                    );


                currentUser =
                    result.data.user;


                screenHistory = [];


                if (
                    currentUser.role ===
                    "driver"
                ) {

                    showScreen(
                        "driverDashboard",
                        false
                    );

                } else {

                    showScreen(
                        "dashboard",
                        false
                    );

                }


            } catch(error) {

                alert(
                    error.message
                );

            }

        }
    );


/* ==========================================
   LOAD DASHBOARD
========================================== */

async function loadDashboard() {

    if (!currentUser) {

        return;

    }


    document
        .getElementById(
            "userGreeting"
        )
        .textContent =
            `Hello, ${currentUser.name}`;


    await loadActivities();

    await checkNotifications();

}


/* ==========================================
   ACTIVITIES
========================================== */

async function loadActivities() {

    try {

        const result =
            await api(
                "activities"
            );


        const container =
            document.getElementById(
                "recentActivities"
            );


        const activities =
            result.data.activities;


        if (
            activities.length === 0
        ) {

            container.innerHTML = `

                <div class="empty-state">

                    <span>📭</span>

                    <p>
                        No recent activities yet.
                    </p>

                    <small>
                        Your bookings will appear here.
                    </small>

                </div>

            `;

            return;

        }


        container.innerHTML =
            activities
                .map(activity => `

                    <div
                        class="activity-card"
                    >

                        <div
                            class="activity-card-header"
                        >

                            <strong>
                                ${escapeHTML(
                                    activity.service_name
                                    || activity.service_type
                                )}
                            </strong>

                            <span
                                class="status"
                            >
                                ${formatStatus(
                                    activity.status
                                )}
                            </span>

                        </div>

                        <small>
                            ${
                                activity.pickup_location
                                || ""
                            }

                            ${
                                activity.destination
                                ? " → "
                                + activity.destination
                                : ""
                            }
                        </small>

                    </div>

                `)
                .join("");


    } catch(error) {

        console.error(error);

    }

}


/* ==========================================
   PACKAGE FORM
========================================== */

document
    .getElementById("packageForm")
    .addEventListener(
        "submit",
        async function(e) {

            e.preventDefault();


            const data =
                Object.fromEntries(
                    new FormData(this)
                );


            const resultBox =
                document.getElementById(
                    "packageResult"
                );


            resultBox.innerHTML =
                "<p>Searching for a delivery option...</p>";


            try {

                const result =
                    await api(
                        "create_package",
                        data
                    );


                resultBox.innerHTML = `

                    <div class="activity-card">

                        <h3>
                            Package found ✓
                        </h3>

                        <p>
                            ${escapeHTML(
                                result.message
                            )}
                        </p>

                        <br>

                        <strong>
                            Tracking number
                        </strong>

                        <p>
                            ${result.data.tracking_number}
                        </p>

                        <small>
                            Save this number to track
                            your package.
                        </small>

                    </div>

                `;


                this.reset();


            } catch(error) {

                resultBox.innerHTML = `

                    <div class="activity-card">

                        <h3>
                            Unable to create package
                        </h3>

                        <p>
                            ${escapeHTML(
                                error.message
                            )}
                        </p>

                    </div>

                `;

            }

        }
    );


/* ==========================================
   TRACKING
========================================== */

async function trackPackage() {

    const tracking =
        document
            .getElementById(
                "trackingNumber"
            )
            .value
            .trim();


    const resultBox =
        document.getElementById(
            "trackingResult"
        );


    if (!tracking) {

        resultBox.innerHTML = `

            <div class="activity-card">

                Please enter your tracking number.

            </div>

        `;

        return;

    }


    resultBox.innerHTML =
        "<p>Searching...</p>";


    try {

        const result =
            await api(
                "track",
                {
                    tracking_number:
                        tracking
                }
            );


        const p =
            result.data.package;


        resultBox.innerHTML = `

            <div class="activity-card">

                <h3>
                    Package found ✓
                </h3>

                <br>

                <p>

                    <strong>
                        Route:
                    </strong>

                    ${escapeHTML(
                        p.pickup_location
                    )}

                    →

                    ${escapeHTML(
                        p.destination
                    )}

                </p>

                <br>

                <p>

                    <strong>
                        Status:
                    </strong>

                    ${formatStatus(
                        p.status
                    )}

                </p>


                <p>

                    <strong>
                        Tracking:
                    </strong>

                    ${escapeHTML(
                        p.tracking_number
                    )}

                </p>


                ${
                    p.driver_name
                    ?

                    `

                    <br>

                    <p>
                        <strong>
                            Driver:
                        </strong>

                        ${escapeHTML(
                            p.driver_name
                        )}

                    </p>

                    <p>

                        <strong>
                            Vehicle:
                        </strong>

                        ${escapeHTML(
                            p.vehicle_model
                            || "Assigned vehicle"
                        )}

                    </p>

                    `

                    :

                    `

                    <br>

                    <p>
                        No driver has accepted
                        this package yet.
                    </p>

                    `
                }

            </div>

        `;


    } catch(error) {

        resultBox.innerHTML = `

            <div class="activity-card">

                <h3>
                    Package not found
                </h3>

                <p>
                    ${escapeHTML(
                        error.message
                    )}
                </p>

            </div>

        `;

    }

}


/* ==========================================
   VEHICLE BOOKING
========================================== */

document
    .getElementById(
        "vehicleBookingForm"
    )
    .addEventListener(
        "submit",
        async function(e) {

            e.preventDefault();


            const data =
                Object.fromEntries(
                    new FormData(this)
                );


            const resultBox =
                document.getElementById(
                    "vehicleResult"
                );


            resultBox.innerHTML =
                "<p>Checking available vehicles...</p>";


            try {

                const result =
                    await api(
                        "book_vehicle",
                        data
                    );


                if (
                    result.data.available
                ) {

                    const vehicle =
                        result.data.vehicle;


                    resultBox.innerHTML = `

                        <div class="activity-card">

                            <h3>
                                Vehicle found ✓
                            </h3>

                            <p>
                                ${escapeHTML(
                                    vehicle.model
                                )}
                            </p>

                            <p>
                                Type:
                                ${escapeHTML(
                                    vehicle.vehicle_type
                                )}
                            </p>

                            <p>
                                Driver:
                                ${escapeHTML(
                                    vehicle.driver_name
                                )}
                            </p>

                            <br>

                            <small>
                                Your booking request
                                has been sent to the driver.
                            </small>

                        </div>

                    `;

                } else {

                    resultBox.innerHTML = `

                        <div class="activity-card">

                            <h3>
                                No vehicle available
                            </h3>

                            <p>
                                ${escapeHTML(
                                    result.message
                                )}
                            </p>

                        </div>

                    `;

                }


            } catch(error) {

                resultBox.innerHTML = `

                    <div class="activity-card">

                        ${escapeHTML(
                            error.message
                        )}

                    </div>

                `;

            }

        }
    );


/* ==========================================
   TRAVEL / TOURS
========================================== */

async function loadServices(
    category
) {

    const container =
        document.getElementById(
            category === "travel"
                ? "travelServices"
                : "tourServices"
        );


    container.innerHTML =
        "<p>Loading...</p>";


    try {

        const result =
            await api(
                "travel_services",
                {
                    category
                }
            );


        if (
            result.data.services.length === 0
        ) {

            container.innerHTML = `

                <div class="empty-state">

                    No services are currently available.

                </div>

            `;

            return;

        }


        container.innerHTML =
            result.data.services
                .map(service => `

                    <div class="service-card">

                        <h3>
                            ${escapeHTML(
                                service.title
                            )}
                        </h3>

                        <p>
                            ${escapeHTML(
                                service.description
                            )}
                        </p>

                        <div class="service-price">

                            From ₦${Number(
                                service.price_from
                            ).toLocaleString()}

                        </div>

                        <button
                            class="primary-btn"
                            onclick="bookService(
                                ${service.id}
                            )"
                        >
                            Book Now
                        </button>

                    </div>

                `)
                .join("");


    } catch(error) {

        container.innerHTML =
            `<p>${escapeHTML(
                error.message
            )}</p>`;

    }

}


async function bookService(
    serviceId
) {

    const pickup =
        prompt(
            "Where will you start from?"
        );


    if (!pickup) {

        return;

    }


    const destination =
        prompt(
            "Where are you going?"
        );


    if (!destination) {

        return;

    }


    try {

        const result =
            await api(
                "vehicle_status",
                {
                    vehicle_id:
                        vehicleId,

                    status
                }
            );


        alert(
            result.message
        );


        loadDriverVehicles();


    } catch(error) {

        alert(
            error.message
        );

    }

}


/* ==========================================
   LOGOUT
========================================== */

async function logout() {

    try {

        await api(
            "logout"
        );


        currentUser = null;

        screenHistory = [];


        showScreen(
            "login",
            false
        );


    } catch(error) {

        alert(
            error.message
        );

    }

}


/* ==========================================
   HTML SAFETY
========================================== */

function escapeHTML(
    value
) {

    if (
        value === null ||
        value === undefined
    ) {

        return "";

    }


    return String(value)

        .replaceAll(
            "&",
            "&amp;"
        )

        .replaceAll(
            "<",
            "&lt;"
        )

        .replaceAll(
            ">",
            "&gt;"
        )

        .replaceAll(
            '"',
            "&quot;"
        )

        .replaceAll(
            "'",
            "&#039;"
        );

}


/* ==========================================
   STATUS
========================================== */

function formatStatus(
    status
) {

    return String(status)
        .replaceAll(
            "_",
            " "
        )
        .replace(
            /^./,
            letter =>
                letter.toUpperCase()
        );

}


/* ==========================================
   START APP
========================================== */

async function startApp() {

    try {

        const result =
            await api(
                "me"
            );


        currentUser =
            result.data.user;


        if (
            currentUser.role ===
            "driver"
        ) {

            showScreen(
                "driverDashboard",
                false
            );

        } else {

            showScreen(
                "dashboard",
                false
            );

        }

    } catch(error) {

        showScreen(
            "onboarding",
            false
        );

    }

}


startApp();

// load current role on profile load
async function loadProfile() {
  const res = await fetch('api.php?action=me');
  const data = await res.json();
  const role = data.user.active_role || 'customer';
  document.getElementById('currentRoleText').innerText = role === 'driver' ? 'Driver Mode' : 'Customer Mode';
  document.getElementById('roleToggle').checked = role === 'driver';
}

document.getElementById('roleToggle')?.addEventListener('change', async (e) => {
  const wantsDriver = e.target.checked;
  if (wantsDriver) {
    // if never been driver, show form
    const res = await fetch('api.php?action=me');
    const data = await res.json();
    if (!data.user.is_driver) {
      document.getElementById('driverOnboarding').style.display = 'block';
      e.target.checked = false; // wait till they fill form
      return;
    }
    switchRole('driver');
  } else {
    switchRole('customer');
  }
});

async function switchRole(role) {
  const res = await fetch('api.php?action=switch_role', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ role })
  });
  const data = await res.json();
  if (data.success) {
    location.reload(); // reload to show driver dashboard or customer dashboard
  } else {
    alert(data.message);
  }
}

async function confirmDriverSwitch() {
  const vehicle_type = document.getElementById('vehicleType').value;
  const license_number = document.getElementById('licenseNum').value;
  if (!vehicle_type) return alert('Enter vehicle type');
  
  const res = await fetch('api.php?action=switch_role', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'},
    body: JSON.stringify({ role: 'driver', vehicle_type, license_number })
  });
  const data = await res.json();
  if (data.success) location.reload();
}

