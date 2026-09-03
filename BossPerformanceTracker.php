<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

$search = "";

if(isset($_GET['engineer_search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['engineer_search']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Boss Performance Tracker</title>

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
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

.card h2{
    color:#003366;
    margin-top:0;
}

.search-box{
    display:flex;
    gap:10px;
    margin-bottom:25px;
}

.search-box input{
    flex:1;
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

.reset{
    background:#6c757d;
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
    background:#004aad;
    color:white;
    padding:6px 12px;
    border-radius:15px;
    font-weight:bold;
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
    <a href="BossAssignEngineer.php">Assign Engineers</a>
    <a href="BossEngineerReport.php">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php">Performance Reports</a>
    <a href="BossPerformanceTracker.php" class="active">Performance Tracker</a>
</div>

<div class="content">

<div class="card">

<h2>Engineer Performance Tracker</h2>

<form method="GET" class="search-box">
    <input
    type="text"
    name="engineer_search"
    placeholder="Search engineer name"
    value="<?php echo htmlspecialchars($search); ?>">

    <button class="btn" type="submit">Search</button>

    <a href="BossPerformanceTracker.php" class="btn reset">Reset</a>
</form>

<table>
<tr>
<th>Engineer ID</th>
<th>Engineer Name</th>
<th>Position</th>
<th>Assigned Projects</th>
<th>Completed Projects</th>
<th>Total Hours Logged</th>
<th>Latest Progress</th>
</tr>

<?php

$query = mysqli_query($conn,

"SELECT 
    s.StaffID,
    s.Username,
    s.Position,

    (
        SELECT COUNT(DISTINCT pa.ProjectID)
        FROM project_assignment pa
        WHERE pa.StaffID = s.StaffID
    ) AS AssignedProjects,

    (
        SELECT COUNT(DISTINCT p.ProjectID)
        FROM project p
        WHERE p.StaffID = s.StaffID
        AND p.Status = 'Completed'
    ) AS CompletedProjects,

    (
        SELECT COALESCE(SUM(wh.HoursWorked),0)
        FROM working_hours wh
        WHERE wh.StaffID = s.StaffID
    ) AS TotalHours,

    (
        SELECT COALESCE(MAX(pp.ProgressPercentage),0)
        FROM project_progress pp
        WHERE pp.StaffID = s.StaffID
    ) AS LatestProgress

FROM staff s

WHERE s.Role = 'Engineer'
AND s.Username LIKE '%$search%'

ORDER BY s.Username ASC");

if(mysqli_num_rows($query) > 0)
{
    while($row = mysqli_fetch_assoc($query))
    {
?>

<tr>
<td><?php echo $row['StaffID']; ?></td>

<td><b><?php echo htmlspecialchars($row['Username']); ?></b></td>

<td><?php echo htmlspecialchars($row['Position']); ?></td>

<td>
<span class="badge">
<?php echo $row['AssignedProjects']; ?>
</span>
</td>

<td>
<span class="badge">
<?php echo $row['CompletedProjects']; ?>
</span>
</td>

<td>
<?php echo $row['TotalHours']; ?> hour(s)
</td>

<td>
<?php echo $row['LatestProgress']; ?>%
</td>
</tr>

<?php
    }
}
else
{
?>

<tr>
<td colspan="7" style="text-align:center;">
No engineer found.
</td>
</tr>

<?php
}
?>

</table>

</div>

</div>

</div>

</body>
</html>