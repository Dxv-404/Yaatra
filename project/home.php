<?php
// Include database connection
include 'db_connect.php';

// Initialize route counts
$trainCount = 0;
$busCount = 0;
$flightCount = 0;

// Query to get the count of routes for each transport type
$query = "SELECT TransportTypes.type_name, COUNT(Schedules.schedule_id) AS count 
          FROM Schedules 
          JOIN TransportTypes ON Schedules.type_id = TransportTypes.type_id 
          GROUP BY TransportTypes.type_name";

$result = $conn->query($query);

// Fetch the results and assign to respective counts
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        switch ($row['type_name']) {
            case 'Train':
                $trainCount = $row['count'];
                break;
            case 'Bus':
                $busCount = $row['count'];
                break;
            case 'Flight':
                $flightCount = $row['count'];
                break;
        }
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yaatra</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* General styling */
        body {
            font-family: Pixelify Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #000;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        /* Intro Animation Styles */
        .intro-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .intro-text {
            font-size: 150px;
            font-family: 'Noto Sans', sans-serif;
            color: #fff;
            animation: changeLanguage 4s steps(7) forwards, fadeOut 1s 4s forwards;
        }

        /* Multiple languages for Yaatra */
        .intro-text::after {
            content: "सफ़र";
            animation: changeLanguage 4s steps(7) forwards;
        }

        /* Fade-out for intro after languages */
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        /* Language transition animation */
        @keyframes changeLanguage {
            0% { content: "सफ़र"; }
            14.28% { content: "صفر"; } /* Bengali */
            28.57% { content: "সফর"; } /* Gujarati */
            42.85% { content: "சஃபர்"; }  /* Malayalam */
            57.14% { content: "సఫర్"; }  /* Telugu */
            71.42% { content: "സഫർ"; }  /* Tamil */
            85.71% { content: "ಯಾತ್ರೆ"; }  /* Kannada */
            100% { content: "सफ़र"; }    /* Back to Hindi */
        }

        /* Hide intro after animation */
        .intro-fade {
            animation: fadeOut 1s forwards;
        }

        /* Rest of the page styling */
        .header {
            margin-top: 2em;
            text-align: center;
            opacity: 0;
            animation: fadeIn 2s ease 4s forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* GIF Container Section */
        .gif-section {
            width: 100%;
            height: 400px;
            background: url('video.gif') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        /* Plan Your Next Trip Text */
        .trip-text {
            font-size: 2.5em;
            margin: 1.5em 0 0.5em;
            text-align: center;
        }

        /* Animated Search Bar */
        .search-bar {
            display: flex;
            justify-content: center;
            width: 100%;
            max-width: 600px;
            margin: 0.5em 0 2em;
        }

        .search-bar button {
            padding: 0.8em;
            font-size: 1em;
            background: #fff;
            color: #000;
            border-radius: 5px 0 0 5px;
            border: none;
            cursor: pointer;
            border-right: 1px solid #ccc;
        }

        .search-bar input[type="text"] {
            padding: 0.8em;
            font-size: 1em;
            width: 100%;
            border: none;
            border-radius: 0 5px 5px 0;
            outline: none;
            color: #333;
        }

        /* Image-Only Containers */
        .image-container-section {
            display: flex;
            gap: 1em;
            justify-content: center;
            margin: 2em 0;
        }

        .image-container {
            position: relative;
            width: 200px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Book Now Button inside Image Containers */
        .image-container a {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.5em 1em;
            font-size: 1em;
            color: #fff;
            background: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .image-container a:hover {
            background: rgba(0, 0, 0, 0.9);
        }
    </style>
</head>
<body>

    <!-- Intro Animation Container -->
    <div class="intro-container" id="intro-container">
        <div class="intro-text"></div>
    </div>

    <!-- Header (hidden initially) -->
    <header class="header" id="header">
        <h1>सफ़र</h1>
    </header>

    <!-- GIF Section -->
    <div class="gif-section">
        <!-- GIF Background is applied via CSS to cover the entire section -->
    </div>

    <!-- "Plan Your Next Trip" Text -->
    <div class="trip-text">Plan your next trip</div>

    <!-- Search Bar with Rotating Placeholder -->
    <div class="search-bar">
        <form action="search_page.php" method="GET" style="display: flex;">
            <button type="submit">Search</button>
            <input type="text" id="search-input" name="location" placeholder="Go to Delhi">
        </form>
    </div>

    <!-- Image-Only Containers with Book Now Button -->
    <div class="image-container-section">
        <div class="image-container">
            <img src="train.jpg" alt="Train Image">
            <a href="booking.php?type=train">Book Now</a>
        </div>
        <div class="image-container">
            <img src="bus.jpg" alt="Bus Image">
            <a href="booking.php?type=bus">Book Now</a>
        </div>
        <div class="image-container">
            <img src="flight.jpg" alt="Flight Image">
            <a href="booking.php?type=flight">Book Now</a>
        </div>
    </div>

    <script>
        // JavaScript for rotating placeholder text in search bar
        const searchInput = document.getElementById("search-input");
        const destinations = ["Go to Delhi", "Go to Jaipur", "Go to Mumbai", "Go to Bengaluru", "Go to Chennai", "Go to Hyderabad", "Go to Kolkata"];
        let index = 0;

        function rotatePlaceholder() {
            searchInput.placeholder = destinations[index];
            index = (index + 1) % destinations.length;
        }

        // Rotate placeholder every 2 seconds
        setInterval(rotatePlaceholder, 2000);

        // JavaScript to remove intro animation after it finishes
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                document.getElementById("intro-container").style.display = 'none';
                document.getElementById("header").style.opacity = 1;
            }, 5000); // Adjusted to match the animation duration
        });
    </script>
</body>
</html>
