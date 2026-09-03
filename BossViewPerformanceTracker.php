<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

$search = "";

if(isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/* SUMMARY CARDS */
$totalHoursThisMonth = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT IFNULL(SUM(HoursWorked),0) AS TotalHours
 FROM working_hours
 WHERE MONTH(WorkDate)=MONTH(CURDATE())
 AND YEAR(WorkDate)=YEAR(CURDATE())"));

$completedThisYear = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS TotalCompleted
 FROM project
 WHERE Status='Completed'
 AND YEAR(StartDate)=YEAR(CURDATE())"));

$totalProjectValue = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT IFNULL(SUM(FinalCost),0) AS TotalCost
 FROM final_report
 WHERE YEAR(GeneratedDate)=YEAR(CURDATE())"));

$totalEngineers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS TotalEngineer
 FROM staff
 WHERE Role='Engineer'"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Boss Performance Tracker</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    overflow-y:auto;
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
    color:#d1d1d1;
    font-size:1.15rem;
    font-weight:800;
    margin-bottom:45px;
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

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.summary-box{
    background:#f8f9fa;
    border-left:6px solid #003366;
    border-radius:20px;
    padding:25px;
}

.summary-box h3{
    color:#666;
    margin:0;
    font-size:17px;
}

.summary-box p{
    color:#003366;
    font-size:34px;
    font-weight:bold;
    margin:15px 0 0;
}

.search-box{
    display:flex;
    gap:10px;
    margin-bottom:20px;
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
    padding:10px 18px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}

.reset{
    background:#6c757d;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

th{
    background:#003366;
    color:white;
    padding:14px;
    text-align:left;
    text-transform:uppercase;
    font-size:0.85rem;
}

td{
    padding:14px;
    border-bottom:1px solid #ddd;
    color:#444;
}

.saved{
    color:#28a745;
    font-weight:bold;
}

.over{
    color:#dc3545;
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
    <h2>Manager Control</h2>

    <a href="BossDashboard.php">Manager Dashboard</a>
    <a href="BossClientProposal.php">Client Proposals</a>
    <a href="BossAssignEngineer.php">Assign Engineers</a>
    <a href="BossEngineerReport.php">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php" class="active">Performance Reports</a>
</div>

<div class="content">

<div class="card">
<h2>Performance Tracker Overview</h2>

<div class="summary-grid">

<div class="summary-box">
<h3>Total Hours This Month</h3>
<p><?php echo number_format($totalHoursThisMonth['TotalHours'],2); ?></p>
</div>

<div class="summary-box">
<h3>Completed Projects This Year</h3>
<p><?php echo $completedThisYear['TotalCompleted']; ?></p>
</div>

<div class="summary-box">
<h3>Total Project Cost This Year</h3>
<p>RM <?php echo number_format($totalProjectValue['TotalCost'],2); ?></p>
</div>

<div class="summary-box">
<h3>Total Engineers</h3>
<p><?php echo $totalEngineers['TotalEngineer']; ?></p>
</div>

</div>
</div>

<div class="card">
<h2>Engineer Performance Report</h2>

<form method="GET" class="search-box">
<input type="text" name="search" placeholder="Search engineer name" value="<?php echo htmlspecialchars($search); ?>">
<button type="submit" class="btn">Search</button>
<a href="BossViewPerformanceTracker.php" class="btn reset">Reset</a>
</form>

<table>
<tr>
<th>Engineer Name</th>
<th>Assigned Projects</th>
<th>Total Hours Worked</th>
</tr>

<?php
$engineerReport = mysqli_query($conn,
"SELECT
    s.Username,
    COUNT(DISTINCT p.ProjectID) AS TotalProjects,
    IFNULL(SUM(w.HoursWorked),0) AS TotalHours
 FROM staff s
 LEFT JOIN project p
 ON s.StaffID = p.StaffID
 LEFT JOIN working_hours w
 ON s.StaffID = w.StaffID
 WHERE s.Role='Engineer'
 AND s.Username LIKE '%$search%'
 GROUP BY s.StaffID, s.Username
 ORDER BY TotalHours DESC");

$chartLabels = [];
$chartHours = [];

if(mysqli_num_rows($engineerReport) > 0) {
    while($row = mysqli_fetch_assoc($engineerReport)) {

        $chartLabels[] = $row['Username'];
        $chartHours[] = (float)$row['TotalHours'];
?>

<tr>
<td><?php echo htmlspecialchars($row['Username']); ?></td>
<td><?php echo $row['TotalProjects']; ?></td>
<td><?php echo number_format($row['TotalHours'],2); ?> hour(s)</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='3' style='text-align:center;'>No engineer found.</td></tr>";
}
?>

</table>
</div>

<div class="card">
<h2>Engineer Hours Distribution</h2>
<canvas id="engineerHoursPie" style="max-width:400px;margin:0 auto;display:block;"></canvas>
</div>

<script>
new Chart(document.getElementById('engineerHoursPie'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($chartHours); ?>,
            backgroundColor: ['#004aad', '#28a745', '#ff9800', '#dc3545', '#6f42c1', '#17a2b8', '#e83e8c', '#20c997']
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<div class="card">
<h2>Total Hours by Project by Month</h2>

<table>
<tr>
<th>Project Name</th>
<th>Month</th>
<th>Year</th>
<th>Total Hours</th>
</tr>

<?php
$monthlyHours = mysqli_query($conn,
"SELECT
    p.ProjectName,
    YEAR(w.WorkDate) AS WorkYear,
    MONTH(w.WorkDate) AS WorkMonth,
    SUM(w.HoursWorked) AS TotalHours
 FROM working_hours w
 INNER JOIN project p
 ON w.ProjectID = p.ProjectID
 GROUP BY p.ProjectID, p.ProjectName, YEAR(w.WorkDate), MONTH(w.WorkDate)
 ORDER BY WorkYear DESC, WorkMonth DESC, p.ProjectName ASC");

if(mysqli_num_rows($monthlyHours) > 0) {
    while($row = mysqli_fetch_assoc($monthlyHours)) {
?>

<tr>
<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>
<td><?php echo $row['WorkMonth']; ?></td>
<td><?php echo $row['WorkYear']; ?></td>
<td><?php echo number_format($row['TotalHours'],2); ?> hour(s)</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center;'>No working hours recorded yet.</td></tr>";
}
?>

</table>
</div>

<div class="card">
<h2>Project Summary by Year</h2>

<table>
<tr>
<th>Year</th>
<th>Total Projects</th>
<th>Completed</th>
<th>In Progress</th>
<th>Approved</th>
<th>Rejected</th>
</tr>

<?php
$projectSummary = mysqli_query($conn,
"SELECT
    YEAR(StartDate) AS ProjectYear,
    COUNT(*) AS TotalProjects,
    SUM(CASE WHEN Status='Completed' THEN 1 ELSE 0 END) AS CompletedProjects,
    SUM(CASE WHEN Status='In Progress' THEN 1 ELSE 0 END) AS InProgressProjects,
    SUM(CASE WHEN Status='Approved' THEN 1 ELSE 0 END) AS ApprovedProjects,
    SUM(CASE WHEN Status='Rejected' THEN 1 ELSE 0 END) AS RejectedProjects
 FROM project
 WHERE StartDate IS NOT NULL
 GROUP BY YEAR(StartDate)
 ORDER BY ProjectYear DESC");

if(mysqli_num_rows($projectSummary) > 0) {
    while($row = mysqli_fetch_assoc($projectSummary)) {
?>

<tr>
<td><?php echo $row['ProjectYear']; ?></td>
<td><?php echo $row['TotalProjects']; ?></td>
<td><?php echo $row['CompletedProjects']; ?></td>
<td><?php echo $row['InProgressProjects']; ?></td>
<td><?php echo $row['ApprovedProjects']; ?></td>
<td><?php echo $row['RejectedProjects']; ?></td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center;'>No project summary found.</td></tr>";
}
?>

</table>
</div>

<div class="card">
<h2>Project Cost Report by Year</h2>

<table>
<tr>
<th>Year</th>
<th>Project Name</th>
<th>Original Budget</th>
<th>Final Cost</th>
<th>Difference</th>
<th>Cost Variance Explanation</th>
</tr>

<?php
$costReport = mysqli_query($conn,
"SELECT
    p.ProjectName,
    YEAR(fr.GeneratedDate) AS ReportYear,
    fr.OriginalBudget,
    fr.FinalCost,
    fr.CostVariance
 FROM final_report fr
 INNER JOIN project p
 ON fr.ProjectID = p.ProjectID
 ORDER BY ReportYear DESC, p.ProjectName ASC");

if(mysqli_num_rows($costReport) > 0) {
    while($row = mysqli_fetch_assoc($costReport)) {

        $difference = $row['OriginalBudget'] - $row['FinalCost'];
        $diffClass = ($difference >= 0) ? "saved" : "over";
        $diffText = ($difference >= 0)
            ? "RM ".number_format($difference,2)." Saved"
            : "RM ".number_format(abs($difference),2)." Over Budget";
?>

<tr>
<td><?php echo $row['ReportYear']; ?></td>
<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>
<td>RM <?php echo number_format($row['OriginalBudget'],2); ?></td>
<td>RM <?php echo number_format($row['FinalCost'],2); ?></td>
<td class="<?php echo $diffClass; ?>">
<?php echo $diffText; ?>
</td>
<td><?php echo nl2br(htmlspecialchars($row['CostVariance'])); ?></td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center;'>No cost report found. Generate final reports first.</td></tr>";
}
?>

</table>
</div>

<div class="card">
<h2>Top 5 Most Active Engineers</h2>

<table>
<tr>
<th>Engineer</th>
<th>Total Hours</th>
</tr>

<?php
$topEngineers = mysqli_query($conn,
"SELECT
    s.Username,
    SUM(w.HoursWorked) AS TotalHours
 FROM working_hours w
 INNER JOIN staff s
 ON w.StaffID = s.StaffID
 GROUP BY s.StaffID, s.Username
 ORDER BY TotalHours DESC
 LIMIT 5");

if(mysqli_num_rows($topEngineers) > 0) {
    while($row = mysqli_fetch_assoc($topEngineers)) {
?>

<tr>
<td><?php echo htmlspecialchars($row['Username']); ?></td>
<td><?php echo number_format($row['TotalHours'],2); ?> hour(s)</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='2' style='text-align:center;'>No engineer working hours found.</td></tr>";
}
?>

</table>
</div>

</div>

</div>

</body>
</html>