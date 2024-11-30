<?php
// Include database connection
include 'db_connect.php';

// Define seat counts for each transport type
$seat_counts = [
    'flight' => 50,
    'train' => 30,
    'bus' => 20,
];

// Fetch all schedules with their transport type
$query = "SELECT Schedules.schedule_id, TransportTypes.type_name 
          FROM Schedules 
          JOIN TransportTypes ON Schedules.type_id = TransportTypes.type_id";
$result = $conn->query($query);

while ($schedule = $result->fetch_assoc()) {
    $schedule_id = $schedule['schedule_id'];
    $transport_type = strtolower($schedule['type_name']);
    $seat_count = $seat_counts[$transport_type] ?? 20; // Default to 20 if type not found

    // Check if seats already exist for this schedule
    $seat_check_query = "SELECT COUNT(*) as seat_count FROM Seats WHERE schedule_id = ?";
    $seat_check_stmt = $conn->prepare($seat_check_query);
    $seat_check_stmt->bind_param("i", $schedule_id);
    $seat_check_stmt->execute();
    $seat_check_result = $seat_check_stmt->get_result();
    $current_seat_count = $seat_check_result->fetch_assoc()['seat_count'];

    // If the required number of seats are not present, add them
    if ($current_seat_count < $seat_count) {
        for ($i = $current_seat_count + 1; $i <= $seat_count; $i++) {
            // Generate seat number (e.g., A1, A2, etc., assuming 6 seats per row for flights)
            $row = chr(65 + floor(($i - 1) / 6)); // Adjust seats per row if necessary
            $seat_number = $row . (($i - 1) % 6 + 1);

            // Insert the new seat
            $insert_seat_query = "INSERT INTO Seats (schedule_id, seat_number, status) VALUES (?, ?, 'Available')";
            $insert_seat_stmt = $conn->prepare($insert_seat_query);
            $insert_seat_stmt->bind_param("is", $schedule_id, $seat_number);
            $insert_seat_stmt->execute();
        }
    }
}

echo "Seats updated for all schedules based on transport type.";
$conn->close();
?>
