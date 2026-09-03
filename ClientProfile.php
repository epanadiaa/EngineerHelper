<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Client') {
    header("Location: UserLogin.php");
    exit();
}

$client_id = $_SESSION['clientID'];
$message = "";

// Handle form submission
if (isset($_POST['update_profile'])) {
    $pic = mysqli_real_escape_string($conn, $_POST['person_in_charge']);
    $company = mysqli_real_escape_string($conn, $_POST['company_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_num']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $updateSql = "UPDATE client SET 
                    PersonInCharge = '$pic', 
                    ClientCompany = '$company', 
                    ClientPhoneNum = '$phone', 
                    ClientEmail = '$email', 
                    ClientAddress = '$address' 
                  WHERE ClientID = '$client_id'";

    if (mysqli_query($conn, $updateSql)) {
        $message = "<div class='alert success'>Profile details adjusted successfully!</div>";
    } else {
        $message = "<div class='alert error'>Update Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch current details
$info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM client WHERE ClientID = '$client_id'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Corporate Profile - Client Portal</title>
    <style>
        body { background: #002b5c; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        h2 { color: #003366; margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #003366; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        textarea { height: 90px; }
        .btn { background: #004aad; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 8px; cursor: pointer; text-transform: uppercase; }
        .btn:hover { background: #003366; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
        .back-btn { display: inline-block; margin-bottom: 20px; color: white; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="ClientDashboard.php" class="back-btn">← Back to Dashboard</a>
        <div class="card">
            <h2>Update Corporate Directory Details</h2>
            <?php echo $message; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Company/Agency Full Legal Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($info['ClientCompany']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Appointed Person In Charge (PIC)</label>
                    <input type="text" name="person_in_charge" value="<?php echo htmlspecialchars($info['PersonInCharge']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Corporate Contact Phone Number</label>
                    <input type="text" name="phone_num" value="<?php echo htmlspecialchars($info['ClientPhoneNum']); ?>">
                </div>
                <div class="form-group">
                    <label>Official Email Endpoint</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($info['ClientEmail']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Registered Main Corporate Address</label>
                    <textarea name="address" required><?php echo htmlspecialchars($info['ClientAddress']); ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn">Commit Changes</button>
            </form>
        </div>
    </div>
</body>
</html>