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

$message = "";
$selectedProject = isset($_POST['projectID']) ? $_POST['projectID'] : "";

/* SAVE PROGRESS */
if(isset($_POST['save']))
{
    $projectID = (int)$_POST['projectID'];
    $progressDate = $_POST['progressDate'];
    $progressPercentage = (int)$_POST['progressPercentage'];
    $remarks = $_POST['remarks'];

    // Ownership check: only allow updates to a project this engineer is actually on
    $own = $conn->prepare(
        "SELECT p.ProjectID FROM project p
         LEFT JOIN project_assignment pa ON pa.ProjectID = p.ProjectID
         WHERE p.ProjectID = ? AND (p.StaffID = ? OR pa.StaffID = ?)"
    );
    $own->bind_param('iii', $projectID, $staffID, $staffID);
    $own->execute();
    $isOwner = $own->get_result()->fetch_assoc();
    $own->close();

    if(!$isOwner)
    {
        $message = "You are not assigned to that project.";
    }
    else
    {
        $insert = $conn->prepare(
            "INSERT INTO project_progress (ProjectID, StaffID, ProgressDate, ProgressPercentage, Remarks)
             VALUES (?, ?, ?, ?, ?)"
        );
        $insert->bind_param('iisis', $projectID, $staffID, $progressDate, $progressPercentage, $remarks);
        $insert->execute();
        $insert->close();

        $newStatus = ($progressPercentage >= 100) ? 'Completed' : 'In Progress';
        $update = $conn->prepare("UPDATE project SET Status = ? WHERE ProjectID = ?");
        $update->bind_param('si', $newStatus, $projectID);
        $update->execute();
        $update->close();

        $message = "Progress updated successfully.";
        $selectedProject = $projectID;
    }
}

/* DEFAULT SELECTED PROJECT */
if($selectedProject == "")
{
    $defaultProject = mysqli_query($conn,
    "SELECT DISTINCT p.ProjectID
     FROM project p
     LEFT JOIN project_assignment pa
     ON p.ProjectID = pa.ProjectID
     WHERE pa.StaffID='$staffID'
     OR p.StaffID='$staffID'
     ORDER BY p.ProjectID DESC
     LIMIT 1");

    if(mysqli_num_rows($defaultProject) > 0)
    {
        $default = mysqli_fetch_assoc($defaultProject);
        $selectedProject = $default['ProjectID'];
    }
}

/* SELECTED PROJECT INFO */
$project = null;
$currentProgress = 0;

if($selectedProject != "")
{
    $projectQuery = mysqli_query($conn,
    "SELECT *
     FROM project
     WHERE ProjectID='$selectedProject'");

    $project = mysqli_fetch_assoc($projectQuery);

    $latestProgress = mysqli_query($conn,
    "SELECT ProgressPercentage
     FROM project_progress
     WHERE ProjectID='$selectedProject'
     AND StaffID='$staffID'
     ORDER BY ProgressDate DESC, ProgressID DESC
     LIMIT 1");

    if(mysqli_num_rows($latestProgress) > 0)
    {
        $row = mysqli_fetch_assoc($latestProgress);
        $currentProgress = $row['ProgressPercentage'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Project Progress</title>

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
    height:calc(100vh - 80px);
}

.card{
    background:white;
    border-radius:40px;
    padding:35px;
    margin-bottom:25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

.card h2,
.card h3{
    color:#003366;
    margin-top:0;
}

select,
input,
textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    margin-top:8px;
    margin-bottom:15px;
    box-sizing:border-box;
}

label{
    font-weight:bold;
    color:#003366;
}

.btn{
    background:#003366;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.btn:hover{
    background:#004aad;
}

.success{
    background:#d4edda;
    color:#155724;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

.progress-bar{
    width:100%;
    height:25px;
    background:#ddd;
    border-radius:20px;
    overflow:hidden;
    margin-top:15px;
}

.progress-fill{
    height:100%;
    background:#003366;
}

.summary-box{
    background:#f8f9fa;
    border-left:6px solid #003366;
    border-radius:20px;
    padding:25px;
    margin-top:20px;
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

.pending{ background:#ff9800; }
.approved{ background:#28a745; }
.rejected{ background:#dc3545; }
.progress{ background:#007bff; }
.completed{ background:#6f42c1; }
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

<div class="page-title">
PROJECT<br>PROGRESS
</div>

<ul>
<li><a href="DashboardEngineer.php">Project Dashboard</a></li>
<li class="active"><a href="ProjectProgress.php">Project Progress</a></li>
<li><a href="ProjectDetails.php">Project Details</a></li>
<li><a href="UploadReport.php">Upload Report</a></li>
<li><a href="Finalreport.php">Report Generator</a></li>
<li><a href="AddWorkingHours.php">Add Working Hours</a></li>
</ul>

</aside>

<main class="content-area">

<div class="card">

<h2>Project Progress</h2>

<?php if($message != "") { ?>
<div class="success">
<?php echo $message; ?>
</div>
<?php } ?>

<form method="POST">

<label>Select Assigned Project</label>

<select name="projectID" onchange="this.form.submit()" required>
<option value="">-- Select Project --</option>

<?php
$projects = mysqli_query($conn,
"SELECT DISTINCT p.*
 FROM project p
 LEFT JOIN project_assignment pa
 ON p.ProjectID = pa.ProjectID
 WHERE pa.StaffID='$staffID'
 OR p.StaffID='$staffID'
 ORDER BY p.ProjectName ASC");

while($row = mysqli_fetch_assoc($projects))
{
?>

<option value="<?php echo $row['ProjectID']; ?>"
<?php if($selectedProject == $row['ProjectID']) echo "selected"; ?>>
<?php echo htmlspecialchars($row['ProjectName']); ?>
</option>

<?php } ?>

</select>

</form>

<?php if($project) { ?>

<div class="summary-box">

<h3><?php echo htmlspecialchars($project['ProjectName']); ?></h3>

<p>
Project ID: <?php echo $project['ProjectID']; ?>
</p>

<p>
Status:
<?php
$statusClass = "pending";

if($project['Status'] == "Approved") $statusClass = "approved";
elseif($project['Status'] == "Rejected") $statusClass = "rejected";
elseif($project['Status'] == "In Progress") $statusClass = "progress";
elseif($project['Status'] == "Completed") $statusClass = "completed";
?>

<span class="badge <?php echo $statusClass; ?>">
<?php echo htmlspecialchars($project['Status']); ?>
</span>
</p>

<h3>Current Progress</h3>

<div class="progress-bar">
<div class="progress-fill" style="width:<?php echo $currentProgress; ?>%;"></div>
</div>

<p>
<b><?php echo $currentProgress; ?>%</b> completed
</p>

</div>

<?php } else { ?>

<p>No assigned project found.</p>

<?php } ?>

</div>

<?php if($project) { ?>

<div class="card">

<h2>Update Progress</h2>

<form method="POST">

<input type="hidden" name="projectID" value="<?php echo $selectedProject; ?>">

<label>Date</label>
<input type="date" name="progressDate" required>

<label>Progress Percentage</label>
<input type="number" name="progressPercentage" min="0" max="100" required>

<label>Remarks</label>
<textarea name="remarks" rows="4" placeholder="Enter progress remarks"></textarea>

<button type="submit" name="save" class="btn">
Save Progress
</button>

</form>

</div>

<div class="card">

<h2>Progress History</h2>

<table>
<tr>
<th>Date</th>
<th>Project</th>
<th>Progress</th>
<th>Remarks</th>
</tr>

<?php
$history = mysqli_query($conn,
"SELECT
    pp.*,
    p.ProjectName
 FROM project_progress pp
 INNER JOIN project p
 ON pp.ProjectID = p.ProjectID
 WHERE pp.StaffID='$staffID'
 ORDER BY pp.ProgressDate DESC, pp.ProgressID DESC");

if(mysqli_num_rows($history) > 0)
{
    while($historyRow = mysqli_fetch_assoc($history))
    {
?>

<tr>
<td><?php echo $historyRow['ProgressDate']; ?></td>
<td><?php echo htmlspecialchars($historyRow['ProjectName']); ?></td>
<td><?php echo $historyRow['ProgressPercentage']; ?>%</td>
<td><?php echo htmlspecialchars($historyRow['Remarks']); ?></td>
</tr>

<?php
    }
}
else
{
?>

<tr>
<td colspan="4" style="text-align:center;">
No progress history found.
</td>
</tr>

<?php } ?>

</table>

</div>

<?php } ?>

</main>

</div>

</body>
</html>