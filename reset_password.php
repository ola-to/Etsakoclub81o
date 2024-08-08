<?php
include 'db.php';

// Fetch the token from the URL
$token = $_GET['token'];

// Protect against SQL injection
$token = $conn->real_escape_string($token);

// Check if the token is valid
$sql = "SELECT * FROM users WHERE reset_token='$token' AND reset_requested_at > NOW() - INTERVAL 1 HOUR";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Token is valid, show reset password form
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword == $confirmPassword) {
            // Hash the new password
            $hashedPassword = hash('sha256', $newPassword);

            // Update the password in the database
            $sql = "UPDATE users SET password='$hashedPassword', reset_token=NULL, reset_requested_at=NULL WHERE reset_token='$token'";
            if ($conn->query($sql) === TRUE) {
                echo "Password has been reset successfully.";
            } else {
                echo "Failed to update password.";
            }
        } else {
            echo "Passwords do not match.";
        }
    } else {
        // Show the reset password form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
            <title>Reset Password</title>
            <style>
                body {
                    font-family: 'Lato', Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: white;
                }
                .content {
                    max-width: 400px;
                    margin: 50px auto;
                    padding: 20px;
                    border: 1px solid #ccc;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .form-header {
                    text-align: center;
                    font-size: 20px;
                    margin-bottom: 20px;
                    border-bottom: 1px solid lightgrey;
                    padding-bottom: 10px;
                    color: #944b1e;
                }
                .form-label {
                    display: block;
                    margin-bottom: 5px;
                    color: #333;
                }
                .form-input {
                    width: calc(100% - 22px);
                    padding: 10px;
                    margin-bottom: 20px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    box-sizing: border-box;
                }
                .form-button {
                    width: 100%;
                    padding: 10px;
                    background-color: #944b1e;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                }
                .form-button:hover {
                    background-color: #752f0a;
                }
                .form-footer {
                    text-align: center;
                    margin-top: 20px;
                }
                .form-footer a {
                    color: #944b1e;
                    text-decoration: none;
                }
                .form-footer a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="content">
                <div class="form-header">
                    Reset Password
                </div>
                <form method="post" action="https://directory.etsakoclub81.org/login.html">
                    <label for="new_password" class="form-label">New Password:</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" required><br><br>
                    <label for="confirm_password" class="form-label">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required><br><br>
                    <button type="submit" class="form-button">Reset Password</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    echo "Invalid or expired token.";
}

$conn->close();
?>