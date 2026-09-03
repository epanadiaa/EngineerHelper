<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

/* SUMMARY COUNTS */

$totalProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM project"));

$pendingProposals = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM project WHERE Status='Pending'"));

$approvedProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM project WHERE Status='Approved'"));

$activeProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM project WHERE Status='In Progress'"));

$completedProjects = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM project WHERE Status='Completed'"));

$totalEngineers = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM staff WHERE Role='Engineer'"));

/* DEADLINE ALERTS - across all projects, with assigned engineer name */
$overdueProjects = mysqli_query($conn,
"SELECT p.ProjectID, p.ProjectName, p.EndDate, s.Username AS EngineerName,
    DATEDIFF(CURDATE(), p.EndDate) AS DaysOverdue
FROM project p
LEFT JOIN staff s ON s.StaffID = p.StaffID
WHERE p.EndDate IS NOT NULL
AND p.EndDate < CURDATE()
AND p.Status <> 'Completed'
ORDER BY p.EndDate ASC");

$overdueCount = mysqli_num_rows($overdueProjects);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manager Dashboard</title>
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
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    margin-bottom:30px;
}

.card h2{
    color:#003366;
    margin-top:0;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.stat-card{
    background:#f8f9fa;
    border-left:6px solid #003366;
    border-radius:20px;
    padding:25px;
}

.stat-card h3{
    margin:0;
    color:#003366;
    font-size:18px;
}

.stat-card p{
    margin:15px 0 0;
    font-size:36px;
    font-weight:bold;
    color:#004aad;
}

.quick-links{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-top:25px;
}

.quick-link{
    background:#003366;
    color:white;
    text-decoration:none;
    padding:20px;
    border-radius:20px;
    font-weight:bold;
    text-align:center;
}

.quick-link:hover{
    background:#004aad;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
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
    font-weight:bold;
    font-size:13px;
}

.pending{ background:#ff9800; }
.approved{ background:#28a745; }
.progress{ background:#007bff; }
.completed{ background:#6f42c1; }
.rejected{ background:#dc3545; }

.alert-banner{
    border-radius:20px;
    padding:20px 25px;
    margin-bottom:20px;
    color:white;
    font-weight:600;
    background:#dc3545;
}

.alert-banner table{
    width:100%;
    margin-top:12px;
    border-collapse:collapse;
    color:#333;
    background:white;
    border-radius:12px;
    overflow:hidden;
}

.alert-banner th{
    background:#7a1f28;
    color:white;
    padding:8px 12px;
    text-align:left;
}

.alert-banner td{
    padding:8px 12px;
    background:white;
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

    <a href="BossDashboard.php" class="active">Manager Dashboard</a>
    <a href="BossClientProposal.php">Client Proposals</a>
    <a href="BossAssignEngineer.php">Assign Engineers</a>
    <a href="BossEngineerReport.php">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php">Performance Reports</a>
</div>

<div class="content">

<?php if($overdueCount > 0) { ?>
<div class="alert-banner">
    ⚠️ <?php echo $overdueCount; ?> project(s) are past their end date and not yet completed:
    <table>
    <tr><th>Project</th><th>Assigned Engineer</th><th>End Date</th><th>Days Overdue</th></tr>
    <?php while($od = mysqli_fetch_assoc($overdueProjects)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($od['ProjectName']); ?></td>
        <td><?php echo htmlspecialchars($od['EngineerName'] ?? 'Unassigned'); ?></td>
        <td><?php echo $od['EndDate']; ?></td>
        <td><?php echo $od['DaysOverdue']; ?></td>
    </tr>
    <?php } ?>
    </table>
</div>
<?php } ?>

<div class="card">
<h2>Manager Dashboard Overview</h2>

<div class="cards">

<div class="stat-card">
<h3>Total Projects</h3>
<p><?php echo $totalProjects['total']; ?></p>
</div>

<div class="stat-card">
<h3>Pending Proposals</h3>
<p><?php echo $pendingProposals['total']; ?></p>
</div>

<div class="stat-card">
<h3>Approved Projects</h3>
<p><?php echo $approvedProjects['total']; ?></p>
</div>

<div class="stat-card">
<h3>Active Projects</h3>
<p><?php echo $activeProjects['total']; ?></p>
</div>

<div class="stat-card">
<h3>Completed Projects</h3>
<p><?php echo $completedProjects['total']; ?></p>
</div>

<div class="stat-card">
<h3>Total Engineers</h3>
<p><?php echo $totalEngineers['total']; ?></p>
</div>

</div>

<div class="quick-links">

<a class="quick-link" href="BossClientProposal.php">
Review Client Proposals
</a>

<a class="quick-link" href="BossAssignEngineer.php">
Assign Engineers
</a>

<a class="quick-link" href="BossEngineerReport.php">
View Reports
</a>

<a class="quick-link" href="BossViewPerformanceTracker.php">
Track Performance
</a>

</div>

</div>

<div class="card">
<h2>Project Status Distribution</h2>
<canvas id="statusPieChart" style="max-width:350px;margin:0 auto;display:block;"></canvas>
</div>

<script>
new Chart(document.getElementById('statusPieChart'), {
    type: 'pie',
    data: {
        labels: ['Pending', 'Approved', 'In Progress', 'Completed'],
        datasets: [{
            data: [
                <?php echo $pendingProposals['total']; ?>,
                <?php echo $approvedProjects['total']; ?>,
                <?php echo $activeProjects['total']; ?>,
                <?php echo $completedProjects['total']; ?>
            ],
            backgroundColor: ['#ff9800', '#28a745', '#007bff', '#6f42c1']
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
<h2>Recent Projects</h2>

<table>
<tr>
<th>Project ID</th>
<th>Project Name</th>
<th>Client</th>
<th>Engineer</th>
<th>Status</th>
</tr>

<?php
$recentProjects = mysqli_query($conn,
"SELECT
    p.ProjectID,
    p.ProjectName,
    p.Status,
    c.ClientCompany,
    s.Username AS EngineerName
 FROM project p
 LEFT JOIN client c
 ON p.ClientID = c.ClientID
 LEFT JOIN staff s
 ON p.StaffID = s.StaffID
 ORDER BY p.ProjectID DESC
 LIMIT 10");

while($row = mysqli_fetch_assoc($recentProjects)) {

    $status = $row['Status'];
    $class = "pending";

    if($status == "Approved") $class = "approved";
    elseif($status == "In Progress") $class = "progress";
    elseif($status == "Completed") $class = "completed";
    elseif($status == "Rejected") $class = "rejected";
?>

<tr>
<td><?php echo $row['ProjectID']; ?></td>

<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td><?php echo htmlspecialchars($row['ClientCompany']); ?></td>

<td>
<?php echo $row['EngineerName'] ? htmlspecialchars($row['EngineerName']) : "Unassigned"; ?>
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