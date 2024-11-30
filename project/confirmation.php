<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

// Retrieve user details
$user_query = "SELECT name FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Retrieve the latest booking_id for the given schedule and user
$latest_booking_query = "SELECT MAX(booking_id) AS latest_booking_id FROM Bookings WHERE schedule_id = ? AND user_id = ?";
$latest_booking_stmt = $conn->prepare($latest_booking_query);
$latest_booking_stmt->bind_param("ii", $schedule_id, $user_id);
$latest_booking_stmt->execute();
$latest_booking_result = $latest_booking_stmt->get_result();
$latest_booking = $latest_booking_result->fetch_assoc();
$latest_booking_id = $latest_booking['latest_booking_id'];

// Retrieve schedule and booking details
$schedule_query = "SELECT Routes.origin, Routes.destination, Schedules.price, Schedules.departure_time, Schedules.date, TransportTypes.type_name
                   FROM Schedules 
                   JOIN Routes ON Schedules.route_id = Routes.route_id 
                   JOIN TransportTypes ON Schedules.type_id = TransportTypes.type_id
                   WHERE Schedules.schedule_id = ?";
$schedule_stmt = $conn->prepare($schedule_query);
$schedule_stmt->bind_param("i", $schedule_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
$schedule = $schedule_result->fetch_assoc();

$transport_type = ucfirst($schedule['type_name']);
$icon = ($transport_type == 'Flight') ? '✈️' : (($transport_type == 'Train') ? '🚆' : '🚌');

// Retrieve ticket details for the latest booking
$booking_query = "SELECT Seats.seat_number, Tickets.ticket_holder_name, Tickets.baggage, Tickets.total_price 
                  FROM Tickets 
                  JOIN Seats ON Tickets.seat_id = Seats.seat_id 
                  WHERE Tickets.booking_id = ?";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $latest_booking_id);
$booking_stmt->execute();
$seats_result = $booking_stmt->get_result();

// Calculate the total price based on the number of seats booked
$total_price = 0;
$seats = [];
while ($seat = $seats_result->fetch_assoc()) {
    $total_price += $seat['total_price'];
    $seats[] = $seat;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Pixelify Sans, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Header with icons */
        .header {
            width: 100%;
            padding: 10px;
            background: #000;
            color: #fff;
            font-size: 1.5em;
            text-align: center;
            position: fixed;
            top: 0;
            font-family: 'Noto Sans Devanagari', sans-serif;
        }

        .header-icons {
            position: absolute;
            left: 20px;
            top: 10px;
            display: flex;
            gap: 10px;
        }

        .header-icons img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        /* Payment animation and confirmation card styling */
        #animation-container, #confirmation-card {
            display: none;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.9);
        }

        /* Video styling */
        #payment-animation {
            width: 60%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.3);
        }

        /* Ticket Container Styling */
        .confirmation-card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            background-color: #000;
            padding-top: 50px;
        }

        .ticket-container {
            background-color: #333;
            padding: 30px;
            width: 400px;
            border-radius: 10px;
            color: #fff;
            text-align: center;
            position: relative;
            margin-top: 50px;
        }

        .ticket-header {
            font-size: 1.5em;
            color: #4caf50;
            margin-bottom: 20px;
        }

        .ticket-details {
            font-size: 1.2em;
            margin: 20px 0;
        }

        .route-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .route-info h3 {
            font-size: 1.3em;
        }

        .passenger-info {
            font-size: 1em;
            border-top: 1px solid #444;
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Header with Navigation Icons -->
<div class="header">
    <div class="header-icons">
        <a href="home.php"><img src="home_icon.png" alt="Home"></a>
        <a href="booking.php?type=train"><img src="train_icon.png" alt="Train"></a>
        <a href="booking.php?type=bus"><img src="bus_icon.png" alt="Bus"></a>
        <a href="booking.php?type=flight"><img src="flight_icon.png" alt="Flight"></a>
    </div>
    सफ़र - Booking Confirmation
</div>

<!-- Payment Animation Container -->
<div id="animation-container">
    <video id="payment-animation" autoplay>
        <source src="payment.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>

<!-- Booking Confirmation Ticket Container -->
<div class="confirmation-card-container" id="confirmation-card">
    <div class="ticket-container">
        <div class="ticket-header">Booking Confirmation</div>
        <p>Booked by: <?php echo htmlspecialchars($user['name']); ?></p>
        <p><?php echo ucfirst($transport_type); ?> from <?php echo htmlspecialchars($schedule['origin']); ?> to <?php echo htmlspecialchars($schedule['destination']); ?></p>
        <p>Date: <?php echo date("F j, Y", strtotime($schedule['date'])); ?> | Departure: <?php echo date("g:i A", strtotime($schedule['departure_time'])); ?></p>

        <!-- Route Info with Transport Icon -->
        <div class="route-info">
            <h3><?php echo strtoupper($schedule['origin']); ?></h3>
            <span><?php echo $icon; ?></span>
            <h3><?php echo strtoupper($schedule['destination']); ?></h3>
        </div>

        <!-- Passenger Information -->
        <?php foreach ($seats as $seat) { ?>
            <div class="passenger-info">
                <p>Passenger: <?php echo htmlspecialchars($seat['ticket_holder_name']); ?></p>
                <p>Seat: <?php echo htmlspecialchars($seat['seat_number']); ?></p>
                <p>Baggage: <?php echo ($seat['baggage'] == 'Yes' ? '20kg' : 'None'); ?></p>
            </div>
        <?php } ?>

        <p>Total Price: ₹<?php echo number_format($total_price, 2); ?></p>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Show the animation first
        const animationContainer = document.getElementById('animation-container');
        const confirmationCard = document.getElementById('confirmation-card');
        
        animationContainer.style.display = 'flex';
        
        // Hide the animation and show the confirmation after 2 seconds
        setTimeout(() => {
            animationContainer.style.display = 'none';
            confirmationCard.style.display = 'flex';
        }, 2000);  // 2-second delay for the animation
    });
</script>

</body>
</html>
