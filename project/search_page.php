<?php
include 'db_connect.php';

$search_location = $_GET['location'] ?? '';

// Prepare a query to fetch schedules matching the search location in either origin or destination
$query = "SELECT Routes.origin, Routes.destination, Schedules.departure_time, 
                 Schedules.arrival_time, Schedules.price, Schedules.schedule_id, 
                 Schedules.date, TransportTypes.type_name
          FROM Schedules
          JOIN Routes ON Schedules.route_id = Routes.route_id
          JOIN TransportTypes ON Schedules.type_id = TransportTypes.type_id
          WHERE Routes.origin LIKE ? OR Routes.destination LIKE ?";
$stmt = $conn->prepare($query);
$search_term = "%$search_location%";
$stmt->bind_param("ss", $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

// Icons for each transport type
$icons = [
    'Flight' => '✈️',
    'Train' => '🚆',
    'Bus' => '🚌'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: Pixelify Sans, sans-serif;
            background-color: #000;
            color: #fff;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 100px; /* Adjust for header */
        }

        /* Loading animation */
        #loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
            background-color: #000;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
        }

        /* Header with icons */
        .header {
            width: 100%;
            padding: 20px;
            background-color: black;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 3.2em;
            position: fixed;
            top: 0;
            z-index: 5;
        }

        .header-icons {
            position: absolute;
            left: 20px;
            display: flex;
            gap: 15px;
        }

        .header-icons img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        /* Container for search results */
        .container {
            max-width: 800px;
            width: 90%;
            margin: 0 auto;
            padding-top: 20px;
            display: none; /* Initially hidden, will be shown after loading */
        }

        h2 {
            text-align: center;
            font-size: 1.8em;
            color: #fff;
            margin-bottom: 20px;
        }

        /* Styling for each route */
        .route {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .route:hover {
            background-color: #444;
        }

        .icon {
            font-size: 2em;
            margin-right: 20px;
        }

        .details {
            flex-grow: 1;
        }

        .details p {
            margin: 5px 0;
            font-size: 1em;
            color: #ccc;
        }

        .route-price {
            font-weight: bold;
            font-size: 1.2em;
            color: #ffd700;
        }

        /* GIF styling for loading */
        #loading-gif {
            width: 100px;
        }
    </style>
</head>
<body>

<!-- Loading Animation -->
<div id="loading-container">
    <img id="loading-gif" src="loading_animation.gif" alt="Loading...">
</div>

<!-- Header with Navigation Icons -->
<div class="header" id="header" style="display: none;">
    <div class="header-icons">
        <a href="home.php"><img src="home_icon.png" alt="Home"></a>
        <a href="booking.php?type=train"><img src="train_icon.png" alt="Train"></a>
        <a href="booking.php?type=bus"><img src="bus_icon.png" alt="Bus"></a>
        <a href="booking.php?type=flight"><img src="flight_icon.png" alt="Flight"></a>
    </div>
    सफ़र
</div>

<!-- Search Results Container -->
<div class="container" id="container">
    <h2>Search Results for "<?php echo htmlspecialchars($search_location); ?>"</h2>

    <?php while ($schedule = $result->fetch_assoc()): ?>
        <?php 
        $icon = $icons[$schedule['type_name']] ?? '🚌';
        $transport_type = strtolower($schedule['type_name']);
        ?>
        <a href="seat_selection_<?php echo $transport_type; ?>.php?schedule_id=<?php echo $schedule['schedule_id']; ?>" class="route">
            <div class="icon"><?php echo $icon; ?></div>
            <div class="details">
                <p><strong><?php echo htmlspecialchars($schedule['origin']); ?></strong> to <strong><?php echo htmlspecialchars($schedule['destination']); ?></strong></p>
                <p>Departure: <?php echo date("g:i A", strtotime($schedule['departure_time'])); ?> | Arrival: <?php echo date("g:i A", strtotime($schedule['arrival_time'])); ?></p>
                <p>Date: <?php echo date("F j, Y", strtotime($schedule['date'])); ?></p>
            </div>
            <div class="route-price">₹<?php echo number_format($schedule['price'], 2); ?></div>
        </a>
    <?php endwhile; ?>
</div>

<script>
    // Show loading GIF for 3 seconds, then display the page content
    setTimeout(() => {
        document.getElementById('loading-container').style.display = 'none';
        document.getElementById('header').style.display = 'flex';
        document.getElementById('container').style.display = 'block';
    }, 3000);
</script>

</body>
</html>
