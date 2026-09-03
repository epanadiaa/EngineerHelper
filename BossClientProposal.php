<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

$message = "";

/* APPROVE PROPOSAL */
if (isset($_POST['approve_project'])) {
    $projectID = mysqli_real_escape_string($conn, $_POST['project_id']);

    $update = mysqli_query($conn,
    "UPDATE project
     SET Status='Approved'
     WHERE ProjectID='$projectID'");

    if ($update) {
        $message = "<div class='alert success'>Project proposal approved successfully.</div>";
    } else {
        $message = "<div class='alert error'>Error approving proposal.</div>";
    }
}

/* REJECT PROPOSAL */
if (isset($_POST['reject_project'])) {
    $projectID = mysqli_real_escape_string($conn, $_POST['project_id']);

    $update = mysqli_query($conn,
    "UPDATE project
     SET Status='Rejected'
     WHERE ProjectID='$projectID'");

    if ($update) {
        $message = "<div class='alert error'>Project proposal rejected.</div>";
    } else {
        $message = "<div class='alert error'>Error rejecting proposal.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Boss Client Proposals</title>

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

.btn{
    color:white;
    border:none;
    padding:9px 15px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    margin-bottom:5px;
}

.view-btn{
    background:#004aad;
}

.approve-btn{
    background:#28a745;
}

.reject-btn{
    background:#dc3545;
}

.badge{
    padding:6px 12px;
    border-radius:15px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.pending{ background:#ff9800; }
.approved{ background:#28a745; }
.rejected{ background:#dc3545; }
.progress{ background:#007bff; }
.completed{ background:#6f42c1; }

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
    <a href="BossClientProposal.php" class="active">Client Proposals</a>
    <a href="BossAssignEngineer.php">Assign Engineers</a>
    <a href="BossEngineerReport.php">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php">Performance Reports</a>
    <a href="BossPerformanceTracker.php">Performance Tracker</a>
</div>

<div class="content">

<div class="card">

<h2>Client Project Proposals</h2>

<?php echo $message; ?>

<table>
<tr>
<th>Project ID</th>
<th>Project Name</th>
<th>Client Company</th>
<th>Location</th>
<th>Timeline</th>
<th>Status</th>
<th>Proposal</th>
<th>Action</th>
</tr>

<?php
$proposalQuery = mysqli_query($conn,
"SELECT
    p.*,
    c.ClientCompany
 FROM project p
 INNER JOIN client c
 ON p.ClientID = c.ClientID
 ORDER BY p.ProjectID DESC");

if(mysqli_num_rows($proposalQuery) > 0) {
    while($row = mysqli_fetch_assoc($proposalQuery)) {

        $status = $row['Status'];
        $class = "pending";

        if($status == "Approved") $class = "approved";
        elseif($status == "Rejected") $class = "rejected";
        elseif($status == "In Progress") $class = "progress";
        elseif($status == "Completed") $class = "completed";

        $docQuery = mysqli_query($conn,
        "SELECT *
         FROM project_document
         WHERE ProjectID='".$row['ProjectID']."'
         AND P_DocName='Project Proposal'
         LIMIT 1");

        $doc = mysqli_fetch_assoc($docQuery);
?>

<tr>
<td><?php echo $row['ProjectID']; ?></td>

<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td><?php echo htmlspecialchars($row['ClientCompany']); ?></td>

<td><?php echo htmlspecialchars($row['Place']); ?></td>

<td>
<?php echo $row['StartDate']; ?>
<br>to<br>
<?php echo $row['EndDate']; ?>
</td>

<td>
<span class="badge <?php echo $class; ?>">
<?php echo $status; ?>
</span>
</td>

<td>
<?php if($doc && !empty($doc['P_FilePath'])) { ?>
<a class="btn view-btn" href="<?php echo $doc['P_FilePath']; ?>" target="_blank">View</a>
<?php } elseif(!empty($row['ProposalFile'])) { ?>
<a class="btn view-btn" href="uploads/ProjectProposal/<?php echo $row['ProposalFile']; ?>" target="_blank">View</a>
<?php } else { echo "-"; } ?>
</td>

<td>
<?php if($row['Status'] == "Pending") { ?>

<form method="POST" style="display:inline;">
<input type="hidden" name="project_id" value="<?php echo $row['ProjectID']; ?>">
<button type="submit" name="approve_project" class="btn approve-btn">Approve</button>
</form>

<form method="POST" style="display:inline;">
<input type="hidden" name="project_id" value="<?php echo $row['ProjectID']; ?>">
<button type="submit" name="reject_project" class="btn reject-btn">Reject</button>
</form>

<?php } else { ?>
-
<?php } ?>
</td>
</tr>

<?php
    }
} else {
?>

<tr>
<td colspan="8" style="text-align:center;">
No client proposals found.
</td>
</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>
</html>