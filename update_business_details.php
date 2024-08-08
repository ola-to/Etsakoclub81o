<?php
session_start();
include 'db.php';

// Check if the user is logged in, if not, return an error
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo 'error: not logged in';
    exit;
}

// Get the field and value from the POST request
$field = $_POST['field'];
$value = $_POST['value'];
$membership_no = $_SESSION['membership_no'];

// Log the received data for debugging
error_log("Received field: $field, value: $value, membership_no: $membership_no");

// Validate the inputs
if (!empty($field) && !empty($value) && !empty($membership_no)) {
    // Prepare the SQL statement
    $query = "UPDATE members SET $field = ? WHERE membership_no = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $value, $membership_no);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo 'success: updated ' . htmlspecialchars($field) . ' to ' . htmlspecialchars($value);
    } else {
        echo 'error: ' . $stmt->error;
    }
} else {
    echo 'error: missing data';
}
?>