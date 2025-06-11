<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, username, password, role, booking_history FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role, $booking_history);
        if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a new session
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role;
                $_SESSION["booking_history"] = $booking_history;

                // Redirect user to their profile page
                header("location: user-profile.php");
            } else {
                // Password is not valid
                header("location: ../login.html?error=invalid_credentials");
            }
        }
    } else {
        // Username doesn't exist
        header("location: ../login.html?error=invalid_credentials");
    }

    $stmt->close();
    $conn->close();
}
?>