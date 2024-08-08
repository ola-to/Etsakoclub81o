<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

include 'db.php';

// Get the logged-in user's membership number from the session
$membership_no = $_SESSION['membership_no'];

if ($membership_no) {
    $query = "SELECT * FROM members WHERE membership_no = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $membership_no); // Assuming membership_no is a string
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $business = $result->fetch_assoc();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    $business = null;
    echo 'No membership number provided.';
}

// Handle image upload and update in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Update the image path in the database
            $stmt = $conn->prepare("UPDATE members SET image_path = ? WHERE membership_no = ?");
            $stmt->bind_param("ss", $target_file, $membership_no);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = 'Image uploaded successfully!';
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $_SESSION['flash_message'] = 'Sorry, there was an error uploading your file.';
        }
    } else {
        $_SESSION['flash_message'] = 'File is not an image.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=PT+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Business Details - <?php echo htmlspecialchars($business['business_type']); ?></title>
    <style>
        body {
            font-family: 'Lato', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .header, .mobile-header {
            display: flex;
            align-items: center;
            padding: 10px;
            color: white;
            width: 100%;
        }
        .header {
            background-color: white;
            justify-content: space-between;
        }
        .mobile-header {
            background-color: white;
            justify-content: space-between;
            position: relative;
        }
        .logo {
            width: 150px;
            height: 50px;
            margin-right: 10px;
        }
        .logo img {
            width: 100%;
            height: auto;
        }
        .search-box {
            display: flex;
            align-items: right;
            margin-right: auto;
            margin-left: 60px;
        }
        #search-input, #search-button {
            height: 40px;
            line-height: 40px;
            font-size: 16px;
            padding: 0 10px;
        }
        #search-input {
            border: 1px solid #ccc;
            border-right: none;
            border-radius: 5px 0 0 5px;
            box-sizing: border-box;
        }
        #search-button {
            border: 1px solid #ccc;
            background-color: #944b1e;
            color: white;
            cursor: pointer;
            border-radius: 0 5px 5px 0;
            border-left: none;
            box-sizing: border-box;
        }
        .mobile-search-box input {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 16px;
            padding: 5px;
        }
        .mobile-search-box input::placeholder {
            color: grey;
        }
        .mobile-search-box input:focus {
            outline: none;
        }
        .business-links {
            display: flex;
            align-items: center;
        }
        .business-links a {
            margin-right: 40px;
            text-decoration: none;
            color: black;
            text-align: center;
            font-size: 14px;
        }
        .claim-button {
            background-color: #100660;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        .subheader {
            background-color: white;
            color: #333;
            padding: 11px 17px;
            text-align: left;
            font-size: 17px;
            border-top: 1px solid black;
            width: 100%;
            margin-top: 5px;
        }
        .content-wrapper {
            display: flex;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            flex: 1;
            overflow-x: hidden;
        }
        .column img {
            max-width: 50%;
            max-height: 150px; /* Reduced size */
            height: auto;
            display: block;
            margin-left: 0;
            object-fit: cover;
        }
        .sidebar {
            width: 25%;
            padding: 20px;
            background-color: #f4f4f4;
            border-right: 1px solid #ddd;
        }
        .searchm-box {
            display: flex;
            align-items: center;
            margin-right: auto;
            margin-left: 60px;
        }
        #searchm-input, #search-button {
            height: 40px;
            line-height: 40px;
            font-size: 16px;
            padding: 0 10px;
        }
        #searchm-input {
            border: 1px solid #ccc;
            border-right: none;
            border-radius: 5px 0 0 5px;
            box-sizing: border-box;
        }
        #searchm-button {
            border: 1px solid #ccc;
            background-color: #944b1e;
            color: white;
            cursor: pointer;
            border-radius: 0 5px 5px 0;
            border-left: none;
            box-sizing: border-box;
        }
        .sidebar h3 {
            margin-top: 0;
        }
        .main-menu {
            margin-top: 20px;
        }
        .main-menu ul {
            list-style: none;
            padding: 0;
        }
        .main-menu ul li {
            margin-bottom: 10px;
        }
        .main-menu ul li i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
            flex: 1;
            margin: 0 20px;
            background-color: white;
        }
        .footer {
            background-color: black;
            color: white;
            padding: 20px;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }
        .profile-box {
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .progress-bar {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-bar-fill {
            display: block;
            height: 20px;
            width: 30%;
            background-color: green;
        }
        .highlight {
            font-family: 'PT Serif', serif;
            font-size: 1.5em;
        }
        .edit-link {
            float: right; /* Align to the right */
            color: grey;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid black;
            padding: 2px 10px;
            border-radius: 3px;
            margin-left: 10px;
            font-size: 1.5em;
        }
        .divider {
            width: 100%;
            height: 1px;
            background-color: grey;
            margin: 20px 0;
        }
        .dropdown {
            display: none;
        }
        .dropdown ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .dropdown ul li {
            margin: 0;
            padding: 10px;
            background-color: #f4f4f4;
            border-bottom: 1px solid #ddd;
        }
        .dropdown ul li:hover {
            background-color: #ddd;
        }
        .dropdown-btn {
            display: none;
            background-color: #944b1e;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            text-align: left;
            width: 100%;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .mobile-header .search-box {
            flex-grow: 1;
            margin-right: 0;
        }
        .mobile-header .logo {
            margin: auto;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 15px;
            width: 150px; /* Increased width */
            height: auto;
        }
        .menu-icon {
            width: 40px;
            height: 40px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: black;
            font-size: 24px;
        }
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: white;
            z-index: 999;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .popup-content {
            position: absolute;
            top: 23%;
            left: 30%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            text-align: left;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
        }
        .popup-content a {
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            color: black;
            font-size: 18px;
        }
        .popup-content a:hover {
            text-decoration: underline;
        }
        .close-btn {
            background-color: #ccc;
            color: black;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 20px;
        }
        .close-btn:hover {
            background-color: #aaa;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .content {
                margin: 0;
            }
            .dropdown-btn {
                display: block;
            }
            .dropdown {
                display: none;
                width: 100%;
                margin: 0;
            }
            .dropdown ul {
                padding: 0;
            }
            .dropdown ul li {
                padding: 10px 20px;
            }
            .column img {
                max-width: 70%;
                max-height: 200px;
                margin-left: 0;
            }
            .header {
                display: none;
            }
            .mobile-header {
                display: flex;
            }
            .mobile-header .logo {
                display: block; /* Show logo on mobile view */
            }
            .menu-icon {
                display: block; /* Show menu icon on mobile view */
            }
            .new-search-box {
                display: none;
            }
            .searchm-box {
                display: block;
                margin-top: 10px;
                margin-left: 28px;
                }
        }
        @media (min-width: 769px) {
            .new-search-box {
                display: none;
            }
            .mobile-header .logo {
                display: none; /* Hide logo on larger screens */
            }
            .menu-icon {
                display: none; /* Hide menu icon on larger screens */
            }
            .searchm-box {
                display: none;
            }
        }
        .edit-form {
            display: flex;
            align-items: center;
        }
        .edit-form input {
            flex: 1;
            margin-right: 10px;
            padding: 10px;
            font-size: 16px;
            height: 40px;
        }
        .edit-form button {
            padding: 10px 15px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            height: 40px;
        }
        .save-button {
            padding: 10px 15px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            height: 40px;
            display: inline-block;
            margin-left: 10px;
        }
        .edit-input {
            padding: 10px;
            font-size: 16px;
            height: 40px;
            width: calc(100% - 40px);
            margin-top: 10px;
        }
        .divider {
            height: 20px;
            background-color: grey;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="#" class="logo">
            <img src="ets.png" alt="Logo">
        </a>
        <div class="search-box">
            <input type="text" id="search-input" placeholder="Search... 🔍">
            <button id="search-button" style="background-color: green; color: white;" onclick="performSearch()">🔍</button>
        </div>
        <div class="business-links">
            <a href="https://directory.etsakoclub81.org">Home</a>
            <a href="https://directory.etsakoclub81.org/register.html#">Register</a>
            <a href="https://directory.etsakoclub81.org/login.html">Login</a>
            <a href="https://directory.etsakoclub81.org">Back to Main Website</a>
        </div>
    </div>
    <div class="mobile-header">
        <div class="mobile-search-box">
            <input type="text" placeholder="" aria-label="Search">
        </div>
        <a href="#" class="logo">
            <img src="ets.png" alt="Logo" style="width: 120px; height: auto;"> <!-- Increased logo size -->
        </a>
        <button class="menu-icon" onclick="openPopup()">☰</button>
    </div>
    <div class="searchm-box">
            <input type="text" id="search-input" placeholder="Search... 🔍">
            <button id="search-button" style="background-color: green; color: white;" onclick="performSearch()">🔍</button>
        </div>

    <!-- Popup Menu -->
    <div class="popup" id="menuPopup">
        <div class="popup-content">
            <a href="https://directory.etsakoclub81.org">Home</a>
            <a href="https://directory.etsakoclub81.org/register.html#">Register</a>
            <a href="https://directory.etsakoclub81.org/login.html">Login</a>
            <a href="https://directory.etsakoclub81.org">Back to Main Website</a>
            <button class="close-btn" onclick="closePopup()">Close</button>
        </div>
    </div>
    <div class="subheader">
        
    </div>
    <div class="content-wrapper">
        <div class="sidebar">
            <h3><?php echo htmlspecialchars($business['business_type']); ?></h3>
            <p><strong class="highlight">Location:</strong><br><?php echo htmlspecialchars($business['physical_address']); ?></p>
            <h3>Main Menu</h3>
            <div class="main-menu">
                <ul>
                    <li><a href="https://directory.etsakoclub81.org"><i class="fas fa-home"></i> Home</a></li>
<li><a href="https://directory.etsakoclub81.org/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li>Marketing Services</li>
                    <hr>
                    <li><i class="fas fa-lock"></i> Listing Performance</li>
                </ul>
            </div>
        </div>
        <div class="content">
        <?php
        $image_src = isset($business['image_path']) ? $business['image_path'] : 'https://via.placeholder.com/200x150.png?text=Business+Image';
        $image_src .= '?' . time(); // To bypass caching
        ?>
            <img src="<?php echo $image_src; ?>" alt="Business Image" id="business-image" style="max-width: 100px; max-height: 150px; border: 1px solid lightgrey;">
            <h2><?php echo htmlspecialchars($business['business_type']); ?></h2>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($business['physical_address']); ?></p>
        
            <button class="dropdown-btn" onclick="toggleDropdown()">Menu</button>
            <div class="dropdown">
                <ul>
                    <li><a href="https://directory.etsakoclub81.org"><i class="fas fa-home"></i> Home</a></li>
<li><a href="https://directory.etsakoclub81.org/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li>Marketing Services</li>
                    <hr>
                    <li><i class="fas fa-lock"></i> Listing Performance</li>
                </ul>
            </div>
            <?php if ($business): ?>
            
            <div class="profile-box">
                <p>Core Profile Completeness</p>
                <p>Now you're cookin'. You're nearly there!</p>
                <div class="progress-bar">
                    <span class="progress-bar-fill"></span>
                </div>
            </div>
            <div class="row">
                <div class="column">
                    <img src="<?php echo $image_src; ?>" alt="Business Image" id="business-image" style="max-width: 200px; max-height: 150px; border: 2px solid lightgrey;">
                    <br>
                    <button onclick="document.getElementById('image-upload').click()">Change</button>
                    <input type="file" id="image-upload" style="display: none;" onchange="uploadImage()">
                    <div class="progress-bar" id="image-upload-progress" style="display: none;">
                        <span class="progress-bar-fill" style="width: 0;"></span>
                    </div>
                </div>
                <div class="column">
                    <h2><?php echo htmlspecialchars($business['business_type']); ?></h2>
                    <p>
                        <strong class="highlight">Contact Person:</strong><span class="edit-link" onclick="editField('contact_person')">edit</span><br>
                        <span data-field="contact_person"><?php echo htmlspecialchars($business['contact_person']); ?></span>
                    </p>
                    <p>
                        <strong class="highlight">Location:</strong><span class="edit-link" onclick="editField('physical_address')">edit</span><br>
                        <span data-field="physical_address"><?php echo htmlspecialchars($business['physical_address']); ?></span>
                    </p>
                    
                    <div id="phone-details">
                        <p>
                            <strong class="highlight">Phone:</strong><br>
                            <span data-field="business_phone_no"><?php echo htmlspecialchars($business['business_phone_no']); ?></span>
                            <span class="edit-link" onclick="editField('business_phone_no')">edit</span>
                        </p>
                    </div>
                    <div id="updated-phone-numbers"></div>
                    <br>
                    <div class="divider"></div>
                    <div>
                        <p>
                            <strong class="highlight">About:</strong><span class="edit-link" onclick="editField('about')">edit</span><br>
                            <span data-field="about"><?php echo htmlspecialchars($business['about'] ?? 'Customer use this information to learn what makes your company great.'); ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <p>No business details found for the specified type.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <div class="footer-column">
            <p>© 2024 Etsako Club 81. All rights reserved.</p>
        </div>
    </div>

    <script>
        function performSearch() {
            const searchInput = document.getElementById('search-input').value;
            window.location.href = `index.php?search=${encodeURIComponent(searchInput)}`;
        }

        function toggleDropdown() {
            var dropdown = document.querySelector('.dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
        // JavaScript function to open the popup
        function openPopup() {
            var popup = document.getElementById("menuPopup");
            popup.style.display = "block";
            document.body.style.overflow = "hidden"; // Disable scrolling on the body
        }

        // JavaScript function to close the popup
        function closePopup() {
            var popup = document.getElementById("menuPopup");
            popup.style.display = "none";
            document.body.style.overflow = "auto"; // Enable scrolling on the body
        }

        function saveField(field, newValue) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_business_details.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.includes('success')) {
                        location.reload();
                    } else {
                        alert('Failed to update the field. Please try again.');
                    }
                }
            };
            xhr.send('field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(newValue));
        }

        function editField(field) {
            var span = document.querySelector('[data-field="' + field + '"]');
            if (!span) return;
            var currentValue = span.innerText;
            var input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            input.setAttribute('data-field', field);
            input.className = 'edit-input';
            var saveButton = document.createElement('button');
            saveButton.innerText = 'Save';
            saveButton.className = 'save-button';
            saveButton.onclick = function() {
                saveField(field, input.value);
            };
            var parent = span.parentNode;
            parent.insertBefore(input, span);
            parent.insertBefore(saveButton, span);
            span.style.display = 'none';
        }

        function uploadImage() {
            var fileInput = document.getElementById('image-upload');
            var file = fileInput.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('image', file);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_image.php', true);
            xhr.upload.onprogress = function(event) {
                var progress = Math.round((event.loaded / event.total) * 100);
                var progressBar = document.getElementById('image-upload-progress');
                progressBar.style.display = 'block';
                progressBar.querySelector('.progress-bar-fill').style.width = progress + '%';
            };
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.includes('success')) {
                        var img = document.getElementById('business-image');
                        img.src = URL.createObjectURL(file);
                        var progressBar = document.getElementById('image-upload-progress');
                        progressBar.style.display = 'none';
                        img.src = 'path_to_uploaded_image.jpg?' + new Date().getTime();
                    } else {
                        alert('Failed to upload the image. Please try again.');
                    }
                }
            };
            xhr.send(formData);
        }

        window.editField = editField;
    </script>
</body>
</html>