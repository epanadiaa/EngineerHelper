<?php
session_start();
include 'config.php';

if(!isset($_SESSION['clientID']))
{
    header("Location: UserLogin.php");
    exit();
}

$clientID = $_SESSION['clientID'];
$username = $_SESSION['username'];

/* CLIENT INFO */
$clientQuery = mysqli_query($conn,
"SELECT * FROM client
INNER JOIN client_account
ON client.ClientID = client_account.ClientID
WHERE client.ClientID = '$clientID'");

$client = mysqli_fetch_assoc($clientQuery);

/* TOTAL PROJECTS */
$totalProjects = mysqli_query($conn,
"SELECT COUNT(*) AS total
FROM project
WHERE ClientID='$clientID'");

$totalProjects = mysqli_fetch_assoc($totalProjects);

/* COMPLETED PROJECTS */
$completedProjects = mysqli_query($conn,
"SELECT COUNT(*) AS total
FROM project
WHERE ClientID='$clientID'
AND Status='Completed'");

$completedProjects = mysqli_fetch_assoc($completedProjects);

/* PENDING PROJECTS */
$pendingProjects = mysqli_query($conn,
"SELECT COUNT(*) AS total
FROM project
WHERE ClientID='$clientID'
AND Status!='Completed'");

$pendingProjects = mysqli_fetch_assoc($pendingProjects);

/* RECENT PROJECTS */
$projectList = mysqli_query($conn,
"SELECT *
FROM project
WHERE ClientID='$clientID'
ORDER BY ProjectID DESC");
?>

<!DOCTYPE html>
<html>

<head>

<title>Client Dashboard</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',Arial,sans-serif;
}

body{
display:flex;
background:#f4f6fa;
}

/* SIDEBAR */

.sidebar{
width:250px;
height:100vh;
background:#002b66;
color:white;
position:fixed;
left:0;
top:0;
padding:20px;
}

.logo{
font-size:28px;
font-weight:bold;
margin-bottom:40px;
}

.sidebar ul{
list-style:none;
}

.sidebar ul li{
margin:20px 0;
}

.sidebar ul li a{
color:white;
text-decoration:none;
font-size:1.15rem;
font-weight:800;
text-transform:uppercase;
display:block;
padding:10px;
border-radius:10px;
transition:0.3s;
}

.sidebar ul li a:hover{
background:#004aad;
}

/* MAIN */

.main{
margin-left:250px;
padding:30px;
width:100%;
}

.header{
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
margin-bottom:25px;
}

.header h1{
color:#002b66;
}

.header p{
color:gray;
}

/* CARDS */

.cards{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;
margin-bottom:30px;
}

.card{
background:white;
padding:25px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.card h3{
color:#002b66;
margin-bottom:10px;
}

.card .number{
font-size:32px;
font-weight:bold;
color:#004aad;
}

/* TABLE */

.table-container{
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

table{
width:100%;
border-collapse:collapse;
}

table th{
background:#002b66;
color:white;
padding:12px;
}

table td{
padding:12px;
border-bottom:1px solid #ddd;
}

.status{
padding:5px 10px;
border-radius:10px;
font-size:13px;
font-weight:600;
}

.completed{
background:#d4edda;
color:#155724;
}

.pending{
background:#fff3cd;
color:#856404;
}

.inprogress{
background:#cce5ff;
color:#004085;
}

.view-btn{
background:#004aad;
color:white;
padding:8px 12px;
text-decoration:none;
border-radius:8px;
}

.view-btn:hover{
background:#003080;
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

<div class="logo">
Engineer Helper
</div>

<ul>

<li>
<a href="ClientDashboard.php">
 Dashboard
</a>
</li>

<li>
<a href="ClientProjectDetails.php">
 Project Details
</a>
</li>

<li>
<a href="ClientUploadBOQ.php">
 Upload BOQ
</a>
</li>

<li>
<a href="ClientDownloadReport.php">
 Download Report
</a>
</li>

<li>
<a href="ClientProfile.php">
 Profile
</a>
</li>

<li>
<a href="LogOut.php">
 Logout
</a>
</li>

</ul>

</div>

<!-- MAIN -->

<div class="main">

<div class="header">

<h1>
Welcome, <?php echo $client['PersonInCharge']; ?>
</h1>

<p>
Company: <?php echo $client['ClientCompany']; ?>
</p>

</div>

<!-- CARDS -->

<div class="cards">

<div class="card">
<h3>Total Projects</h3>
<div class="number">
<?php echo $totalProjects['total']; ?>
</div>
</div>

<div class="card">
<h3>Completed Projects</h3>
<div class="number">
<?php echo $completedProjects['total']; ?>
</div>
</div>

<div class="card">
<h3>Pending Projects</h3>
<div class="number">
<?php echo $pendingProjects['total']; ?>
</div>
</div>

</div>

<!-- PROJECT TABLE -->

<div class="table-container">

<h2 style="margin-bottom:20px;color:#002b66;">
Recent Projects
</h2>

<table>

<tr>
<th>Project Name</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php
while($row=mysqli_fetch_assoc($projectList))
{
$statusClass="pending";

if($row['Status']=="Completed")
{
$statusClass="completed";
}
elseif($row['Status']=="In Progress")
{
$statusClass="inprogress";
}
?>

<tr>

<td><?php echo $row['ProjectName']; ?></td>

<td><?php echo $row['StartDate']; ?></td>

<td><?php echo $row['EndDate']; ?></td>

<td>
<span class="status <?php echo $statusClass; ?>">
<?php echo $row['Status']; ?>
</span>
</td>

<td>
<a class="view-btn"
href="ClientProjectDetails.php?ProjectID=<?php echo $row['ProjectID']; ?>">
View
</a>
</td>

</tr>

<?php
}
?>

</table>

</div>

</div>

</body>
</html>