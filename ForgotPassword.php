<?php
include 'config.php';

$message = "";

if(isset($_POST['reset']))
{
    $username = $_POST['username'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if($newPassword != $confirmPassword)
    {
        $message = "Passwords do not match.";
    }
    else
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // STAFF
        $staffCheck = mysqli_query($conn,
        "SELECT * FROM staff WHERE Username='$username'");

        if(mysqli_num_rows($staffCheck) > 0)
        {
            mysqli_query($conn,
            "UPDATE staff
            SET Password='$hashedPassword'
            WHERE Username='$username'");

            $message = "Password reset successfully!";
        }
        else
        {
            // CLIENT
            $clientCheck = mysqli_query($conn,
            "SELECT * FROM client_account
            WHERE Username='$username'");

            if(mysqli_num_rows($clientCheck) > 0)
            {
                mysqli_query($conn,
                "UPDATE client_account
                SET Password='$hashedPassword'
                WHERE Username='$username'");

                $message = "Password reset successfully!";
            }
            else
            {
                $message = "Username not found.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="Auth_style.css">
</head>

<body>

<div class="container">

<div class="left-panel">
    <h1>Engineer Helper</h1>
    <p>Reset your password to regain access to the system.</p>
</div>

<div class="right-panel">

    <h2>Forgot Password</h2>

    <div class="message">
        <?php echo $message; ?>
    </div>

    <form method="POST">

        <div class="input-group">
            <input type="text"
                   name="username"
                   placeholder="Username"
                   required>
        </div>

        <div class="input-group">
            <input type="password"
                   name="newPassword"
                   placeholder="New Password"
                   required>
        </div>

        <div class="input-group">
            <input type="password"
                   name="confirmPassword"
                   placeholder="Confirm Password"
                   required>
        </div>

        <button class="btn"
                type="submit"
                name="reset">
            Reset Password
        </button>

    </form>

    <div class="auth-links">
        <a href="UserLogin.php">
            Back to Login
        </a>
    </div>

</div>
</div>

</body>
</html>