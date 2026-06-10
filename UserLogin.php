<?php
session_start();
include 'config.php';

$message="";

if(isset($_POST['login']))
{
    $username=$_POST['username'];
    $password=$_POST['password'];

    $staff=mysqli_query($conn,
    "SELECT * FROM staff WHERE Username='$username'");

    if(mysqli_num_rows($staff)==1)
    {
        $row=mysqli_fetch_assoc($staff);

        if(password_verify($password,$row['Password']))
        {
            $_SESSION['id']=$row['StaffID'];
            $_SESSION['username']=$row['Username'];
            $_SESSION['role']=$row['Role'];

            if($row['Role']=="Admin")
                header("Location: admin_dashboard.php");

            elseif($row['Role']=="Engineer")
                header("Location: engineer_dashboard.php");

            else
                header("Location: boss_dashboard.php");

            exit();
        }
    }

    $client=mysqli_query($conn,
    "SELECT * FROM client_account WHERE Username='$username'");

    if(mysqli_num_rows($client)==1)
    {
        $row=mysqli_fetch_assoc($client);

        if(password_verify($password,$row['Password']))
        {
            $_SESSION['clientID']=$row['ClientID'];
            $_SESSION['username']=$row['Username'];

            header("Location: client_dashboard.php");
            exit();
        }
    }

    $message="Invalid Username or Password";
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
<p>
Engineering Project Management System
</p>
</div>

<div class="right-panel">

    <h2>Login</h2>

    <div class="message">
        <?php echo $message; ?>
    </div>

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

    </form> <div class="auth-links">
        <a href="RegisterClient.php">Register as Client</a>
        <a href="RegisterStaff.php">Register as Staff</a>
        <a href="ForgotPassword.php">Forgot Password?</a>
    </div>

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