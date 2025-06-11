<?php
session_start();
require_once 'config.php'; // Your database connection file

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $event_name = trim($_POST["event_name"] ?? ''); // Get event name from form
    $number_of_tickets = intval($_POST["num_tickets"] ?? 1); // Get number of tickets
    $booking_date = date('Y-m-d H:i:s'); // Current timestamp for booking

    // Basic validation
    if (empty($event_name) || $number_of_tickets <= 0) {
        die("Error: Event name and number of tickets are required.");
    }

    // 1. Fetch current booking_history for the user
    $current_history_json = null;
    $stmt_fetch = $conn->prepare("SELECT booking_history FROM users WHERE id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $stmt_fetch->bind_result($current_history_json);
        $stmt_fetch->fetch();
        $stmt_fetch->close();
    } else {
        error_log("Error preparing fetch history statement: " . $conn->error);
        die("Database error. Please try again.");
    }

    // Decode existing history or initialize an empty array
    $booking_history_array = [];
    if ($current_history_json && $current_history_json !== 'null') {
        $decoded_history = json_decode($current_history_json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_history)) {
            $booking_history_array = $decoded_history;
        } else {
            error_log("Error decoding JSON booking history for user ID: " . $user_id . " | JSON: " . $current_history_json);
            // Handle corrupted JSON gracefully, e.g., start fresh or notify user
            // For now, we'll proceed with an empty array.
        }
    }

    // 2. Add new booking details
    $new_booking = [
        'event_name' => $event_name,
        'number_of_tickets' => $number_of_tickets,
        'booking_date' => $booking_date,
        'booking_id' => uniqid('booking_') // Simple unique ID for each booking
    ];
    $booking_history_array[] = $new_booking;

    // 3. Encode the updated array back to JSON
    $updated_history_json = json_encode($booking_history_array);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error encoding JSON booking history for user ID: " . $user_id . " | Data: " . print_r($booking_history_array, true));
        die("Error processing booking data. Please try again.");
    }

    // 4. Update the user's booking_history column
    $stmt_update = $conn->prepare("UPDATE users SET booking_history = ? WHERE id = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("si", $updated_history_json, $user_id); // 's' for string (JSON is stored as string)
        if ($stmt_update->execute()) {
            // Update session variable as well so user-profile.php can show it immediately
            $_SESSION["booking_history"] = $updated_history_json;
            header("location: user-profile.php"); // Redirect to profile or history page
            exit;
        } else {
            error_log("Error updating booking history: " . $stmt_update->error);
            echo "Error: Could not save your booking. " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        error_log("Error preparing update history statement: " . $conn->error);
        die("Database error. Please try again.");
    }
}

$conn->close();
?>