<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

$message = "";

if (isset($_POST['assign_project'])) {

    $projectID = mysqli_real_escape_string($conn, $_POST['project_id']);
    $staffID = mysqli_real_escape_string($conn, $_POST['staff_id']);

    if (!empty($projectID) && !empty($staffID)) {

        mysqli_query($conn,
        "UPDATE project
         SET StaffID='$staffID',
             Status='In Progress'
         WHERE ProjectID='$projectID'");

        $checkAssign = mysqli_query($conn,
        "SELECT *
         FROM project_assignment
         WHERE ProjectID='$projectID'
         AND StaffID='$staffID'");

        if (mysqli_num_rows($checkAssign) == 0) {
            mysqli_query($conn,
            "INSERT INTO project_assignment
            (AssignedStartDate, AssignedEndDate, WorkingHoursPerWeek, StaffID, ProjectID)
            VALUES
            (CURDATE(), NULL, 0, '$staffID', '$projectID')");
        }

        $message = "<div class='alert success'>Engineer assigned successfully.</div>";
    } else {
        $message = "<div class='alert error'>Please select both project and engineer.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Boss Assign Engineer</title>

<style>
body{
    background:#002b5c;
    margin:0;
    font-family:'Segoe UI',Arial,sans-serif;
    height:100vh;
    overflow:hidden;
}

.header{
    height:70px;
    background:rgba(0,0,0,0.2);
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 35px;
}

.header h1{
    margin:0;
    font-size:34px;
}

.header a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.layout{
    display:flex;
    height:calc(100vh - 70px);
}

.sidebar{
    width:220px;
    background:white;
    padding:30px 20px;
}

.sidebar h3{
    color:#003366;
    margin-bottom:10px;
}

.sidebar h2{
    color:#003366;
    font-size:28px;
    margin-bottom:35px;
}

.sidebar a{
    display:block;
    text-decoration:none;
    color:#c7c7c7;
    font-weight:bold;
    margin-bottom:22px;
    text-transform:uppercase;
}

.sidebar a.active,
.sidebar a:hover{
    color:#003366;
}

.content{
    flex:1;
    padding:35px;
    overflow:auto;
}

.card{
    background:white;
    border-radius:35px;
    padding:35px;
    margin-bottom:30px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

.card h2{
    color:#003366;
    margin-top:0;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    font-weight:bold;
    color:#003366;
    margin-bottom:8px;
}

select{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    font-size:16px;
}

.btn{
    background:#004aad;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    text-decoration:none;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#003366;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #ddd;
}

.badge{
    padding:6px 12px;
    border-radius:15px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.approved{ background:#28a745; }
.progress{ background:#007bff; }
.completed{ background:#6f42c1; }
.rejected{ background:#dc3545; }
.pending{ background:#ff9800; }
.unassigned{ background:#dc3545; }

.alert{
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    font-weight:bold;
}

.alert.success{
    background:#d4edda;
    color:#155724;
}

.alert.error{
    background:#f8d7da;
    color:#721c24;
}
</style>
</head>

<body>

<div class="header">
    <h1>Engineer Helper</h1>
    <a href="LogOut.php">LOG OUT</a>
</div>

<div class="layout">

<div class="sidebar">
    <h3>Management Portal</h3>
    <h2>Boss Control</h2>

    <a href="BossDashboard.php">Boss Dashboard</a>
    <a href="BossClientProposal.php">Client Proposals</a>
    <a href="BossAssignEngineer.php" class="active">Assign Engineers</a>
    <a href="BossEngineerReport.php">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php">Performance Reports</a>
    <a href="BossPerformanceTracker.php">Performance Tracker</a>
</div>

<div class="content">

<?php echo $message; ?>

<div class="card">
<h2>Assign Engineer to Approved Project</h2>

<form method="POST">

<div class="form-group">
<label>Select Approved Project</label>

<select name="project_id" required>
<option value="">-- Choose Project --</option>

<?php
$projectQuery = mysqli_query($conn,
"SELECT ProjectID, ProjectName
 FROM project
 WHERE Status='Approved'
 AND (StaffID IS NULL OR StaffID = 0)
 ORDER BY ProjectID DESC");

while($project = mysqli_fetch_assoc($projectQuery)) {
    echo "<option value='".$project['ProjectID']."'>[ID: ".$project['ProjectID']."] ".$project['ProjectName']."</option>";
}
?>

</select>
</div>

<div class="form-group">
<label>Select Engineer</label>

<select name="staff_id" required>
<option value="">-- Choose Engineer --</option>

<?php
$engineerQuery = mysqli_query($conn,
"SELECT StaffID, Username, Position
 FROM staff
 WHERE Role='Engineer'
 ORDER BY Username ASC");

while($engineer = mysqli_fetch_assoc($engineerQuery)) {
    echo "<option value='".$engineer['StaffID']."'>".$engineer['Username']." - ".$engineer['Position']."</option>";
}
?>

</select>
</div>

<button type="submit" name="assign_project" class="btn">
Confirm Assignment
</button>

</form>
</div>

<div class="card">
<h2>Current Project Assignments</h2>

<table>
<tr>
<th>Project ID</th>
<th>Project Name</th>
<th>Client</th>
<th>Assigned Engineer</th>
<th>Status</th>
</tr>

<?php
$assignmentQuery = mysqli_query($conn,
"SELECT
    p.ProjectID,
    p.ProjectName,
    p.Status,
    c.ClientCompany,
    s.Username
 FROM project p
 LEFT JOIN client c
 ON p.ClientID = c.ClientID
 LEFT JOIN staff s
 ON p.StaffID = s.StaffID
 ORDER BY p.ProjectID DESC");

while($row = mysqli_fetch_assoc($assignmentQuery)) {

    $status = $row['Status'];
    $class = "pending";

    if($status == "Approved") $class = "approved";
    elseif($status == "Rejected") $class = "rejected";
    elseif($status == "In Progress") $class = "progress";
    elseif($status == "Completed") $class = "completed";
?>

<tr>
<td><?php echo $row['ProjectID']; ?></td>
<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>
<td><?php echo htmlspecialchars($row['ClientCompany']); ?></td>

<td>
<?php
if($row['Username']) {
    echo htmlspecialchars($row['Username']);
} else {
    echo "<span class='badge unassigned'>Unassigned</span>";
}
?>
</td>

<td>
<span class="badge <?php echo $class; ?>">
<?php echo $row['Status']; ?>
</span>
</td>
</tr>

<?php } ?>

</table>
</div>

</div>

</div>

</body>
</html>