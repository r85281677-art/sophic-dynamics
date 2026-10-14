<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', error_reporting(E_ALL));
//your code starts here
//$conn = mysqli_connect

require_once "config.php";

header("Content-Type: application/json");


function sendResponse(
    $success,
    $message = "",
    $data = []
) {

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit;
}


function requireLogin() {

    if (!isset($_SESSION["user_id"])) {

        sendResponse(
            false,
            "You must sign in first."
        );

    }

}


function currentUser($pdo) {

    requireLogin();

    $stmt = $pdo->prepare(
        "SELECT
            id,
            name,
            email,
            phone,
            role,
            notifications_enabled,
            created_at
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([
        $_SESSION["user_id"]
    ]);

    return $stmt->fetch();

}


function notifyUser(
    $pdo,
    $userId,
    $title,
    $message
) {

    $stmt = $pdo->prepare(
        "INSERT INTO notifications
        (user_id, title, message)
        VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $title,
        $message
    ]);

}


$action = $_GET["action"] ?? "";


$raw = file_get_contents(
    "php://input"
);

$data = json_decode(
    $raw,
    true
);

if (!is_array($data)) {

    $data = $_POST;

}


try {


/* =====================================================
   REGISTER
===================================================== */

if ($action === "register") {

    $name =
        trim($data["name"] ?? "");

    $email =
        strtolower(
            trim($data["email"] ?? "")
        );

    $phone =
        trim($data["phone"] ?? "");

    $password =
        $data["password"] ?? "";

    $role =
        $data["role"] ?? "customer";


    if (
        !$name ||
        !$email ||
        !$password
    ) {

        sendResponse(
            false,
            "Please complete all required fields."
        );

    }


    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        sendResponse(
            false,
            "Please enter a valid email address."
        );

    }


    if (strlen($password) < 6) {

        sendResponse(
            false,
            "Password must contain at least 6 characters."
        );

    }


    if (
        !in_array(
            $role,
            ["customer", "driver"]
        )
    ) {

        $role = "customer";

    }


    $check = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE email = ?"
    );

    $check->execute([
        $email
    ]);


    if ($check->fetch()) {

        sendResponse(
            false,
            "An account with this email already exists."
        );

    }


    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    $stmt = $pdo->prepare(
        "INSERT INTO users
        (
            name,
            email,
            phone,
            password_hash,
            role
        )
        VALUES (?, ?, ?, ?, ?)"
    );


    $stmt->execute([
        $name,
        $email,
        $phone,
        $passwordHash,
        $role
    ]);


    $userId =
        $pdo->lastInsertId();


    $_SESSION["user_id"] =
        $userId;


    notifyUser(
        $pdo,
        $userId,
        "Welcome to Sophix Dynamics",
        "Your account has been created successfully."
    );


    sendResponse(
        true,
        "Account created successfully.",
        [
            "user" => currentUser($pdo)
        ]
    );

}


/* =====================================================
   LOGIN
===================================================== */

if ($action === "login") {

    $email =
        strtolower(
            trim($data["email"] ?? "")
        );

    $password =
        $data["password"] ?? "";


    $stmt = $pdo->prepare(
        "SELECT *
         FROM users
         WHERE email = ?"
    );

    $stmt->execute([
        $email
    ]);


    $user =
        $stmt->fetch();


    if (
        !$user ||
        !password_verify(
            $password,
            $user["password_hash"]
        )
    ) {

        sendResponse(
            false,
            "Incorrect email or password."
        );

    }


    $_SESSION["user_id"] =
        $user["id"];


    sendResponse(
        true,
        "Signed in successfully.",
        [
            "user" => currentUser($pdo)
        ]
    );

}


/* =====================================================
   LOGOUT
===================================================== */

if ($action === "logout") {

    $_SESSION = [];

    session_destroy();

    sendResponse(
        true,
        "You have been signed out."
    );

}


/* =====================================================
   CURRENT USER
===================================================== */

if ($action === "me") {

    sendResponse(
        true,
        "",
        [
            "user" =>
                currentUser($pdo)
        ]
    );

}


/* =====================================================
   UPDATE PROFILE
===================================================== */

if ($action === "update_profile") {

    $user =
        currentUser($pdo);


    $name =
        trim($data["name"] ?? "");

    $phone =
        trim($data["phone"] ?? "");


    if (!$name) {

        sendResponse(
            false,
            "Name cannot be empty."
        );

    }


    $stmt = $pdo->prepare(
        "UPDATE users
         SET name = ?, phone = ?
         WHERE id = ?"
    );


    $stmt->execute([
        $name,
        $phone,
        $user["id"]
    ]);


    sendResponse(
        true,
        "Profile updated successfully.",
        [
            "user" =>
                currentUser($pdo)
        ]
    );

}


/* =====================================================
   SETTINGS
===================================================== */

if ($action === "update_settings") {

    $user =
        currentUser($pdo);


    $enabled =
        !empty(
            $data["notifications_enabled"]
        ) ? 1 : 0;


    $stmt = $pdo->prepare(
        "UPDATE users
         SET notifications_enabled = ?
         WHERE id = ?"
    );


    $stmt->execute([
        $enabled,
        $user["id"]
    ]);


    sendResponse(
        true,
        "Settings updated."
    );

}


/* =====================================================
   NOTIFICATIONS
===================================================== */

if ($action === "notifications") {

    $user =
        currentUser($pdo);


    $stmt = $pdo->prepare(
        "SELECT *
         FROM notifications
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 50"
    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "",
        [
            "notifications" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   MARK NOTIFICATIONS READ
===================================================== */

if ($action === "read_notifications") {

    $user =
        currentUser($pdo);


    $stmt = $pdo->prepare(
        "UPDATE notifications
         SET is_read = 1
         WHERE user_id = ?"
    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "Notifications marked as read."
    );

}


/* =====================================================
   CREATE PACKAGE
===================================================== */

if ($action === "create_package") {

    $user =
        currentUser($pdo);


    $pickup =
        trim(
            $data["pickup_location"] ?? ""
        );

    $destination =
        trim(
            $data["destination"] ?? ""
        );

    $packageType =
        trim(
            $data["package_type"] ?? ""
        );

    $size =
        $data["package_size"] ?? "small";

    $weight =
        floatval(
            $data["weight_kg"] ?? 0
        );


    if (
        !$pickup ||
        !$destination ||
        !$packageType
    ) {

        sendResponse(
            false,
            "Please complete the package form."
        );

    }


    $trackingNumber =
        "SOP-" .
        date("Ymd") .
        "-" .
        strtoupper(
            bin2hex(
                random_bytes(3)
            )
        );


    $stmt = $pdo->prepare(
        "INSERT INTO packages
        (
            customer_id,
            pickup_location,
            destination,
            package_type,
            package_size,
            weight_kg,
            tracking_number
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );


    $stmt->execute([
        $user["id"],
        $pickup,
        $destination,
        $packageType,
        $size,
        $weight,
        $trackingNumber
    ]);


    $packageId =
        $pdo->lastInsertId();


    /*
       Look for an available vehicle.
    */

    $vehicleStmt =
        $pdo->query(
            "SELECT
                v.*,
                u.id AS driver_id,
                u.name AS driver_name
             FROM vehicles v
             JOIN users u
             ON u.id = v.driver_id
             WHERE v.status = 'available'
             ORDER BY v.id ASC
             LIMIT 1"
        );


    $vehicle =
        $vehicleStmt->fetch();


    if ($vehicle) {

        $bookingStmt =
            $pdo->prepare(
                "INSERT INTO bookings
                (
                    customer_id,
                    driver_id,
                    vehicle_id,
                    package_id,
                    service_type,
                    service_name,
                    pickup_location,
                    destination,
                    status
                )
                VALUES (?, ?, ?, ?, 'package',
                        'Package Delivery',
                        ?, ?, 'pending')"
            );


        $bookingStmt->execute([
            $user["id"],
            $vehicle["driver_id"],
            $vehicle["id"],
            $packageId,
            $pickup,
            $destination
        ]);


        notifyUser(
            $pdo,
            $vehicle["driver_id"],
            "New delivery request",
            "A new package delivery is available."
        );


        $message =
            "Your package has been found and a delivery option is available.";

    } else {

        $message =
            "Your package has been registered. No vehicle is currently available. You will be notified when one becomes available.";

        notifyUser(
            $pdo,
            $user["id"],
            "Searching for a vehicle",
            "No delivery vehicle is currently available. We will notify you when one becomes available."
        );

    }


    notifyUser(
        $pdo,
        $user["id"],
        "Package created",
        "Your tracking number is $trackingNumber."
    );


    sendResponse(
        true,
        $message,
        [
            "tracking_number" =>
                $trackingNumber
        ]
    );

}


/* =====================================================
   TRACK PACKAGE
===================================================== */

if ($action === "track") {

    $user =
        currentUser($pdo);


    $tracking =
        strtoupper(
            trim(
                $data["tracking_number"] ?? ""
            )
        );


    $stmt = $pdo->prepare(

        "SELECT

            p.*,

            b.status AS booking_status,

            v.model AS vehicle_model,

            v.plate_number,

            v.vehicle_type,

            d.name AS driver_name,

            d.phone AS driver_phone

        FROM packages p

        LEFT JOIN bookings b
        ON b.package_id = p.id

        LEFT JOIN vehicles v
        ON v.id = b.vehicle_id

        LEFT JOIN users d
        ON d.id = b.driver_id

        WHERE
            p.customer_id = ?
        AND
            p.tracking_number = ?

        ORDER BY b.id DESC

        LIMIT 1"

    );


    $stmt->execute([
        $user["id"],
        $tracking
    ]);


    $package =
        $stmt->fetch();


    if (!$package) {

        sendResponse(
            false,
            "We couldn't find a package with that tracking number."
        );

    }


    sendResponse(
        true,
        "Package found.",
        [
            "package" =>
                $package
        ]
    );

}


/* =====================================================
   AVAILABLE VEHICLES
===================================================== */

if ($action === "vehicles") {

    currentUser($pdo);


    $stmt = $pdo->query(

        "SELECT

            v.*,

            u.name AS driver_name

         FROM vehicles v

         JOIN users u
         ON u.id = v.driver_id

         WHERE v.status = 'available'

         ORDER BY v.id DESC"

    );


    sendResponse(
        true,
        "",
        [
            "vehicles" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   BOOK VEHICLE
===================================================== */

if ($action === "book_vehicle") {

    $user =
        currentUser($pdo);


    $vehicleType =
        $data["vehicle_type"] ?? "";

    $pickup =
        trim(
            $data["pickup_location"] ?? ""
        );

    $destination =
        trim(
            $data["destination"] ?? ""
        );


    if (
        !$vehicleType ||
        !$pickup ||
        !$destination
    ) {

        sendResponse(
            false,
            "Please complete all booking fields."
        );

    }


    $stmt = $pdo->prepare(

        "SELECT

            v.*,

            u.id AS driver_id,
            u.name AS driver_name

         FROM vehicles v

         JOIN users u
         ON u.id = v.driver_id

         WHERE
            v.vehicle_type = ?
         AND
            v.status = 'available'

         ORDER BY v.id ASC

         LIMIT 1"

    );


    $stmt->execute([
        $vehicleType
    ]);


    $vehicle =
        $stmt->fetch();


    if (!$vehicle) {

        notifyUser(
            $pdo,
            $user["id"],
            "No vehicle available",
            "No $vehicleType is currently available. We will notify you when one becomes available."
        );


        sendResponse(
            true,
            "No $vehicleType is currently available. We will notify you when one becomes available.",
            [
                "available" => false
            ]
        );

    }


    $stmt = $pdo->prepare(

        "INSERT INTO bookings
        (
            customer_id,
            driver_id,
            vehicle_id,
            service_type,
            service_name,
            pickup_location,
            destination,
            status
        )

        VALUES
        (
            ?, ?, ?, 'vehicle',
            ?, ?, ?, 'pending'
        )"

    );


    $stmt->execute([

        $user["id"],

        $vehicle["driver_id"],

        $vehicle["id"],

        ucfirst($vehicleType)
        . " booking",

        $pickup,

        $destination

    ]);


    $bookingId =
        $pdo->lastInsertId();


    notifyUser(

        $pdo,

        $vehicle["driver_id"],

        "New booking request",

        "A customer has requested your "
        . $vehicleType
        . " for a trip."

    );


    sendResponse(

        true,

        "Your booking request has been sent to a driver.",

        [
            "available" => true,

            "booking_id" =>
                $bookingId,

            "vehicle" =>
                $vehicle

        ]

    );

}


/* =====================================================
   REGISTER VEHICLE
===================================================== */

if ($action === "register_vehicle") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Only driver accounts can register vehicles."
        );

    }


    $type =
        $data["vehicle_type"] ?? "";

    $model =
        trim(
            $data["model"] ?? ""
        );

    $plate =
        strtoupper(
            trim(
                $data["plate_number"] ?? ""
            )
        );

    $capacity =
        floatval(
            $data["capacity_kg"] ?? 0
        );


    if (
        !$type ||
        !$model ||
        !$plate ||
        $capacity <= 0
    ) {

        sendResponse(
            false,
            "Please complete all vehicle fields."
        );

    }


    $check =
        $pdo->prepare(
            "SELECT id
             FROM vehicles
             WHERE plate_number = ?"
        );


    $check->execute([
        $plate
    ]);


    if ($check->fetch()) {

        sendResponse(
            false,
            "That plate number is already registered."
        );

    }


    $stmt = $pdo->prepare(

        "INSERT INTO vehicles
        (
            driver_id,
            vehicle_type,
            model,
            plate_number,
            capacity_kg
        )

        VALUES (?, ?, ?, ?, ?)"

    );


    $stmt->execute([

        $user["id"],
        $type,
        $model,
        $plate,
        $capacity

    ]);


    notifyUser(

        $pdo,

        $user["id"],

        "Vehicle registered",

        "$model has been successfully added to your account."

    );


    sendResponse(

        true,

        "Vehicle registered successfully."

    );

}


/* =====================================================
   DRIVER VEHICLES
===================================================== */

if ($action === "my_vehicles") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $stmt = $pdo->prepare(

        "SELECT *
         FROM vehicles
         WHERE driver_id = ?
         ORDER BY id DESC"

    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "",
        [
            "vehicles" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   CHANGE VEHICLE STATUS
===================================================== */

if ($action === "vehicle_status") {

    $user =
        currentUser($pdo);


    $vehicleId =
        intval(
            $data["vehicle_id"] ?? 0
        );

    $status =
        $data["status"] ?? "offline";


    if (
        !in_array(
            $status,
            [
                "available",
                "offline"
            ]
        )
    ) {

        sendResponse(
            false,
            "Invalid vehicle status."
        );

    }


    $stmt = $pdo->prepare(

        "UPDATE vehicles

         SET status = ?

         WHERE
            id = ?
         AND
            driver_id = ?"

    );


    $stmt->execute([

        $status,

        $vehicleId,

        $user["id"]

    ]);


    sendResponse(
        true,
        "Vehicle status updated."
    );

}


/* =====================================================
   DRIVER JOBS
===================================================== */

if ($action === "driver_jobs") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $stmt = $pdo->prepare(

        "SELECT

            b.*,

            p.tracking_number,

            v.model AS vehicle_model,

            v.plate_number,

            c.name AS customer_name,

            c.phone AS customer_phone

         FROM bookings b

         JOIN users c
         ON c.id = b.customer_id

         LEFT JOIN packages p
         ON p.id = b.package_id

         LEFT JOIN vehicles v
         ON v.id = b.vehicle_id

         WHERE b.driver_id = ?

         ORDER BY b.id DESC"

    );


    $stmt->execute([
        $user["id"]
    ]);

    sendResponse(
        true,
        "",
        [
            "jobs" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   ACCEPT DRIVER JOB
===================================================== */

if ($action === "accept_job") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $bookingId =
        intval(
            $data["booking_id"] ?? 0
        );


    $stmt = $pdo->prepare(
        "SELECT *
         FROM bookings
         WHERE id = ?
         AND driver_id = ?
         AND status = 'pending'"
    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    $booking =
        $stmt->fetch();


    if (!$booking) {

        sendResponse(
            false,
            "This booking is no longer available."
        );

    }


    $stmt = $pdo->prepare(
        "UPDATE bookings
         SET status = 'accepted'
         WHERE id = ?
         AND driver_id = ?"
    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    notifyUser(
        $pdo,
        $booking["customer_id"],
        "Booking accepted",
        "Your driver has accepted your booking request."
    );


    sendResponse(
        true,
        "Booking accepted successfully."
    );

}


/* =====================================================
   REJECT DRIVER JOB
===================================================== */

if ($action === "reject_job") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $bookingId =
        intval(
            $data["booking_id"] ?? 0
        );


    $stmt = $pdo->prepare(
        "SELECT *
         FROM bookings
         WHERE id = ?
         AND driver_id = ?
         AND status = 'pending'"
    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    $booking =
        $stmt->fetch();


    if (!$booking) {

        sendResponse(
            false,
            "This booking is no longer available."
        );

    }


    $stmt = $pdo->prepare(
        "UPDATE bookings
         SET status = 'rejected'
         WHERE id = ?
         AND driver_id = ?"
    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    notifyUser(
        $pdo,
        $booking["customer_id"],
        "Booking declined",
        "The driver was unable to accept your booking. We will look for another available option."
    );


    sendResponse(
        true,
        "Booking rejected."
    );

}


/* =====================================================
   CUSTOMER BOOKINGS
===================================================== */

if ($action === "bookings") {

    $user =
        currentUser($pdo);


    $stmt = $pdo->prepare(

        "SELECT

            b.*,

            p.tracking_number,

            v.model AS vehicle_model,

            v.plate_number,

            v.vehicle_type,

            d.name AS driver_name,

            d.phone AS driver_phone

         FROM bookings b

         LEFT JOIN packages p
         ON p.id = b.package_id

         LEFT JOIN vehicles v
         ON v.id = b.vehicle_id

         LEFT JOIN users d
         ON d.id = b.driver_id

         WHERE b.customer_id = ?

         ORDER BY b.id DESC"

    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "",
        [
            "bookings" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   CANCEL BOOKING
===================================================== */

if ($action === "cancel_booking") {

    $user =
        currentUser($pdo);


    $bookingId =
        intval(
            $data["booking_id"] ?? 0
        );


    $stmt = $pdo->prepare(

        "SELECT *
         FROM bookings
         WHERE id = ?
         AND customer_id = ?"

    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    $booking =
        $stmt->fetch();


    if (!$booking) {

        sendResponse(
            false,
            "Booking not found."
        );

    }


    if (
        in_array(
            $booking["status"],
            ["completed", "cancelled"]
        )
    ) {

        sendResponse(
            false,
            "This booking cannot be cancelled."
        );

    }


    $stmt = $pdo->prepare(

        "UPDATE bookings
         SET status = 'cancelled'
         WHERE id = ?
         AND customer_id = ?"

    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    if (!empty($booking["driver_id"])) {

        notifyUser(
            $pdo,
            $booking["driver_id"],
            "Booking cancelled",
            "A customer has cancelled their booking."
        );

    }


    sendResponse(
        true,
        "Booking cancelled successfully."
    );

}


/* =====================================================
   COMPLETE BOOKING
===================================================== */

if ($action === "complete_booking") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $bookingId =
        intval(
            $data["booking_id"] ?? 0
        );


    $stmt = $pdo->prepare(

        "SELECT *
         FROM bookings
         WHERE id = ?
         AND driver_id = ?"

    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    $booking =
        $stmt->fetch();


    if (!$booking) {

        sendResponse(
            false,
            "Booking not found."
        );

    }


    $stmt = $pdo->prepare(

        "UPDATE bookings
         SET status = 'completed'
         WHERE id = ?
         AND driver_id = ?"

    );


    $stmt->execute([
        $bookingId,
        $user["id"]
    ]);


    if (!empty($booking["vehicle_id"])) {

        $vehicleStmt = $pdo->prepare(

            "UPDATE vehicles
             SET status = 'available'
             WHERE id = ?
             AND driver_id = ?"

        );


        $vehicleStmt->execute([
            $booking["vehicle_id"],
            $user["id"]
        ]);

    }


    notifyUser(
        $pdo,
        $booking["customer_id"],
        "Booking completed",
        "Your delivery or trip has been completed successfully."
    );


    sendResponse(
        true,
        "Booking marked as completed."
    );

}


/* =====================================================
   RECENT ACTIVITY
===================================================== */

if ($action === "activity") {

    $user =
        currentUser($pdo);


    $stmt = $pdo->prepare(

        "SELECT

            b.id,

            b.service_type,

            b.service_name,

            b.pickup_location,

            b.destination,

            b.status,

            b.created_at,

            p.tracking_number

         FROM bookings b

         LEFT JOIN packages p
         ON p.id = b.package_id

         WHERE b.customer_id = ?

         ORDER BY b.id DESC

         LIMIT 20"

    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "",
        [
            "activities" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   DRIVER NOTIFICATIONS
===================================================== */

if ($action === "driver_notifications") {

    $user =
        currentUser($pdo);


    if ($user["role"] !== "driver") {

        sendResponse(
            false,
            "Driver access required."
        );

    }


    $stmt = $pdo->prepare(

        "SELECT *
         FROM notifications
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 50"

    );


    $stmt->execute([
        $user["id"]
    ]);


    sendResponse(
        true,
        "",
        [
            "notifications" =>
                $stmt->fetchAll()
        ]
    );

}


/* =====================================================
   UNKNOWN ACTION
===================================================== */

if ($action === "me") {
    if (!isset($_SESSION["user_id"])) sendResponse(false, "Not logged in");
    $userId = $_SESSION["user_id"];
    $stmt = $pdo->prepare("SELECT id, name, email, phone, is_customer, is_driver, active_role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) sendResponse(false, "User not found");
    try {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $orderCount = $countStmt->fetchColumn();
    } catch (Exception $e) { $orderCount = 0; }
    $isNew = (time() - strtotime($user['created_at'])) < (7*24*60*60);
    $user['order_count'] = $orderCount;
    $user['is_new'] = $isNew;
    sendResponse(true, "OK", ["user" => $user]);
}

if ($action === "switch_role") {
    if (!isset($_SESSION["user_id"])) sendResponse(false, "Not logged in");
    
    $newRole = $data["role"] ?? ""; // 'customer' or 'driver'
    if (!in_array($newRole, ["customer", "driver"])) {
        sendResponse(false, "Invalid role");
    }

    $userId = $_SESSION["user_id"];
    
    // if switching to driver for first time, make them a driver
    if ($newRole === "driver") {
        $pdo->prepare("UPDATE users SET is_driver = 1, active_role = 'driver' WHERE id = ?")->execute([$userId]);
        
        // if they sent vehicle info first time
        if (!empty($data["vehicle_type"])) {
            $pdo->prepare("INSERT INTO driver_profiles (user_id, vehicle_type, license_number) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vehicle_type = VALUES(vehicle_type), license_number = VALUES(license_number)")
                ->execute([$userId, $data["vehicle_type"], $data["license_number"] ?? ""]);
        }
    } else {
        $pdo->prepare("UPDATE users SET active_role = 'customer' WHERE id = ?")->execute([$userId]);
    }

    $_SESSION["active_role"] = $newRole;
    sendResponse(true, "Switched to $newRole", ["active_role" => $newRole]);
}

if ($action == "travel_services") {
    // get travel data from DB
    $stmt = $pdo->prepare("SELECT * FROM travel_services ORDER BY id DESC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, "", ["services" => $services]);
}

if ($action == "travel_book") {
    // example for booking travel
    $data = json_decode(file_get_contents("php://input"), true);
    // your booking logic here
    sendResponse(true, "Travel booked successfully.");
}


} catch (PDOException $e) {

    sendResponse(
        false,
        "A database error occurred."
    );

} catch (Exception $e) {

    sendResponse(
        false,
        "Something went wrong."
    );

}

?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/*
 * Travel API.
 * Assumes your existing config.php creates a PDO connection named $pdo.
 * Assumes successful login stores the user's numeric ID in $_SESSION['user_id'].
 * If your project uses a different session key, change currentUserId() below.
 */

require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

function respond($success, $message = '', $data = [], $status = 200) {
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}
function currentUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}
function inputData() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    return is_array($json) ? $json : $_POST;
}
function cleanText($value, $max = 150) {
    return mb_substr(trim((string)$value), 0, $max);
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        respond(false, 'Database connection not found. Check config.php and make sure it defines $pdo.', [], 500);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? inputData()['action'] ?? 'search';
    $input = inputData();

    if ($action === 'search') {
        $type = cleanText($_GET['type'] ?? $input['type'] ?? '', 40);
        $allowed = ['flight','hotel','ride','bus','train','car_rental','mover','airport_transfer','package','activity'];
        if (!in_array($type, $allowed, true)) respond(false, 'Choose a valid service type.', [], 400);

        $origin = cleanText($_GET['origin'] ?? $input['origin'] ?? '');
        $destination = cleanText($_GET['destination'] ?? $input['destination'] ?? '');
        $location = cleanText($_GET['location'] ?? $input['location'] ?? '');
        $startDate = cleanText($_GET['start_date'] ?? $input['start_date'] ?? '', 10);
        $endDate = cleanText($_GET['end_date'] ?? $input['end_date'] ?? '', 10);
        $maxPrice = $_GET['max_price'] ?? $input['max_price'] ?? '';
        $sql = "SELECT id, service_type, provider_name, title, description, origin, destination, location,
                       departure_datetime, arrival_datetime, check_in_date, check_out_date,
                       price, currency, capacity, available_quantity, image_url
                FROM travel_service_options_options
                WHERE service_type = :type AND status = 'available' AND available_quantity > 0";
        $params = [':type' => $type];

        if ($origin !== '') { $sql .= " AND (origin LIKE :origin OR location LIKE :origin)"; $params[':origin'] = "%$origin%"; }
        if ($destination !== '') { $sql .= " AND (destination LIKE :destination OR location LIKE :destination)"; $params[':destination'] = "%$destination%"; }
        if ($location !== '') { $sql .= " AND location LIKE :location"; $params[':location'] = "%$location%"; }
        if ($startDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            if ($type === 'hotel') { $sql .= " AND (check_in_date IS NULL OR check_in_date <= :start_date)"; }
            else { $sql .= " AND (departure_datetime IS NULL OR DATE(departure_datetime) = :start_date OR departure_datetime >= :start_dt)"; }
            $params[':start_date'] = $startDate;
            if ($type !== 'hotel') $params[':start_dt'] = $startDate . ' 00:00:00';
        }
        if ($endDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) && $type === 'hotel') {
            $sql .= " AND (check_out_date IS NULL OR check_out_date >= :end_date)";
            $params[':end_date'] = $endDate;
        }
        if ($maxPrice !== '' && is_numeric($maxPrice) && (float)$maxPrice >= 0) {
            $sql .= " AND price <= :max_price"; $params[':max_price'] = (float)$maxPrice;
        }
        $sql .= " ORDER BY price ASC LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        respond(true, 'Search complete.', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($action === 'book') {
        $userId = currentUserId();
        if (!$userId) respond(false, 'Please sign in before making a booking.', [], 401);
        $serviceId = (int)($input['service_id'] ?? 0);
        $quantity = max(1, min(20, (int)($input['quantity'] ?? 1)));
        $name = cleanText($input['passenger_name'] ?? '', 150);
        $email = cleanText($input['passenger_email'] ?? '', 190);
        $phone = cleanText($input['passenger_phone'] ?? '', 40);
        $notes = cleanText($input['notes'] ?? '', 1000);
        $travelDate = cleanText($input['travel_date'] ?? '', 10);
        $checkIn = cleanText($input['check_in_date'] ?? '', 10);
        $checkOut = cleanText($input['check_out_date'] ?? '', 10);

        if ($serviceId < 1) respond(false, 'Select a valid service.', [], 400);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Enter a valid email address.', [], 400);

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM travel_service_options_options WHERE id = ? AND status = 'available' FOR UPDATE");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$service || (int)$service['available_quantity'] < $quantity) {
            $pdo->rollBack();
            respond(false, 'This service is no longer available in the requested quantity. Search again.', [], 409);
        }
        $reference = 'TRV' . strtoupper(bin2hex(random_bytes(5)));
        $total = (float)$service['price'] * $quantity;
        $stmt = $pdo->prepare("INSERT INTO travel_bookings
            (reference_code,user_id,service_id,service_type,quantity,travel_date,check_in_date,check_out_date,
             passenger_name,passenger_email,passenger_phone,notes,total_price,currency,status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
        $stmt->execute([$reference,$userId,$serviceId,$service['service_type'],$quantity,
            $travelDate ?: null,$checkIn ?: null,$checkOut ?: null,$name ?: null,$email ?: null,
            $phone ?: null,$notes ?: null,$total,$service['currency']]);
        $update = $pdo->prepare("UPDATE travel_service_options_options SET available_quantity = available_quantity - ? WHERE id = ?");
        $update->execute([$quantity, $serviceId]);
        $pdo->commit();
        respond(true, 'Booking request created. It is pending provider confirmation.', [
            'reference_code' => $reference, 'status' => 'pending', 'total_price' => $total,
            'currency' => $service['currency']
        ], 201);
    }

    if ($action === 'my_bookings') {
        $userId = currentUserId();
        if (!$userId) respond(false, 'Please sign in to view your bookings.', [], 401);
        $stmt = $pdo->prepare("SELECT b.id,b.reference_code,b.service_type,b.quantity,b.travel_date,b.check_in_date,
            b.check_out_date,b.total_price,b.currency,b.status,b.created_at,s.title,s.provider_name,s.origin,s.destination,s.location
            FROM travel_bookings b JOIN travel_service_options_options s ON s.id=b.service_id
            WHERE b.user_id=? ORDER BY b.created_at DESC");
        $stmt->execute([$userId]);
        respond(true, 'Your travel bookings.', $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($action === 'save_search') {
        $userId = currentUserId();
        if (!$userId) respond(false, 'Please sign in to save searches.', [], 401);
        $type = cleanText($input['type'] ?? '', 40);
        $allowed = ['flight','hotel','ride','bus','train','car_rental','mover','airport_transfer','package','activity'];
        if (!in_array($type, $allowed, true)) respond(false, 'Choose a valid service type.', [], 400);
        $stmt = $pdo->prepare("INSERT INTO travel_saved_searches (user_id,service_type,origin,destination,location,start_date,end_date,max_price)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$userId,$type,cleanText($input['origin'] ?? '') ?: null,
            cleanText($input['destination'] ?? '') ?: null,cleanText($input['location'] ?? '') ?: null,
            ($input['start_date'] ?? '') ?: null,($input['end_date'] ?? '') ?: null,
            (isset($input['max_price']) && $input['max_price'] !== '' && is_numeric($input['max_price'])) ? (float)$input['max_price'] : null]);
        respond(true, 'Search saved. This stores the criteria; automatic availability alerts need a scheduled checker/provider integration.');
    }

    if ($action === 'reminders') {
        $userId = currentUserId();
        if (!$userId) respond(false, 'Please sign in to manage reminders.', [], 401);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->prepare("SELECT id,title,reminder_at,notes,status FROM travel_reminders WHERE user_id=? ORDER BY reminder_at ASC");
            $stmt->execute([$userId]);
            respond(true, 'Your reminders.', $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        $title = cleanText($input['title'] ?? '', 180);
        $at = cleanText($input['reminder_at'] ?? '', 19);
        $notes = cleanText($input['notes'] ?? '', 1000);
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $at) ?: DateTime::createFromFormat('Y-m-d H:i:s', $at);
        if ($title === '' || !$date) respond(false, 'Enter a reminder title and a valid date/time.', [], 400);
        $stmt = $pdo->prepare("INSERT INTO travel_reminders (user_id,title,reminder_at,notes) VALUES (?,?,?,?)");
        $stmt->execute([$userId,$title,$date->format('Y-m-d H:i:s'),$notes ?: null]);
        respond(true, 'Reminder saved. In-app listing works; browser/email notifications require a scheduled task or notification setup.', ['id' => $pdo->lastInsertId()], 201);
    }

    if ($action === 'cancel_booking') {
        $userId = currentUserId();
        if (!$userId) respond(false, 'Please sign in first.', [], 401);
        $bookingId = (int)($input['booking_id'] ?? 0);
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id,service_id,quantity,status FROM travel_bookings WHERE id=? AND user_id=? FOR UPDATE");
        $stmt->execute([$bookingId,$userId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking || $booking['status'] !== 'pending') {
            $pdo->rollBack();
            respond(false, 'Only your pending bookings can be cancelled here. Contact the provider for confirmed bookings.', [], 409);
        }
        $pdo->prepare("UPDATE travel_bookings SET status='cancelled' WHERE id=?")->execute([$bookingId]);
        $pdo->prepare("UPDATE travel_service_options_options SET available_quantity=available_quantity+? WHERE id=?")->execute([(int)$booking['quantity'],(int)$booking['service_id']]);
        $pdo->commit();
        respond(true, 'Booking cancelled.');
    }

    respond(false, 'Unknown action.', [], 400);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Travel API error: ' . $e->getMessage());
    respond(false, 'A server error occurred. Check your PHP error log and database tables.', [], 500);
}
