<?php
session_start();
include 'config.php';

if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'Engineer')
{
    header("Location: UserLogin.php");
    exit();
}

$username = $_SESSION['username'];

$staffQuery = mysqli_query($conn,
"SELECT * FROM staff WHERE Username='$username'");

$staff = mysqli_fetch_assoc($staffQuery);
$staffID = $staff['StaffID'];

$message = "";

if(isset($_POST['save']))
{
    $projectID = $_POST['projectID'];
    $workDate = $_POST['workDate'];
    $taskName = $_POST['taskName'];

    if($taskName == "Other")
    {
        $taskName = trim($_POST['otherTask']);
    }

    $hoursWorked = $_POST['hoursWorked'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn,
    "INSERT INTO working_hours
    (StaffID, ProjectID, WorkDate, TaskName, HoursWorked, Description)
    VALUES
    ('$staffID', '$projectID', '$workDate', '$taskName', '$hoursWorked', '$description')");

    $message = "Working hours saved successfully.";
}

$totalHours = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COALESCE(SUM(HoursWorked),0) AS total
FROM working_hours
WHERE StaffID='$staffID'"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Working Hours</title>

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

.card h2{
    color:#003366;
    margin-top:0;
}

.summary-box{
    background:#f8f9fa;
    border-left:6px solid #003366;
    border-radius:20px;
    padding:25px;
    margin-bottom:25px;
}

.summary-box h3{
    margin:0;
    color:#666;
}

.summary-box p{
    color:#003366;
    font-size:34px;
    font-weight:bold;
    margin:15px 0 0;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    font-weight:bold;
    color:#003366;
    margin-bottom:5px;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    box-sizing:border-box;
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
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
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

#otherTaskBox{
    display:none;
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

<div class="page-title">
ADD WORKING<br>HOURS
</div>

<ul>
<li><a href="DashboardEngineer.php">Project Dashboard</a></li>
<li><a href="ProjectProgress.php">Project Progress</a></li>
<li><a href="ProjectDetails.php">Project Details</a></li>
<li><a href="UploadReport.php">Upload Report</a></li>
<li><a href="Finalreport.php">Report Generator</a></li>
<li class="active"><a href="AddWorkingHours.php">Add Working Hours</a></li>
</ul>

</aside>

<main class="content-area">

<div class="card">

<h2>Working Hours Overview</h2>

<div class="summary-box">
<h3>Total Hours Logged</h3>
<p><?php echo number_format($totalHours['total'],2); ?></p>
</div>

</div>

<div class="card">

<h2>Record Working Hours</h2>

<?php
if($message != "")
{
    echo "<div class='success'>$message</div>";
}
?>

<form method="POST">

<div class="form-group">
<label>Select Assigned Project</label>

<select name="projectID" required>
<option value="">-- Select Project --</option>

<?php
$projectQuery = mysqli_query($conn,
"SELECT DISTINCT p.*
FROM project p
LEFT JOIN project_assignment pa
ON p.ProjectID = pa.ProjectID
WHERE pa.StaffID='$staffID'
OR p.StaffID='$staffID'
ORDER BY p.ProjectName ASC");

while($project = mysqli_fetch_assoc($projectQuery))
{
?>

<option value="<?php echo $project['ProjectID']; ?>">
<?php echo htmlspecialchars($project['ProjectName']); ?>
</option>

<?php } ?>

</select>
</div>

<div class="form-grid">

<div class="form-group">
<label>Date</label>
<input type="date" name="workDate" required>
</div>

<div class="form-group">
<label>Hours Worked</label>
<input type="number" step="0.5" min="0.5" max="24" name="hoursWorked" required>
</div>

</div>

<div class="form-group">
<label>Task</label>

<select name="taskName" id="taskSelect" onchange="toggleOtherTask()" required>
<option value="Site Inspection">Site Inspection</option>
<option value="BOQ Calculation">BOQ Calculation</option>
<option value="Documentation">Documentation</option>
<option value="Meeting">Meeting</option>
<option value="Report Preparation">Report Preparation</option>
<option value="Design Review">Design Review</option>
<option value="Other">Other</option>
</select>
</div>

<div class="form-group" id="otherTaskBox">
<label>Enter Custom Task</label>
<input type="text" name="otherTask" placeholder="Example: Soil Testing">
</div>

<div class="form-group">
<label>Description</label>
<textarea name="description" rows="4" placeholder="Enter working description"></textarea>
</div>

<button class="btn" type="submit" name="save">
Save Working Hours
</button>

</form>

</div>

<div class="card">

<h2>Working Hours History</h2>

<table>
<tr>
<th>Date</th>
<th>Project</th>
<th>Task</th>
<th>Hours</th>
<th>Description</th>
</tr>

<?php
$history = mysqli_query($conn,
"SELECT
    wh.*,
    p.ProjectName
FROM working_hours wh
INNER JOIN project p
ON wh.ProjectID = p.ProjectID
WHERE wh.StaffID='$staffID'
ORDER BY wh.WorkDate DESC, wh.WorkingHourID DESC");

if(mysqli_num_rows($history) > 0)
{
    while($row = mysqli_fetch_assoc($history))
    {
?>

<tr>
<td><?php echo $row['WorkDate']; ?></td>
<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>
<td><?php echo htmlspecialchars($row['TaskName']); ?></td>
<td><?php echo number_format($row['HoursWorked'],2); ?></td>
<td><?php echo htmlspecialchars($row['Description']); ?></td>
</tr>

<?php
    }
}
else
{
?>

<tr>
<td colspan="5" style="text-align:center;">
No working hours recorded yet.
</td>
</tr>

<?php } ?>

</table>

</div>

</main>

</div>

<script>
function toggleOtherTask()
{
    let task = document.getElementById("taskSelect").value;
    let otherBox = document.getElementById("otherTaskBox");

    if(task === "Other")
    {
        otherBox.style.display = "block";
    }
    else
    {
        otherBox.style.display = "none";
    }
}
</script>

</body>
</html>