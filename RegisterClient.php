<?php
include 'config.php';

$message = "";

if(isset($_POST['register']))
{
    $person = trim($_POST['person']);
    $company = trim($_POST['company']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    /* =========================
       CHECK USERNAME EXISTS
    ========================== */
    $stmt = $conn->prepare("SELECT 1 FROM client_account WHERE Username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0)
    {
        $message = "Username already exists.";
        $stmt->close();
    }
    else
    {
        $stmt->close();

        /* =========================
           CHECK EMAIL EXISTS
        ========================== */
        $stmt = $conn->prepare("SELECT 1 FROM client WHERE ClientEmail = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0)
        {
            $message = "Email already registered.";
            $stmt->close();
        }
        else
        {
            $stmt->close();

            /* =========================
               INSERT CLIENT INFO
            ========================== */
            $stmt = $conn->prepare("
                INSERT INTO client 
                (PersonInCharge, ClientCompany, ClientPhoneNum, ClientAddress, ClientEmail)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('sssss', $person, $company, $phone, $address, $email);
            $stmt->execute();

            $clientID = $conn->insert_id;
            $stmt->close();

            /* =========================
               INSERT CLIENT ACCOUNT
            ========================== */
            $stmt = $conn->prepare("
                INSERT INTO client_account (ClientID, Username, Password)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param('iss', $clientID, $username, $password);
            $stmt->execute();

            $stmt->close();

            $message = "Registration Successful!";
        }
    }
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

<?php if(!empty($message)): ?>
<div class="message">
<?php echo $message; ?>
</div>
<?php endif; ?>

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

<div class="input-group password-container">
<input type="password" name="password" id="password" placeholder="Password" required>
<span id="togglePassword" class="eye-icon">👁️</span>
</div>

<button class="btn" type="submit" name="register">
Register
</button>

</form>

<div class="link">
<a href="UserLogin.php">Back to Login</a>
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