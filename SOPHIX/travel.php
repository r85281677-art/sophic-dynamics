<?php
require_once 'config.php';
require_once 'api.php';
session_start();
// Change this path only if your existing config.php is in a different folder.
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel | Move Smarter</title>
<link rel="stylesheet" href="travel.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">Move Smarter <span>Send. Travel. Explore.</span></a>
  <nav><a href="index.php">Home</a><a href="travel.php" class="active">Travel</a><a href="travel.php#bookings">My Bookings</a><a href="travel.php#reminders">Reminders</a></nav>
</header>
<main class="container">
  <section class="hero">
    <div><p class="eyebrow">YOUR JOURNEY STARTS HERE</p><h1>Plan your next journey.</h1><p>Search services, compare listed options, and keep your travel plans together.</p></div>
  </section>

  <section class="panel">
    <h2>What do you need today?</h2>
    <div class="service-grid" id="serviceGrid">
      <button class="service-card selected" data-type="flight"><span>✈️</span><b>Flights</b><small>Search listed flights</small></button>
      <button class="service-card" data-type="hotel"><span>🏨</span><b>Hotels</b><small>Find a room</small></button>
      <button class="service-card" data-type="ride"><span>🚗</span><b>Book a ride</b><small>Local and airport rides</small></button>
      <button class="service-card" data-type="bus"><span>🚌</span><b>Bus tickets</b><small>Intercity transport</small></button>
      <button class="service-card" data-type="train"><span>🚆</span><b>Train tickets</b><small>Where available</small></button>
      <button class="service-card" data-type="car_rental"><span>🚘</span><b>Car rental</b><small>Rent a vehicle</small></button>
      <button class="service-card" data-type="mover"><span>📦</span><b>Hire movers</b><small>Move home or office</small></button>
      <button class="service-card" data-type="airport_transfer"><span>🛬</span><b>Airport transfer</b><small>To or from the airport</small></button>
      <button class="service-card" data-type="package"><span>🧳</span><b>Travel packages</b><small>Combined offers</small></button>
      <button class="service-card" data-type="activity"><span>🎟️</span><b>Activities</b><small>Things to do</small></button>
    </div>

    <form id="searchForm" class="search-form">
      <label>From / pickup<input name="origin" id="origin" placeholder="e.g. Abuja"></label>
      <label>To / destination<input name="destination" id="destination" placeholder="e.g. Lagos"></label>
      <label>Location (mainly hotels)<input name="location" id="location" placeholder="City or area"></label>
      <div class="form-group">
    <label for="start_date">Start / Departure Date</label>

    <input
        type="date"
        id="start_date"
        name="start_date"
        required
    >

    <small id="start_date_error" class="date-error"></small>
</div>

<div class="form-group">
    <label for="end_date">End / Return Date</label>

    <input
        type="date"
        id="end_date"
        name="end_date"
        required
    >

    <small id="end_date_error" class="date-error"></small>
</div>

<div id="date_notification" role="alert" aria-live="polite"></div>

      <label>Maximum price (NGN)<input type="number" name="max_price" id="maxPrice" min="0" step="0.01" placeholder="No limit"></label>
      <div class="form-actions"><button class="primary" type="submit">Search availability</button><button class="secondary" type="button" id="saveSearch">Save this search</button></div>
    </form>
    <p class="notice">Availability is based on listings entered by your providers. External flights and hotel inventory require a real provider/API connection. Sample listings, if installed, are clearly marked as demos.</p>
  </section>

  <section class="panel">
    <div class="section-heading"><div><p class="eyebrow">MATCHING OPTIONS</p><h2>Search results</h2></div><span id="resultCount">0 results</span></div>
    <div id="message" class="message" role="status">Choose a service and search to see available listings.</div>
    <div id="results" class="results"></div>
  </section>

  <section class="panel" id="bookings">
    <div class="section-heading"><div><p class="eyebrow">YOUR TRIPS</p><h2>My travel bookings</h2></div><button class="secondary" id="loadBookings">Refresh</button></div>
    <div id="bookingResults" class="results"><p class="muted">Sign in and refresh to view your bookings.</p></div>
  </section>

  <section class="panel" id="reminders">
    <p class="eyebrow">DON'T FORGET</p><h2>Travel reminders</h2>
    <form id="reminderForm" class="search-form reminder-form">
      <label>Reminder title<input name="title" required placeholder="e.g. Leave for the airport"></label>
      <label>Date and time<input type="datetime-local" name="reminder_at" required></label>
      <label class="wide">Notes (optional)<input name="notes" placeholder="Booking reference, address, or other details"></label>
      <div class="form-actions"><button class="primary" type="submit">Save reminder</button><button class="secondary" type="button" id="loadReminders">Show my reminders</button></div>
    </form>
    <div id="reminderResults" class="results"></div>
  </section>
</main>

<dialog id="bookingDialog">
  <form id="bookingForm" class="dialog-card">
    <button type="button" class="dialog-close" id="closeDialog" aria-label="Close">×</button>
    <p class="eyebrow">BOOK A SERVICE</p><h2 id="bookingTitle">Booking details</h2>
    <input type="hidden" name="service_id" id="bookingServiceId">
    <label>Quantity / passengers<input type="number" name="quantity" min="1" max="20" value="1" required></label>
    <label>Passenger / contact name<input name="passenger_name" maxlength="150"></label>
    <label>Email<input type="email" name="passenger_email" maxlength="190"></label>
    <label>Phone<input name="passenger_phone" maxlength="40"></label>
    <label>Travel date<input type="date" name="travel_date"></label>
    <label>Hotel check-in<input type="date" name="check_in_date"></label>
    <label>Hotel check-out<input type="date" name="check_out_date"></label>
    <label>Notes<textarea name="notes" rows="3"></textarea></label>
    <button class="primary full" type="submit">Request booking</button>
    <p class="muted small">Booking requests start as pending. This page does not collect payment.</p>
  </form>
</dialog>
<script src="travel.js"></script>
</body>
</html>
