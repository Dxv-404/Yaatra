<?php
include 'db_connect.php';
session_start();

$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

// Fetch schedule details and price
$query = "SELECT Routes.origin, Routes.destination, Schedules.price 
          FROM Schedules 
          JOIN Routes ON Schedules.route_id = Routes.route_id 
          WHERE Schedules.schedule_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();
$price_per_seat = $schedule['price'];

// Fetch all seats for the flight schedule
$seats_query = "SELECT seat_id, seat_number, status FROM Seats WHERE schedule_id = ?";
$seats_stmt = $conn->prepare($seats_query);
$seats_stmt->bind_param("i", $schedule_id);
$seats_stmt->execute();
$seats_result = $seats_stmt->get_result();
$seats = [];
while ($row = $seats_result->fetch_assoc()) {
    $seats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Flight Seat Selection</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            background: url('background_2.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: Pixelify Sans, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        
        /* Header with Yaatra title and navigation icons */
        .header {
            width: 100%;
            padding: 15px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 6em;
            position: fixed;
            top: 0;
            z-index: 100;
        }

        .header-icons {
            position: absolute;
            left: 15px;
            display: flex;
            gap: 15px;
        }

        .header-icons img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }

        /* Main Content Container */
        .container {
            background-color: ;
            border-radius: 10px;
            padding: 20px;
            width: 90%;
            max-width: 1000px;
            margin-top: 160px;
        }

        /* Seat Grid Styling */
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* Adjust columns as needed */
            gap: 10px;
            margin-top: 20px;
        }

        .seat {
            width: 50px;
            height: 50px;
            background-color: #fff;
            color: #000;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .seat.selected {
            background-color: #4caf50;
            color: #fff;
        }

        .seat.unavailable {
            background-color: #666;
            cursor: not-allowed;
        }

        /* Price Summary and Continue Button */
        .price-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: ;
            border-radius: 10px;
            color: #fff;
            text-align: center;
        }

        .price-summary p {
            margin: 10px 0;
        }

        .continue-btn {
            padding: 10px 20px;
            font-size: 1.1em;
            font-weight: bold;
            color: #fff;
            background-color: #ff6600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .continue-btn:hover {
            background-color: #ff4500;
        }

        /* Popup Styling */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #222;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            z-index: 1000;
            color: #fff;
            text-align: center;
        }

        .popup h3 {
            margin-bottom: 15px;
        }

        .popup-content {
            margin-bottom: 10px;
        }

        .popup-content label {
            display: block;
            margin: 5px 0;
        }

        .popup-content input[type="text"], 
        .popup-content select {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #444;
            background: #333;
            color: #fff;
            margin-top: 5px;
        }

        .close-popup, .book-btn {
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 1.1em;
            font-weight: bold;
            color: #fff;
            background-color: #ff6600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .close-popup:hover, .book-btn:hover {
            background-color: #ff4500;
        }
    </style>
</head>
<body>

<!-- Header with Yaatra Title and Icons -->
<div class="header">
    <div class="header-icons">
        <a href="home.php"><img src="home_icon.png" alt="Home"></a>
        <a href="booking.php?type=train"><img src="train_icon.png" alt="Train"></a>
        <a href="booking.php?type=bus"><img src="bus_icon.png" alt="Bus"></a>
        <a href="booking.php?type=flight"><img src="flight_icon.png" alt="Flight"></a>
    </div>
    सफ़र
</div>

<!-- Main Seat Selection Container -->
<div class="container">
    <h2>Flight from <?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></h2>
    <p>Price per Seat: ₹<?php echo number_format($price_per_seat, 2); ?></p>

    <div class="seat-grid">
        <?php foreach ($seats as $seat): ?>
            <div class="seat <?php echo $seat['status'] == 'Booked' ? 'unavailable' : ''; ?>" 
                 data-seat-id="<?php echo $seat['seat_id']; ?>" 
                 data-seat-number="<?php echo $seat['seat_number']; ?>">
                 <?php echo $seat['seat_number']; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="price-summary">
        <p>Total Price: ₹<span id="total-price">0.00</span></p>
        <button class="continue-btn" onclick="showPopup()">Continue</button>
    </div>
</div>

<!-- Popup for Ticket Details -->
<div id="popup" class="popup">
    <h3>Enter Ticket Details</h3>
    <form id="ticketDetailsForm" method="POST" action="confirmation.php">
        <div id="ticketDetailsContainer"></div>
        <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
        <button type="button" class="close-popup" onclick="closePopup()">Close</button>
        <button type="submit" class="book-btn">Book Now</button>
    </form>
</div>

<script>
    const selectedSeats = [];
    const pricePerSeat = <?php echo $price_per_seat; ?>;
    const totalPriceElement = document.getElementById("total-price");

    document.querySelectorAll('.seat').forEach(seat => {
        seat.addEventListener('click', function () {
            if (!this.classList.contains('unavailable')) {
                this.classList.toggle('selected');
                const seatID = this.getAttribute('data-seat-id');
                const seatNumber = this.getAttribute('data-seat-number');
                
                if (selectedSeats.some(seat => seat.id === seatID)) {
                    selectedSeats.splice(selectedSeats.findIndex(seat => seat.id === seatID), 1);
                } else {
                    selectedSeats.push({ id: seatID, number: seatNumber });
                }
                totalPriceElement.textContent = (selectedSeats.length * pricePerSeat).toFixed(2);
            }
        });
    });

    function showPopup() {
        const container = document.getElementById('ticketDetailsContainer');
        container.innerHTML = '';
        selectedSeats.forEach(seat => {
            const seatDiv = document.createElement('div');
            seatDiv.classList.add('popup-content');
            seatDiv.innerHTML = `
                <label>Seat ${seat.number}</label>
                <input type="text" name="ticket_holder[${seat.id}]" placeholder="Enter Name" required>
                <label>Baggage</label>
                <select name="baggage[${seat.id}]">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>`;
            container.appendChild(seatDiv);
        });
        document.getElementById('popup').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
    }
</script>

</body>
</html>
