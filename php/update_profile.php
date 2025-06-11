<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_SESSION["id"];
    $new_username = trim($_POST['new_username']);
    $new_email = trim($_POST['new_email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $update_fields = [];
    $param_types = '';
    $param_values = [];

    // Check if username is already taken by another user
    $stmt_check_username = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt_check_username->bind_param("si", $new_username, $id);
    $stmt_check_username->execute();
    $stmt_check_username->store_result();
    if ($stmt_check_username->num_rows > 0) {
        header("location: user-profile.php?update=username_exists");
        exit;
    }
    $stmt_check_username->close();

    // Check if email is already taken by another user
    $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt_check_email->bind_param("si", $new_email, $id);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    if ($stmt_check_email->num_rows > 0) {
        header("location: user-profile.php?update=email_exists");
        exit;
    }
    $stmt_check_email->close();

    // Add username to update
    $update_fields[] = "username = ?";
    $param_types .= "s";
    $param_values[] = $new_username;

    // Add email to update
    $update_fields[] = "email = ?";
    $param_types .= "s";
    $param_values[] = $new_email;

    // Handle password change if provided
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            header("location: user-profile.php?update=password_mismatch");
            exit;
        }
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = "password = ?";
        $param_types .= "s";
        $param_values[] = $hashed_password;
    }

    // Prepare update statement
    $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $param_types .= "i";
    $param_values[] = $id;

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind parameters dynamically
        $stmt->bind_param($param_types, ...$param_values);

        if ($stmt->execute()) {
            // Update session variables
            $_SESSION["username"] = $new_username;
            // No need to update email in session as it's not directly stored in session
            // Password is not stored in session
            header("location: user-profile.php?update=success");
        } else {
            header("location: user-profile.php?update=failed");
        }
        $stmt->close();
    } else {
        header("location: user-profile.php?update=failed");
    }
    $conn->close();
} else {
    // If not a POST request, redirect to profile page
    header("location: user-profile.php");
    exit;
}
?>