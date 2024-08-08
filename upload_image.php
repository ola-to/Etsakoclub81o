<?php
session_start();

include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo 'Unauthorized access.';
    exit;
}

// Get the logged-in user's membership number
$membership_no = $_SESSION['membership_no'];

if (!$membership_no) {
    echo 'No membership number provided.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Validate the image file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($image['type'], $allowed_types)) {
        echo 'Unsupported file type.';
        exit;
    }

    if ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
        echo 'File size exceeds the limit.';
        exit;
    }

    // Create a directory to store uploaded images if it doesn't exist
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate a unique file name
    $file_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    // Move the uploaded file to the designated directory
    if (move_uploaded_file($image['tmp_name'], $file_path)) {
        // Update the database with the new image path
        $query = "UPDATE members SET image_path = ? WHERE membership_no = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $file_path, $membership_no);
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Database update failed: ' . $stmt->error;
        }
    } else {
        echo 'Failed to move the uploaded file.';
    }
} else {
    echo 'No file uploaded.';
}
?>