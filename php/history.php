<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.html");
    exit;
}

require_once 'config.php';

// Fetch the latest booking history from the database
$stmt = $conn->prepare("SELECT booking_history FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$stmt->bind_result($booking_history);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking History</title>
    <link rel="stylesheet" href="../css1.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>Booking History for <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
            <nav class="main-navigation">
                <ul>
                    <li><a href="user-profile.php">Profile</a></li>
                    <li><a href="../events.html">Events</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <h2>Your Past Bookings</h2>
        <div>
            <?php if (!empty($booking_history)): ?>
                <pre><?php echo htmlspecialchars($booking_history); ?></pre>
            <?php else: ?>
                <p>No bookings found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>