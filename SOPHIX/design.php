<?php

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sophix Dynamics</title>

    <link
        rel="stylesheet"
        href="design.css"
    >
    

</head>


<body>


<!-- ================================
     APP
================================ -->

<div id="app">


<!-- ================================
     ONBOARDING
================================ -->

<section
    class="screen active"
    id="onboarding"
>

    <div class="onboarding-card">

        <div class="logo">
            Sophix <span>Dynamics</span>
        </div>

        <div class="hero-icon">
            ✈️
        </div>

        <h1>
            Logistics & Travel
            Made Simple.
        </h1>

        <p>
            Deliver packages, connect places,
            and create experiences you can trust.
        </p>

        <button
            class="primary-btn"
            onclick="showScreen('login')"
        >
            Get Started
        </button>

        <button
            class="text-btn"
            onclick="showScreen('login')"
        >
            Skip
        </button>

    </div>

</section>


<!-- ================================
     LOGIN
================================ -->

<section
    class="screen"
    id="login"
>

    <div class="auth-card">

        <div class="back-button"
             onclick="goBack()">
            ←
        </div>

        <div class="logo">
            Sophix <span>Dynamics</span>
        </div>

        <h1>
            Welcome back 👋
        </h1>

        <p>
            Sign in to continue.
        </p>


        <form id="loginForm">

            <input
                type="email"
                name="email"
                placeholder="Email address"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button
                class="primary-btn"
                type="submit"
            >
                Sign In
            </button>

        </form>


        </div>


        <p class="switch-text">

            Don't have an account?

            <button
                class="link-btn"
                onclick="showScreen('register')"
            >
                Sign up
            </button>

        </p>

    </div>

</section>


<!-- ================================
     REGISTER
================================ -->

<section
    class="screen"
    id="register"
>

    <div class="auth-card">

        <div
            class="back-button"
            onclick="goBack()"
        >
            ←
        </div>

        <div class="logo">
            Sophix <span>Dynamics</span>
        </div>

        <h1>
            Create account
        </h1>

        <p>
            Join Sophix Dynamics.
        </p>


        <form id="registerForm">

            <input
                name="name"
                placeholder="Full name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email address"
                required
            >

            <input
                name="phone"
                placeholder="Phone number"
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                minlength="6"
                required
            >


            <select name="role">

                <option value="customer">
                    I'm a Customer
                </option>

                <option value="driver">
                    I'm a Driver
                </option>

            </select>


            <button
                class="primary-btn"
                type="submit"
            >
                Create Account
            </button>

        </form>

    </div>

</section>


<!-- ================================
     CUSTOMER DASHBOARD
================================ -->

<section
    class="screen"
    id="dashboard"
>

    <header class="topbar">

        <div>

            <small>
                Welcome back
            </small>

            <h2 id="userGreeting">
                Hello
            </h2>

        </div>


        <button
            class="icon-btn"
            onclick="showScreen('notifications')"
        >
            🔔

            <span
                class="notification-dot"
                id="notificationDot"
            ></span>

        </button>


        <button
            class="icon-btn"
            onclick="toggleMenu()"
        >
            ⋮
        </button>

    </header>


    <div
        class="popup-menu"
        id="popupMenu"
    >

        <button
            onclick="showScreen('profile'); toggleMenu();"
        >
            👤 Profile
        </button>

        <button
            onclick="showScreen('settings'); toggleMenu();"
        >
            ⚙️ Settings
        </button>

        <button
            onclick="logout()"
        >
            🚪 Logout
        </button>

    </div>


    <div class="search-box">

        🔍

        <input
            id="globalSearch"
            placeholder="Search Sophix"
        >

    </div>


    <div class="hero-banner">

        <div>

            <span>
                Sophix Dynamics
            </span>

            <h1>
                Move smarter.
            </h1>

            <p>
                Send. Travel. Explore.
            </p>

        </div>

        <div class="hero-plane">
            ✈️
        </div>

    </div>


    <h3>
        What do you need today?
    </h3>


    <div class="quick-grid">

        <button
            class="quick-card"
            onclick="showScreen('sendPackage')"
        >

            <span>📦</span>

            <strong>
                Send Package
            </strong>

            <small>
                Deliver anywhere
            </small>

        </button>


        <button
            class="quick-card"
            onclick="showScreen('vehicles')"
        >

            <span>🚚</span>

            <strong>
                Hire a Vehicle
            </strong>

            <small>
                Find available vehicles
            </small>

        </button>


        <button
        
            class="quick-card"
            onclick="showScreen('travel')"
        >
        <a href="travel.php" style="text-decoration:none; color:inherit;">
            <span>✈️</span>

            <strong>
                Travel
            </strong>

            <small>
                Plan your journey
            </small>

        </button>

    

        <button
            class="quick-card"
            onclick="showScreen('tours')"
        >

            <span>🌍</span>

            <strong>
                Tours
            </strong>

            <small>
                Explore new places
            </small>

        </button>

    </div>


    <div class="section-heading">

        <h3>
            Track your delivery
        </h3>

        <button
            onclick="showScreen('tracking')"
        >
            Track
        </button>

    </div>


    <div class="tracking-mini">

        <span>
            📦
        </span>

        <div>

            <strong>
                Have a tracking number?
            </strong>

            <small>
                Find your package instantly.
            </small>

        </div>

        <button
            onclick="showScreen('tracking')"
        >
            →
        </button>

    </div>


    <div class="section-heading">

        <h3>
            Recent activities
        </h3>

        <button
            onclick="showScreen('bookings')"
        >
            View all
        </button>

    </div>


    <div
        id="recentActivities"
        class="activity-list"
    >

        <div class="empty-state">

            <span>📭</span>

            <p>
                No recent activities yet.
            </p>

            <small>
                Your bookings will appear here.
            </small>

        </div>

    </div>

</section>


<!-- ================================
     SEND PACKAGE
================================ -->

<section
    class="screen"
    id="sendPackage"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Send a Package
        </h2>

    </div>


    <div class="form-card">

        <p>
            Tell us about your package.
        </p>


        <form id="packageForm">

            <label>
                Pickup location
            </label>

            <input
                name="pickup_location"
                placeholder="e.g. Abuja"
                required
            >


            <label>
                Destination
            </label>

            <input
                name="destination"
                placeholder="e.g. Lagos"
                required
            >


            <label>
                Package type
            </label>

            <select name="package_type" required>

                <option value="">
                    Choose package type
                </option>

                <option>
                    Documents
                </option>

                <option>
                    Clothing
                </option>

                <option>
                    Electronics
                </option>

                <option>
                    Food
                </option>

                <option>
                    Other
                </option>

            </select>


            <label>
                Package size
            </label>

            <select name="package_size">

                <option value="small">
                    Small
                </option>

                <option value="medium">
                    Medium
                </option>

                <option value="large">
                    Large
                </option>

            </select>


            <label>
                Weight (kg)
            </label>

            <input
                type="number"
                step="0.1"
                name="weight_kg"
                placeholder="Optional"
            >


            <button
                class="primary-btn"
                type="submit"
            >
                Find Delivery Option
            </button>

        </form>


        <div id="packageResult"></div>

    </div>

</section>


<!-- ================================
     VEHICLES
================================ -->

<section
    class="screen"
    id="vehicles"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Hire a Vehicle
        </h2>

    </div>


    <div class="form-card">

        <h3>
            Choose a vehicle
        </h3>


        <form id="vehicleBookingForm">

            <label>
                Vehicle type
            </label>

            <select
                name="vehicle_type"
                required
            >

                <option value="">
                    Choose vehicle type
                </option>

                <option value="car">
                    🚗 Car
                </option>

                <option value="van">
                    🚐 Van
                </option>

                <option value="truck">
                    🚚 Truck
                </option>

                <option value="bike">
                    🏍️ Bike
                </option>

                <option value="bus">
                    🚌 Bus
                </option>

            </select>


            <input
                name="pickup_location"
                placeholder="Pickup location"
                required
            >


            <input
                name="destination"
                placeholder="Destination"
                required
            >


            <button
                class="primary-btn"
                type="submit"
            >
                Find & Book Vehicle
            </button>

        </form>


        <div id="vehicleResult"></div>

    </div>

</section>


<!-- ================================
     TRAVEL
================================ -->

<section
    class="screen"
    id="travel"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Travel
        </h2>

    </div>


    <p class="page-description">
        Choose the travel service you need.
    </p>


    <div
        id="travelServices"
        class="service-grid"
    ></div>

</section>


<!-- ================================
     TOURS
================================ -->

<section
    class="screen"
    id="tours"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Tours
        </h2>

    </div>


    <p class="page-description">
        Discover places and experiences.
    </p>


    <div
        id="tourServices"
        class="service-grid"
    ></div>

</section>


<!-- ================================
     TRACKING
================================ -->

<section
    class="screen"
    id="tracking"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Track Package
        </h2>

    </div>


    <div class="form-card">

        <span class="big-icon">
            📦
        </span>

        <h3>
            Where is your package?
        </h3>

        <p>
            Enter the tracking number
            you received after creating
            your delivery.
        </p>


        <input
            id="trackingNumber"
            placeholder="SOP-XXXXXXXX"
        >


        <button
            class="primary-btn"
            onclick="trackPackage()"
        >
            Track Package
        </button>


        <div id="trackingResult"></div>

    </div>

</section>


<!-- ================================
     BOOKINGS
================================ -->

<section
    class="screen"
    id="bookings"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            My Bookings
        </h2>

    </div>


    <div
        id="bookingList"
        class="activity-list"
    ></div>

</section>


<!-- ================================
     NOTIFICATIONS
================================ -->

<section
    class="screen"
    id="notifications"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Notifications
        </h2>

    </div>


    <button
        class="secondary-btn"
        onclick="markNotificationsRead()"
    >
        Mark all as read
    </button>


    <div
        id="notificationList"
        class="activity-list"
    ></div>

</section>


<!-- ================================
     PROFILE
================================ -->

<section
    class="screen"
    id="profile"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Profile
        </h2>

    </div>
     
    <div class="profile-card" style="text-align:center; padding:20px;">
  <div class="profile-avatar" style="margin:0 auto;">
    <!-- your avatar icon stays here -->
  </div>
  
  <h3 id="profileName" style="margin-top:15px; text-align:center;">Loading...</h3>
  <p id="profileEmail" style="text-align:center; color:gray;">Loading...</p>
  <span id="profileRoleBadge" style="background:#e0e7ff; padding:5px 10px; border-radius:20px; font-size:12px;">Customer</span>
  
  <div style="margin-top:20px; background:white; padding:15px; border-radius:12px; text-align:left; border:1px solid #eee;">
    <p><strong>Orders made:</strong> <span id="profileOrders">0</span></p>
    <p><strong>Account:</strong> <span id="profileStatus">Loading...</span></p>
  </div>

  <!-- SWITCH - NOW UNDER AND CENTERED -->
  <div style="margin:20px 0; background:#f3f4f6; padding:12px; border-radius:12px; display:flex; justify-content:space-between; align-items:center; text-align:left;">
    <div>
      <strong id="currentRoleText">Customer Mode</strong>
      <p style="font-size:12px; color:gray; margin:0;">Tap to switch</p>
    </div>
    <input type="checkbox" id="roleToggle" style="width:40px; height:22px; accent-color:#0d4a6f;">
  </div>

  <div id="driverOnboarding" style="display:none; margin:10px 0; padding:12px; border:1px solid #ddd; border-radius:12px; background:white; text-align:left;">
    <h4>Become a driver</h4>
    <input id="vehicleType" placeholder="Vehicle type e.g Van" style="width:100%; padding:8px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">
    <input id="licenseNum" placeholder="License number" style="width:100%; padding:8px; margin:5px 0; border-radius:6px; border:1px solid #ccc;">
    <button onclick="confirmDriverSwitch()" style="width:100%; background:#0d4a6f; color:white; padding:10px; border-radius:8px; border:none; margin-top:5px;">Switch to Driver</button>
  </div>
</div>


    <button
        class="danger-btn"
        onclick="logout()"
    >
        Log Out
    </button>

</section>


<!-- ================================
     SETTINGS
================================ -->

<section
    class="screen"
    id="settings"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            Settings
        </h2>

    </div>


    <div class="settings-list">

        <label class="setting-row">

            <div>

                <strong>
                    Notifications
                </strong>

                <small>
                    Receive booking updates.
                </small>

            </div>

            <input
                type="checkbox"
                id="notificationSetting"
                onchange="updateSettings()"
            >

        </label>


        <button
            class="setting-row"
            onclick="alert('Privacy settings can be added here.')"
        >

            <div>

                <strong>
                    Privacy
                </strong>

                <small>
                    Manage your privacy.
                </small>

            </div>

            <span>
                →
            </span>

        </button>


        <button
            class="setting-row"
            onclick="alert('Help centre coming soon.')"
        >

            <div>

                <strong>
                    Help & Support
                </strong>

                <small>
                    Get assistance.
                </small>

            </div>

            <span>
                →
            </span>

        </button>

    </div>

</section>


<!-- ================================
     DRIVER DASHBOARD
================================ -->

<section
    class="screen"
    id="driverDashboard"
>

    <header class="topbar">

        <div>

            <small>
                Driver Dashboard
            </small>

            <h2 id="driverGreeting">
                Hello
            </h2>

        </div>


        <button
            class="icon-btn"
            onclick="showScreen('notifications')"
        >
            🔔
        </button>


        <button
            class="icon-btn"
            onclick="toggleMenu()"
        >
            ⋮
        </button>

    </header>


    <div class="driver-status">

        <div>

            <strong>
                Driver mode
            </strong>

            <small>
                Make your vehicle available.
            </small>

        </div>

        <button
            class="status-toggle"
            onclick="showScreen('driverVehicles')"
        >
            My Vehicles
        </button>

    </div>


    <h3>
        Your jobs
    </h3>


    <div
        id="driverJobs"
        class="activity-list"
    ></div>


    <button
        class="primary-btn"
        onclick="showScreen('driverVehicles')"
    >
        🚚 Manage Vehicles
    </button>

</section>


<!-- ================================
     DRIVER VEHICLES
================================ -->

<section
    class="screen"
    id="driverVehicles"
>

    <div class="page-header">

        <button
            onclick="goBack()"
            class="back-button"
        >
            ←
        </button>

        <h2>
            My Vehicles
        </h2>

    </div>


    <div class="form-card">

        <h3>
            Register a vehicle
        </h3>


        <form id="vehicleForm">

            <select
                name="vehicle_type"
                required
            >

                <option value="">
                    Choose vehicle type
                </option>

                <option value="car">
                    Car
                </option>

                <option value="van">
                    Van
                </option>

                <option value="truck">
                    Truck
                </option>

                <option value="bike">
                    Bike
                </option>

                <option value="bus">
                    Bus
                </option>

            </select>


            <input
                name="model"
                placeholder="Vehicle model"
                required
            >


            <input
                name="plate_number"
                placeholder="Plate number"
                required
            >


            <input
                name="capacity_kg"
                type="number"
                placeholder="Capacity in kg"
                required
            >


            <button
                class="primary-btn"
                type="submit"
            >
                Register Vehicle
            </button>

        </form>

    </div>


    <h3>
        Registered vehicles
    </h3>


    <div
        id="myVehicles"
        class="activity-list"
    ></div>

</section>


<!-- ================================
     BOTTOM NAV
================================ -->

<nav
    class="bottom-nav"
    id="bottomNav"
>

    <button
        onclick="showScreen('dashboard')"
    >
        <span>⌂</span>
        Home
    </button>


    <button
        onclick="showScreen('bookings')"
    >
        <span>▣</span>
        Bookings
    </button>


    <button
        onclick="showScreen('tracking')"
    >
        <span>⌖</span>
        Track
    </button>


    <button
        onclick="showScreen('notifications')"
    >
        <span>♢</span>
        Activity
    </button>


    <button
        onclick="showScreen('profile')"
    >
        <span>♙</span>
        Profile
    </button>

</nav>


</div>


<script src="design.js"></script>

<script>
async function loadMyProfile() {
  try {
    const res = await fetch('api.php?action=me');
    const data = await res.json();
    if (data.success) {
      const u = data.data.user;
      document.getElementById('profileName').textContent = u.name;
      document.getElementById('profileEmail').textContent = u.email;
      document.getElementById('profileOrders').textContent = u.order_count;
      document.getElementById('profileStatus').textContent = u.is_new ? 'New account' : 'Old account';
      document.getElementById('currentRoleText').textContent = u.active_role === 'driver' ? 'Driver Mode' : 'Customer Mode';
      document.getElementById('profileRoleBadge').textContent = u.active_role === 'driver' ? 'Driver' : 'Customer';
      document.getElementById('roleToggle').checked = u.active_role === 'driver';
    }
  } catch(e) { console.log(e); }
}
document.addEventListener('DOMContentLoaded', loadMyProfile);

// Make toggle responsive
document.getElementById('roleToggle')?.addEventListener('change', function(e){
  if(e.target.checked){
    document.getElementById('driverOnboarding').style.display = 'block';
  } else {
    // switch back to customer
    fetch('api.php?action=switch_role', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({role:'customer'})
    }).then(()=>{ loadMyProfile(); alert('Switched to Customer'); });
    document.getElementById('driverOnboarding').style.display = 'none';
  }
});

function confirmDriverSwitch(){
  const vehicle = document.getElementById('vehicleType').value;
  const license = document.getElementById('licenseNum').value;
  fetch('api.php?action=switch_role', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({role:'driver', vehicle_type:vehicle, license_number:license})
  }).then(r=>r.json()).then(d=>{
    if(d.success){ alert('You are now a driver!'); loadMyProfile(); document.getElementById('driverOnboarding').style.display='none'; }
  });
}
</script>

</body>

</html>