<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.html");
    exit;
}

// Include config file
require_once 'config.php';

// Check if connection is valid after including config.php
if (!$conn || $conn->connect_error) {
    error_log("Critical Error: Database connection failed at user-profile.php. " . ($conn ? $conn->connect_error : "Connection object is null."));
    // Handle this gracefully, e.g., redirect to an error page or show a friendly message
    echo "<h1>Error: Could not load user profile due to a database issue. Please try again later.</h1>";
    exit; // Terminate script execution
}

// Initialize variables to 'N/A' or appropriate defaults
$email_from_db = 'N/A';
$booking_history = 'No booking history available.';

// Fetch email from the database
// NO need for the `if (!isset($conn) || $conn->connect_error)` block here
// because the connection was already established at the top and not closed.
$stmt_email = $conn->prepare("SELECT email FROM users WHERE id = ?");
if ($stmt_email) { // Always check if prepare was successful
    $stmt_email->bind_param("i", $_SESSION["id"]);
    $stmt_email->execute();
    $stmt_email->bind_result($email_from_db);
    $stmt_email->fetch();
    $stmt_email->close();
} else {
    // Handle error if prepare fails
    // Safely check if $conn is an object before accessing its properties
    if (is_object($conn) && property_exists($conn, 'error')) {
        error_log("Error preparing email fetch statement: " . $conn->error);
    } else {
        error_log("Error preparing email fetch statement: Database connection object invalid or error property missing.");
    }
    $email_from_db = 'N/A'; // Default value in case of error
}


// Fetch booking history from the database
$stmt_history = $conn->prepare("SELECT booking_history FROM users WHERE id = ?");
if ($stmt_history) { // Always check if prepare was successful
    $stmt_history->bind_param("i", $_SESSION["id"]);
    $stmt_history->execute();
    $stmt_history->bind_result($booking_history);
    $stmt_history->fetch();
    $stmt_history->close();
} else {
    // Handle error if prepare fails
    // Safely check if $conn is an object before accessing its properties
    if (is_object($conn) && property_exists($conn, 'error')) {
        error_log("Error preparing booking history fetch statement: " . $conn->error);
    } else {
        error_log("Error preparing booking history fetch statement: Database connection object invalid or error property missing.");
    }
    $booking_history = 'Error fetching history.'; // Default value in case of error
}

// Assign fetched data to session variables for use in HTML
$_SESSION["email"] = $email_from_db;
$_SESSION["booking_history"] = $booking_history;

// Close the connection at the very end of the script
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="../css1.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <nav class="main-navigation">
                <ul>
                    <li><a href="../home.html">Home</a></li>
                    <li><a href="../events.html">Events</a></li>
                    <li><a href="history.php">History</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="user-profile-content">
        <div class="container">
            <h2>Your Profile</h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION["username"]); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION["role"]); ?></p>
            
            <h3>Booking History</h3>
            <?php if (!empty($_SESSION["booking_history"]) && $_SESSION["booking_history"] !== 'No booking history available.'): ?>
                <p><?php echo nl2br(htmlspecialchars($_SESSION["booking_history"])); ?></p>
            <?php else: ?>
                <p><?php echo htmlspecialchars($_SESSION["booking_history"]); ?></p>
            <?php endif; ?>

            <div class="profile-actions">
                <a href="reset-password.php" class="btn btn-primary">Reset Your Password</a>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>Showlet</h2>
                    <p>Providing memorable experiences</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                      <li><a href="../home.html">Home</a></li>
                      <li><a href="user-profile.php">User</a></li>
                      <li><a href="../events.html">Events</a></li>
                      <li><a href="../ticket.html">Book Now</a></li>
                      <li><a href="history.php">History</a></li>
                      <li><a href="../csform.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p>123 King Avenue, Cape Town</p>
                    <p>Phone: (123) 456-7890</p>
                    <p>Email: CDS@gmail.com</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Showlet. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>