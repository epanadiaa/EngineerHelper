<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Engineer') {
    header("Location: UserLogin.php");
    exit();
}

$username = $_SESSION['username'];

$staffQuery = mysqli_query($conn,
"SELECT * FROM staff WHERE Username='$username'");
$staff = mysqli_fetch_assoc($staffQuery);
$staffID = $staff['StaffID'];

/* SUMMARY */
$totalProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(DISTINCT p.ProjectID) AS total
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE pa.StaffID='$staffID' OR p.StaffID='$staffID'"))['total'];

$completedProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(DISTINCT p.ProjectID) AS total
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE (pa.StaffID='$staffID' OR p.StaffID='$staffID')
AND p.Status='Completed'"))['total'];

$inProgressProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(DISTINCT p.ProjectID) AS total
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE (pa.StaffID='$staffID' OR p.StaffID='$staffID')
AND p.Status='In Progress'"))['total'];

$totalHours = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COALESCE(SUM(HoursWorked),0) AS total
FROM working_hours
WHERE StaffID='$staffID'"))['total'];

/* NEW ASSIGNED PROJECTS */
$newProjects = mysqli_query($conn,
"SELECT DISTINCT p.ProjectName
FROM project p
INNER JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE pa.StaffID='$staffID'
AND pa.AssignedStartDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");

$newProjectNames = [];
while($n = mysqli_fetch_assoc($newProjects)) {
    $newProjectNames[] = $n['ProjectName'];
}

/* PROJECT TABLE */
$projects = mysqli_query($conn,
"SELECT
    p.ProjectID,
    p.ProjectName,
    p.Status,
    p.EndDate,
    pa.AssignedStartDate,
    COALESCE(SUM(wh.HoursWorked),0) AS TotalHours,
    COALESCE((
        SELECT MAX(pp.ProgressPercentage)
        FROM project_progress pp
        WHERE pp.ProjectID = p.ProjectID
        AND pp.StaffID = '$staffID'
    ),0) AS LatestProgress
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
LEFT JOIN working_hours wh ON p.ProjectID = wh.ProjectID AND wh.StaffID='$staffID'
WHERE pa.StaffID='$staffID' OR p.StaffID='$staffID'
GROUP BY p.ProjectID, p.ProjectName, p.Status, p.EndDate, pa.AssignedStartDate
ORDER BY p.ProjectID DESC");

/* DEADLINE ALERTS - this engineer's projects only */
$overdueProjects = mysqli_query($conn,
"SELECT DISTINCT p.ProjectID, p.ProjectName, p.EndDate,
    DATEDIFF(CURDATE(), p.EndDate) AS DaysOverdue
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE (pa.StaffID='$staffID' OR p.StaffID='$staffID')
AND p.EndDate IS NOT NULL
AND p.EndDate < CURDATE()
AND p.Status <> 'Completed'
ORDER BY p.EndDate ASC");

$dueSoonProjects = mysqli_query($conn,
"SELECT DISTINCT p.ProjectID, p.ProjectName, p.EndDate,
    DATEDIFF(p.EndDate, CURDATE()) AS DaysLeft
FROM project p
LEFT JOIN project_assignment pa ON p.ProjectID = pa.ProjectID
WHERE (pa.StaffID='$staffID' OR p.StaffID='$staffID')
AND p.EndDate IS NOT NULL
AND p.EndDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
AND p.Status <> 'Completed'
ORDER BY p.EndDate ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Engineer Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background:#002b5c;
    margin:0;
    font-family:'Segoe UI',Arial,sans-serif;
    display:flex;
    flex-direction:column;
    min-height:100vh;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    background:rgba(0,0,0,0.2);
    color:white;
}

.header h1{
    margin:0;
    font-size:2.2rem;
}

.nav-links a{
    color:white;
    text-decoration:none;
    font-weight:bold;
    margin-left:25px;
}

.layout-container{
    display:flex;
    flex:1;
    overflow:hidden;
}

.sidebar{
    width:280px;
    background:white;
    padding:30px 25px;
    box-sizing:border-box;
}

.sidebar h2{
    color:#003366;
    font-size:1.25rem;
    margin-bottom:20px;
    font-weight:bold;
}

.page-title{
    color:#003366;
    font-size:1.6rem;
    font-weight:900;
    margin-bottom:40px;
    text-transform:uppercase;
}

.sidebar ul{
    list-style:none;
    padding:0;
}

.sidebar li{
    font-size:1.15rem;
    font-weight:800;
    margin-bottom:45px;
    text-transform:uppercase;
    color:#d1d1d1;
}

.sidebar li.active,
.sidebar li:hover{
    color:#003366;
}

.sidebar li a{
    text-decoration:none;
    color:inherit;
}

.content-area{
    flex:1;
    padding:40px;
    overflow:auto;
}

.card{
    background:white;
    border-radius:40px;
    padding:35px;
    margin-bottom:25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

.card h2{
    color:#003366;
    margin-top:0;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.stat-box{
    background:#f8f9fa;
    border-left:6px solid #003366;
    border-radius:20px;
    padding:25px;
}

.stat-box h3{
    color:#666;
    margin:0;
}

.stat-box p{
    color:#003366;
    font-size:34px;
    font-weight:bold;
    margin:15px 0 0;
}

.chart-row{
    margin-bottom:20px;
}

.chart-label{
    display:flex;
    justify-content:space-between;
    font-weight:bold;
    color:#003366;
    margin-bottom:6px;
}

.bar-bg{
    width:100%;
    height:22px;
    background:#ddd;
    border-radius:20px;
    overflow:hidden;
}

.bar-fill{
    height:100%;
    background:#003366;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#003366;
    color:white;
    padding:12px;
    text-align:left;
}

td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

.badge{
    padding:6px 12px;
    border-radius:15px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.new{
    background:#ff9800;
}

.status{
    background:#007bff;
}

.delayed{
    background:#dc3545;
}

.duesoon{
    background:#ff9800;
}

.alert-banner{
    border-radius:20px;
    padding:20px 25px;
    margin-bottom:20px;
    color:white;
    font-weight:600;
}

.alert-banner.overdue{
    background:#dc3545;
}

.alert-banner.duesoon{
    background:#ff9800;
}

.alert-banner ul{
    margin:8px 0 0;
    padding-left:20px;
}

.modal{
    position:fixed;
    top:90px;
    right:40px;
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
    z-index:999;
    max-width:350px;
}

.modal h3{
    color:#003366;
    margin-top:0;
}

.close-btn{
    background:#003366;
    color:white;
    border:none;
    padding:8px 15px;
    border-radius:8px;
    cursor:pointer;
}
</style>
</head>

<body>

<header class="header">
<h1>Engineer Helper</h1>
<div class="nav-links">
<a href="DashboardEngineer.php">HOME</a>
<a href="LogOut.php">LOG OUT</a>
</div>
</header>

<div class="layout-container">

<aside class="sidebar">
<h2>Hi, <?php echo htmlspecialchars($username); ?>!</h2>
<div class="page-title">Project Dashboard</div>

<ul>
<li class="active"><a href="DashboardEngineer.php">Project Dashboard</a></li>
<li><a href="ProjectProgress.php">Project Progress</a></li>
<li><a href="ProjectDetails.php">Project Details</a></li>
<li><a href="UploadReport.php">Upload Report</a></li>
<li><a href="Finalreport.php">Report Generator</a></li>
<li><a href="AddWorkingHours.php">Add Working Hours</a></li>
</ul>
</aside>

<main class="content-area">

<?php if(mysqli_num_rows($overdueProjects) > 0) { ?>
<div class="alert-banner overdue">
    ⚠️ You have <?php echo mysqli_num_rows($overdueProjects); ?> overdue project(s):
    <ul>
    <?php while($od = mysqli_fetch_assoc($overdueProjects)) { ?>
        <li><?php echo htmlspecialchars($od['ProjectName']); ?> — <?php echo $od['DaysOverdue']; ?> day(s) past deadline (<?php echo $od['EndDate']; ?>)</li>
    <?php } ?>
    </ul>
</div>
<?php } ?>

<?php if(mysqli_num_rows($dueSoonProjects) > 0) { ?>
<div class="alert-banner duesoon">
    ⏰ <?php echo mysqli_num_rows($dueSoonProjects); ?> project(s) due within 7 days:
    <ul>
    <?php while($ds = mysqli_fetch_assoc($dueSoonProjects)) { ?>
        <li><?php echo htmlspecialchars($ds['ProjectName']); ?> — due in <?php echo $ds['DaysLeft']; ?> day(s) (<?php echo $ds['EndDate']; ?>)</li>
    <?php } ?>
    </ul>
</div>
<?php } ?>

<div class="card">
<h2>Engineer Dashboard Overview</h2>

<div class="grid">
<div class="stat-box">
<h3>Total Assigned Projects</h3>
<p><?php echo $totalProjects; ?></p>
</div>

<div class="stat-box">
<h3>Completed Projects</h3>
<p><?php echo $completedProjects; ?></p>
</div>

<div class="stat-box">
<h3>In Progress Projects</h3>
<p><?php echo $inProgressProjects; ?></p>
</div>

<div class="stat-box">
<h3>Total Hours Logged</h3>
<p><?php echo number_format($totalHours,2); ?></p>
</div>
</div>
</div>

<div class="card">
<h2>Project Progress Chart</h2>

<?php
mysqli_data_seek($projects, 0);
while($row = mysqli_fetch_assoc($projects)) {
?>
<div class="chart-row">
<div class="chart-label">
<span><?php echo htmlspecialchars($row['ProjectName']); ?></span>
<span><?php echo $row['LatestProgress']; ?>%</span>
</div>
<div class="bar-bg">
<div class="bar-fill" style="width:<?php echo $row['LatestProgress']; ?>%;"></div>
</div>
</div>
<?php } ?>
</div>

<div class="card">
<h2>Assigned Project Summary</h2>

<table>
<tr>
<th>Project</th>
<th>Status</th>
<th>Progress</th>
<th>Total Hours</th>
<th>Label</th>
</tr>

<?php
$chartOnTrack = 0;
$chartDueSoon = 0;
$chartDelayed = 0;
$chartCompleted = 0;

mysqli_data_seek($projects, 0);
while($row = mysqli_fetch_assoc($projects)) {

    $isNew = false;

    if(!empty($row['AssignedStartDate'])) {
        $assignedDate = strtotime($row['AssignedStartDate']);
        $sevenDaysAgo = strtotime('-7 days');

        if($assignedDate >= $sevenDaysAgo) {
            $isNew = true;
        }
    }

    $isOverdue = false;
    $isDueSoon = false;

    if(!empty($row['EndDate']) && $row['Status'] !== 'Completed') {
        $daysLeft = (strtotime($row['EndDate']) - strtotime(date('Y-m-d'))) / 86400;
        if($daysLeft < 0) {
            $isOverdue = true;
        } elseif($daysLeft <= 7) {
            $isDueSoon = true;
        }
    }

    if($row['Status'] === 'Completed') {
        $chartCompleted++;
    } elseif($isOverdue) {
        $chartDelayed++;
    } elseif($isDueSoon) {
        $chartDueSoon++;
    } else {
        $chartOnTrack++;
    }
?>

<tr>
<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td>
<span class="badge status">
<?php echo htmlspecialchars($row['Status']); ?>
</span>
</td>

<td><?php echo $row['LatestProgress']; ?>%</td>
<td><?php echo number_format($row['TotalHours'],2); ?> hour(s)</td>

<td>
<?php if($isOverdue) { ?>
<span class="badge delayed">DELAYED</span>
<?php } elseif($isDueSoon) { ?>
<span class="badge duesoon">DUE SOON</span>
<?php } elseif($isNew) { ?>
<span class="badge new">NEW</span>
<?php } else { echo "-"; } ?>
</td>
</tr>

<?php } ?>

</table>
</div>

<div class="card">
<h2>Project Status Breakdown</h2>
<canvas id="statusPieChart" style="max-width:350px;margin:0 auto;display:block;"></canvas>
</div>

<script>
new Chart(document.getElementById('statusPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['On Track', 'Due Soon', 'Delayed', 'Completed'],
        datasets: [{
            data: [<?php echo $chartOnTrack; ?>, <?php echo $chartDueSoon; ?>, <?php echo $chartDelayed; ?>, <?php echo $chartCompleted; ?>],
            backgroundColor: ['#007bff', '#ff9800', '#dc3545', '#6f42c1']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>

</main>
</div>

<?php if(count($newProjectNames) > 0) { ?>
<div class="modal" id="newProjectModal">
<h3>New Project Assigned</h3>
<p>You have new assigned project(s):</p>

<ul>
<?php foreach($newProjectNames as $projectName) { ?>
<li><?php echo htmlspecialchars($projectName); ?></li>
<?php } ?>
</ul>

<button class="close-btn" onclick="document.getElementById('newProjectModal').style.display='none'">
Close
</button>
</div>
<?php } ?>

</body>
</html>