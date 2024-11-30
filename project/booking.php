<?php
// Include database connection
include 'db_connect.php';

// Get transport type from the URL (train, bus, or flight)
$transport_type = isset($_GET['type']) ? $_GET['type'] : 'train';

// Shorten city names for flights
$city_codes = [
    'Delhi' => 'DEL',
    'Mumbai' => 'BOM',
    'Bangalore' => 'BLR',
    'Chennai' => 'MAA',
    'Kolkata' => 'CCU',
    'Hyderabad' => 'HYD',
    'Pune'=>'PUN',
    'Jaipur'=>'JPR',
    'Nagpur'=>'NPR',
    'Lucknow'=>'LKW',
    'Chandigarh'=>'CDG',
    'Amritsar'=>'AMT',
    'Goa'=>'GOA'
];

// Icon for each transport type
$icons = [
    'train' => 'train_icon.png', // Replace with actual icon path
    'bus' => 'bus_icon.png', // Replace with actual icon path
    'flight' => 'flight_icon.png' // Replace with actual icon path
];

// Define seat selection pages for each transport type
$seat_selection_pages = [
    'train' => 'seat_selection_train.php',
    'bus' => 'seat_selection_bus.php',
    'flight' => 'seat_selection_flight.php'
];

// Fetch routes for the selected transport type
$query = "SELECT Routes.origin, Routes.destination, Schedules.departure_time, 
                 Schedules.arrival_time, Schedules.price, Schedules.schedule_id, Schedules.date
          FROM Routes 
          JOIN Schedules ON Routes.route_id = Schedules.route_id
          JOIN TransportTypes ON Schedules.type_id = TransportTypes.type_id
          WHERE TransportTypes.type_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $transport_type);
$stmt->execute();
$result = $stmt->get_result();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yaatra Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* General styling */
        body {
            font-family: Pixelify Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #000; /* Black background */
            color: #fff; /* White text */
        }

        /* Header banner */
        .header {
            text-align: center;
            font-size: 2.5em;
            padding: 20px 0;
            font-family: 'Noto Sans Devanagari', sans-serif;
            background: #000; /* Black header background */
            color: #fff;
            margin-bottom: 20px;
        }

        /* Navigation icons on top left */
        .nav-icons {
            position: absolute;
            top: 15px;
            left: 20px;
            display: flex;
            gap: 15px;
        }

        .nav-icons a {
            text-decoration: none;
            color: #fff;
            font-size: 1.2em;
        }

        /* GIF container */
        .gif-container {
            width: 100%;
            height: 400px; /* Adjust height as needed */
            background-color: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Ensures the GIF fills the container */
        }

        .gif-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the GIF covers the container fully */
        }

        /* Main container */
        .container {
            display: flex;
            padding: 20px;
        }

        /* Sidebar filters */
        .sidebar {
            width: 250px;
            background: #000; /* Black background */
            padding: 20px;
            border-radius: 8px;
            margin-right: 20px;
            color: #fff;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }

        .filter-group input[type="text"],
        .filter-group input[type="date"] {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #444;
            background: #2a2a2a;
            color: #f0f0f0;
        }

        .apply-filters {
            display: inline-block;
            width: 100%;
            padding: 10px;
            background: #444; /* Grey button */
            color: white;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 10px;
        }

        /* Routes display */
        .routes {
            flex: 1;
            background: #000; /* Black background */
            padding: 20px;
            border-radius: 8px;
        }

        /* Route item container */
        .route {
            background: #333; /* Grey container */
            border-radius: 10px;
            padding: 25px 35px;
            margin-bottom: 30px; /* Increased space between containers */
            position: relative;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .route:hover {
            background: #444;
        }

        /* Dotted line and circles */
        .dotted-line {
            display: flex;
            align-items: center;
            flex: 1;
            margin: 0 20px; /* Adjusted margin for more space */
            position: relative;
        }

        .dotted-line::before,
        .dotted-line::after {
            content: "";
            width: 12px;
            height: 12px;
            background-color: #fff;
            border-radius: 50%;
            margin: 0 10px;
        }

        .dotted-line span {
            flex: 1;
            border-top: 2px dotted #fff;
            position: relative;
        }

        .route-icon {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            height: 24px;
            background: #333;
            padding: 5px;
            border-radius: 50%;
        }

        /* Route details */
        .route h4 {
            font-size: 1.5em; /* Large font for origin and destination */
            margin: 0;
            text-align: center;
        }

        .route-time {
            font-size: 0.9em;
            color: #ccc;
            text-align: center;
            margin-top: 5px;
        }

        .route-date {
            font-size: 0.9em;
            color: #ccc;
            text-align: center;
            margin-top: 10px;
        }

        /* Price styling */
        .route-price {
            font-size: 1em;
            font-weight: bold;
            color: #fff;
            position: absolute;
            top: 10px;
            right: 30px;
        }
    </style>
</head>
<body>

<!-- Header Banner -->
<div class="header">सफ़र</div>

<!-- GIF Container -->
<div class="gif-container">
    <img src="<?php echo $transport_type; ?>.gif" alt="<?php echo ucfirst($transport_type); ?> Animation">
</div>

<!-- Navigation Icons -->
<div class="nav-icons">
    <a href="home.php">🏠</a>
    <?php if ($transport_type !== 'train') { ?><a href="booking.php?type=train">🚆</a><?php } ?>
    <?php if ($transport_type !== 'bus') { ?><a href="booking.php?type=bus">🚌</a><?php } ?>
    <?php if ($transport_type !== 'flight') { ?><a href="booking.php?type=flight">✈️</a><?php } ?>
</div>

<div class="container">
    <!-- Sidebar for filters -->
    <div class="sidebar">
        <h3>Filter</h3>
        <div class="filter-group">
            <label for="price-range">Price Range</label>
            <input type="text" id="price-range" placeholder="Start Price">
            <input type="text" id="price-range" placeholder="Up to">
        </div>
        
        <div class="filter-group">
            <label for="date">Select Date</label>
            <input type="date" id="date" name="date">
        </div>
        
        <a href="#" class="apply-filters">Apply Filters</a>
    </div>

    <!-- Routes display -->
    <div class="routes">
        <h3><?php echo ucfirst($transport_type); ?> Routes</h3>

        <?php
        // Display each route from the database
        while ($row = $result->fetch_assoc()) {
            // Determine whether to shorten place names for flights
            $origin = $transport_type === 'flight' ? ($city_codes[$row['origin']] ?? $row['origin']) : $row['origin'];
            $destination = $transport_type === 'flight' ? ($city_codes[$row['destination']] ?? $row['destination']) : $row['destination'];
            $price = $row['price'];
        ?>

        <a href="<?php echo $seat_selection_pages[$transport_type]; ?>?schedule_id=<?php echo $row['schedule_id']; ?>" class="route">
            <!-- Price in the top-right corner -->
            <div class="route-price">
                ₹<?php echo number_format($price, 2); ?>
            </div>

            <!-- Origin -->
            <div>
                <h4><?php echo $origin; ?></h4>
                <div class="route-time"><?php echo date("g:i A", strtotime($row['departure_time'])); ?></div>
            </div>

            <!-- Dotted line with icon and date in the center -->
            <div class="dotted-line">
                <span></span>
                <img src="<?php echo $icons[$transport_type]; ?>" alt="<?php echo $transport_type; ?> icon" class="route-icon">
                <div class="route-date"><?php echo date("M j", strtotime($row['date'])); ?></div>
                <span></span>
            </div>

            <!-- Destination -->
            <div>
                <h4><?php echo $destination; ?></h4>
                <div class="route-time"><?php echo date("g:i A", strtotime($row['arrival_time'])); ?></div>
            </div>
        </a>

        <?php } ?>
    </div>
</div>

</body>
</html>
