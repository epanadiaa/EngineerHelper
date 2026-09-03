<?php
include 'config.php';

$message = "";

if(isset($_POST['register']))
{
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    /* CHECK DUPLICATE USERNAME */

    $checkUsername = mysqli_query($conn,
    "SELECT * FROM staff
    WHERE Username='$username'");

    if(mysqli_num_rows($checkUsername) > 0)
    {
        $message = "<div class='error-message'>
                    Username already exists.
                    </div>";
    }
    else
    {
        /* CHECK DUPLICATE EMAIL */

        $checkEmail = mysqli_query($conn,
        "SELECT * FROM staff
        WHERE Email='$email'");

        if(mysqli_num_rows($checkEmail) > 0)
        {
            $message = "<div class='error-message'>
                        Email already registered.
                        </div>";
        }
        else
        {
            $sql = "INSERT INTO staff
            (
                Username,
                Role,
                Position,
                Email,
                Password
            )
            VALUES
            (
                '$username',
                '$role',
                '$position',
                '$email',
                '$password'
            )";

            if(mysqli_query($conn, $sql))
            {
                echo "
                <script>
                    alert('Staff Registered Successfully!');
                    window.location='UserLogin.php';
                </script>
                ";
                exit();
            }
            else
            {
                $message = "<div class='error-message'>
                            Registration Failed:
                            ".mysqli_error($conn)."
                            </div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Staff Registration</title>

<link rel="stylesheet" href="Auth_style.css">

<style>

.success-message{
    background:#d4edda;
    color:#155724;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
    font-weight:bold;
}

.error-message{
    background:#f8d7da;
    color:#721c24;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="container">

    <div class="left-panel">

        <h1>Engineer Helper</h1>

        <p>
            Create a staff account for Admin, Engineer or Boss.
        </p>

    </div>

    <div class="right-panel">

        <h2>Staff Registration</h2>

        <?php echo $message; ?>

        <form method="POST">

            <div class="input-group">
                <input
                type="text"
                name="username"
                placeholder="Username"
                required>
            </div>

            <div class="input-group">

                <select name="role" required>

                    <option value="">Select Role</option>

                    <option value="Admin">
                        Admin
                    </option>

                    <option value="Engineer">
                        Engineer
                    </option>

                    <option value="Boss">
                        Boss
                    </option>

                </select>

            </div>

            <div class="input-group">

                <input
                type="text"
                name="position"
                placeholder="Position"
                required>

            </div>

            <div class="input-group">

                <input
                type="email"
                name="email"
                placeholder="Email Address"
                required>

            </div>

            <div class="input-group password-container">

                <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required>

                <span
                id="togglePassword"
                class="eye-icon">
                👁️
                </span>

            </div>

            <button
            class="btn"
            type="submit"
            name="register">

            Register Staff

            </button>

        </form>

        <div class="link">

            <a href="UserLogin.php">
                Back to Login
            </a>

        </div>

    </div>

</div>

<script>

const togglePassword =
document.querySelector('#togglePassword');

const password =
document.querySelector('#password');

togglePassword.addEventListener('click', function ()
{
    const type =
    password.getAttribute('type') === 'password'
    ? 'text'
    : 'password';

    password.setAttribute('type', type);

    this.textContent =
    type === 'password'
    ? '👁️'
    : '🙈';
});

</script>

</body>
</html>