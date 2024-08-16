<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$license_key = isset($_POST['license_key']) ? $_POST['license_key'] : '';
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
$expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
$contact = isset($_POST['contact']) ? $_POST['contact'] : '';
$mac_address = isset($_POST['mac_address']) ? $_POST['mac_address'] : '';

$message = "";
$success = false;

if ($license_key && $company_name && $expiry_date && $contact && $mac_address) {
    $check_sql = "SELECT * FROM licenses WHERE license_key = '$license_key'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $message = "License already exists.";
    } else {
        $sql = "INSERT INTO licenses (license_key, company_name, expiry_date, contact, mac_address) VALUES ('$license_key', '$company_name', '$expiry_date', '$contact', '$mac_address')";

        if ($conn->query($sql) === TRUE) {
            $message = "License saved successfully";
            $success = true;
        } else {
            $message = "Error saving license: " . $conn->error;
        }
    }
} else {
    $message = "All fields are required.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save License</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #383838;
            padding: 15px;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .save-form {
            margin-top: 20px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .form-group label {
            width: 200px;
            font-weight: bold;
            margin-right: 15px;
            text-align: right;
        }
        .form-group input[type="text"], .form-group input[type="date"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #3395FF;
            border: none;
            border-radius: 4px;
            color: black;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Add New License</h2>
    </div>
    <div class="container">
        <form method="post" action="save_license.php" class="save-form">
            <div class="form-group">
                <label for="license_key">License Key:</label>
                <input type="text" name="license_key" id="license_key" placeholder="License Key" required>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" name="company_name" id="company_name" placeholder="Company Name" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date:</label>
                <input type="date" name="expiry_date" id="expiry_date" placeholder="Expiry Date" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" name="contact" id="contact" placeholder="Contact" required>
            </div>
            <div class="form-group">
                <label for="mac_address">MAC Address:</label>
                <input type="text" name="mac_address" id="mac_address" placeholder="MAC Address" required>
            </div>
            <div class="button-group">
                <button type="submit">Save License</button>
                <button type="button" onclick="window.location.href='license.html'">Back to Main Page</button>
            </div>
        </form>
    </div>

    <script>
        <?php if ($success): ?>
            alert("<?php echo $message; ?>");
            window.location.href = 'license.html';
        <?php endif; ?>
    </script>
</body>
</html>
