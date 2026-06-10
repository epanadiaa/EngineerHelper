<?php
include 'config.php';

$message="";

if(isset($_POST['register']))
{
    $person=$_POST['person'];
    $company=$_POST['company'];
    $phone=$_POST['phone'];
    $address=$_POST['address'];
    $email=$_POST['email'];

    $username=$_POST['username'];
    $password=password_hash($_POST['password'],PASSWORD_DEFAULT);

    mysqli_query($conn,
    "INSERT INTO client
    (PersonInCharge,ClientCompany,
    ClientPhoneNum,ClientAddress,ClientEmail)

    VALUES
    ('$person','$company','$phone',
    '$address','$email')");

    $clientID=mysqli_insert_id($conn);

    mysqli_query($conn,
    "INSERT INTO client_account
    (ClientID,Username,Password)

    VALUES
    ('$clientID','$username','$password')");

    $message="Registration Successful";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Client Registration</title>
<link rel="stylesheet" href="Auth_style.css">
</head>

<body>

<div class="container">

<div class="left-panel">
<h1>Engineer Helper</h1>
<p>Create your client account.</p>
</div>

<div class="right-panel">

<h2>Client Registration</h2>

<div class="message">
<?php echo $message; ?>
</div>

<form method="POST">

<div class="input-group">
<input type="text" name="person" placeholder="Person In Charge" required>
</div>

<div class="input-group">
<input type="text" name="company" placeholder="Company Name" required>
</div>

<div class="input-group">
<input type="text" name="phone" placeholder="Phone Number">
</div>

<div class="input-group">
<textarea name="address" placeholder="Address"></textarea>
</div>

<div class="input-group">
<input type="email" name="email" placeholder="Email" required>
</div>

<div class="input-group">
<input type="text" name="username" placeholder="Username" required>
</div>

<div class="input-group">
<input type="password" name="password" placeholder="Password" required>
</div>

<button class="btn" type="submit" name="register">
Register
</button>

</form>

<div class="link">
<a href="login.php">Back to Login</a>
</div>

</div>
</div>

</body>
</html>