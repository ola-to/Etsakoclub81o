<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session

$servername = "localhost"; // Your database server
$dbUsername = "joansimm_mem"; // Your database username
$dbPassword = "joansimm_mem"; // Your database password
$dbname = "joansimm_mem"; // Your database name

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password, membership_no FROM members WHERE email = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword, $membership_no);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $id;
            $_SESSION['membership_no'] = $membership_no;

            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>