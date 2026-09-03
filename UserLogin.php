<?php
session_start();
include 'config.php';

$message = "";

if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Matches the lowercase name="password" in HTML form

    /* =======================
       STAFF LOGIN CHECK
    ======================== */
    $stmt = $conn->prepare("SELECT StaffID, Username, Password, Role FROM staff WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $staffResult = $stmt->get_result();

    if($row = $staffResult->fetch_assoc())
    {
        // $row['Password'] must match the exact case from your database column
        if(password_verify($password, $row['Password']))
        {
            $_SESSION['id'] = $row['StaffID'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = $row['Role'];

            if($row['Role'] == "Admin")
                header("Location: DashboardAdmin.php");
            elseif($row['Role'] == "Engineer")
                header("Location: DashboardEngineer.php");
            elseif($row['Role'] == "Boss" || $row['Role'] == "Manager")
                header("Location: BossDashboard.php");
            else
                header("Location: BossDashboard.php");

            exit();
        }
    }

    $stmt->close();

    /* =======================
       CLIENT LOGIN CHECK
    ======================== */
    // Selects structural keys directly from your client_account schema mapping
    $stmt = $conn->prepare("SELECT ClientID, Username, Password FROM client_account WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $clientResult = $stmt->get_result();

    if($row = $clientResult->fetch_assoc())
    {
        // Validates hashed input against your VARCHAR(255) column
        if(password_verify($password, $row['Password']))
        {
            $_SESSION['clientID'] = $row['ClientID']; // Corrected to uppercase ID to match client views
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = 'Client';             // Prevents security gate components from redirecting you out

            header("Location: ClientDashboard.php");  // Routes to your custom client dashboard layout
            exit();
        }
    }

    $stmt->close();

    $message = "Invalid Username or Password";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Engineer Helper Login</title>
    <link rel="stylesheet" href="Auth_style.css">
</head>

<body>

<div class="container">

    <div class="left-panel">
        <h1>Engineer Helper</h1>
        <p>Engineering Project Management System</p>
    </div>

    <div class="right-panel">

        <h2>Login</h2>

        <?php if(!empty($message)): ?>
            <div class="message" style="color: red; font-weight: bold; margin-bottom: 15px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group password-container">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span id="togglePassword" class="eye-icon">👁️</span>
            </div>

            <button class="btn" type="submit" name="login">
                Login
            </button>

        </form>

        <div class="auth-links">
            <a href="RegisterClient.php">Register as Client</a>
            <a href="RegisterStaff.php">Register as Staff</a>
            <a href="ForgotPassword.php">Forgot Password?</a>
        </div>

    </div>
</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.textContent = type === 'password' ? '👁️' : '🙈';
});
</script>

</body>
</html>