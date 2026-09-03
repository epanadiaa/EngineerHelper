<?php
include 'config.php';

$message="";

if(isset($_POST['register']))
{
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $position = trim($_POST['position']);
    $email = trim($_POST['email']);

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO staff (Username, Role, Position, Email, Password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $username, $role, $position, $email, $password);

    if($stmt->execute())
    {
        $message = "Staff Registered Successfully";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Registration</title>
<link rel="stylesheet" href="Auth_style.css">
</head>

<body>

<div class="container">

<div class="left-panel">
<h1>Engineer Helper</h1>
<p>Create a staff account for Admin, Engineer or Boss.</p>
</div>

<div class="right-panel">

<h2>Staff Registration</h2>

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
<select name="role">

<option>Admin</option>
<option>Engineer</option>
<option>Boss</option>

</select>
</div>

<div class="input-group">
<input type="text"
name="position"
placeholder="Position">
</div>

<div class="input-group">
<input type="email"
name="email"
placeholder="Email"
required>
</div>

<div class="input-group">
<input type="password"
name="password"
placeholder="Password"
required>
</div>

<button class="btn"
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

</body>
</html>