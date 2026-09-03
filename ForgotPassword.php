<?php
include 'config.php';

$message = "";

if(isset($_POST['reset']))
{
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if($newPassword != $confirmPassword)
    {
        $message = "Passwords do not match.";
    }
    elseif(strlen($newPassword) < 8)
    {
        $message = "Password must be at least 8 characters.";
    }
    else
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $resetDone = false;

        // STAFF - username AND registered email must both match
        $stmt = $conn->prepare("SELECT StaffID FROM staff WHERE Username = ? AND Email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $staffResult = $stmt->get_result();

        if($staffResult->fetch_assoc())
        {
            $update = $conn->prepare("UPDATE staff SET Password = ? WHERE Username = ?");
            $update->bind_param('ss', $hashedPassword, $username);
            $update->execute();
            $resetDone = true;
        }
        $stmt->close();

        // CLIENT - username AND the email on the linked client record must both match
        if(!$resetDone)
        {
            $stmt = $conn->prepare(
                "SELECT ca.ClientAccountID FROM client_account ca
                 JOIN client c ON c.ClientID = ca.ClientID
                 WHERE ca.Username = ? AND c.ClientEmail = ?"
            );
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $clientResult = $stmt->get_result();

            if($clientResult->fetch_assoc())
            {
                $update = $conn->prepare("UPDATE client_account SET Password = ? WHERE Username = ?");
                $update->bind_param('ss', $hashedPassword, $username);
                $update->execute();
                $resetDone = true;
            }
            $stmt->close();
        }

        // Same message either way - don't reveal whether the username/email exists
        $message = $resetDone
            ? "Password reset successfully!"
            : "We couldn't verify those details. Please check your username and registered email.";
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
            <input type="email"
                   name="email"
                   placeholder="Registered Email"
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