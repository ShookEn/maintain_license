<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_license_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$license_key = isset($_GET['license_key']) ? $_GET['license_key'] : '';
$company_name = isset($_GET['company_name']) ? $_GET['company_name'] : '';

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_license_key = isset($_POST['license_key']) ? $_POST['license_key'] : '';
    $new_company_name = isset($_POST['company_name']) ? $_POST['company_name'] : '';
    $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
    $mac_address = isset($_POST['mac_address']) ? $_POST['mac_address'] : '';

    $sql = "UPDATE licenses SET license_key = ?, company_name = ?, expiry_date = ?, contact = ?, mac_address = ? WHERE license_key = ? AND company_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $new_license_key, $new_company_name, $expiry_date, $contact, $mac_address, $license_key, $company_name);

    if ($stmt->execute()) {
        $message = "License updated successfully";
        $success = true;
    } else {
        $message = "Error updating license: " . $stmt->error;
    }
}

$sql = "SELECT * FROM licenses WHERE license_key = ? AND company_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $license_key, $company_name);
$stmt->execute();
$result = $stmt->get_result();
$license = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update License</title>
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
            color: #4CAF50;
        }
        .update-form {
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
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #61D854;
            border: none;
            border-radius: 4px;
            color: black;
            cursor: pointer;
        }
        button:hover {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Update License</h2>
    </div>
    <div class="container">
        <form method="post" action="update_license.php?license_key=<?php echo urlencode($license_key); ?>&company_name=<?php echo urlencode($company_name); ?>" class="update-form">
            <div class="form-group">
                <label for="license_key">License Key:</label>
                <input type="text" name="license_key" id="license_key" value="<?php echo htmlspecialchars($license['license_key']); ?>" required>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($license['company_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date:</label>
                <input type="date" name="expiry_date" id="expiry_date" value="<?php echo htmlspecialchars($license['expiry_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact:</label>
                <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($license['contact']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mac_address">MAC Address:</label>
                <input type="text" name="mac_address" id="mac_address" value="<?php echo htmlspecialchars($license['mac_address']); ?>" required>
            </div>
            <div class="button-group">
                <button type="submit">Update License</button>
                <button type="button" onclick="window.location.href='license.html'">Back to Main Page</button>
            </div>
        </form>

        <?php if ($message): ?>
            <script>
                alert("<?php echo $message; ?>");
                <?php if ($success): ?>
                        window.location.href = 'license.html';
                <?php endif; ?>
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
