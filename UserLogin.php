<?php
session_start();
include 'config.php';

$message = "";

if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    /* =======================
       STAFF LOGIN CHECK
    ======================== */
    $stmt = $conn->prepare("SELECT StaffID, Username, Password, Role FROM staff WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $staffResult = $stmt->get_result();

    if($row = $staffResult->fetch_assoc())
    {
        if(password_verify($password, $row['Password']))
        {
            $_SESSION['id'] = $row['StaffID'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = $row['Role'];

            if($row['Role'] == "Admin")
                header("Location: admin_dashboard.php");
            elseif($row['Role'] == "Engineer")
                header("Location: engineer_dashboard.php");
            else
                header("Location: boss_dashboard.php");

            exit();
        }
    }

    $stmt->close();

    /* =======================
       CLIENT LOGIN CHECK
    ======================== */
    $stmt = $conn->prepare("SELECT ClientID, Username, Password FROM client_account WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $clientResult = $stmt->get_result();

    if($row = $clientResult->fetch_assoc())
    {
        if(password_verify($password, $row['Password']))
        {
            $_SESSION['clientID'] = $row['ClientID'];
            $_SESSION['username'] = $row['Username'];

            header("Location: ClientDashboard.php");
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
            <div class="message">
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