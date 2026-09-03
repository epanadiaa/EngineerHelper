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

if(isset($_POST['save_report']))
{
    $projectID = $_POST['projectID'];
    $projectManager = mysqli_real_escape_string($conn, $_POST['projectManager']);
    $contractor = mysqli_real_escape_string($conn, $_POST['contractor']);
    $objective = mysqli_real_escape_string($conn, $_POST['objective']);
    $originalBudget = $_POST['originalBudget'];
    $finalCost = $_POST['finalCost'];
    $costVariance = mysqli_real_escape_string($conn, $_POST['costVariance']);
    $planningDesign = mysqli_real_escape_string($conn, $_POST['planningDesign']);
    $inspectionTesting = mysqli_real_escape_string($conn, $_POST['inspectionTesting']);

    $insert = mysqli_query($conn,
    "INSERT INTO final_report
    (ProjectID, StaffID, ProjectManager, Contractor, Objective,
     OriginalBudget, FinalCost, CostVariance, PlanningDesign, InspectionTesting)
    VALUES
    ('$projectID', '$staffID', '$projectManager', '$contractor', '$objective',
     '$originalBudget', '$finalCost', '$costVariance', '$planningDesign', '$inspectionTesting')");

    if($insert)
    {
        $message = "Final report saved successfully.";
    }
    else
    {
        $message = "Error saving final report: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Final Report</title>

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

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}

.form-group{
    margin-bottom:15px;
}

.form-group.full{
    grid-column:span 2;
}

label{
    font-weight:bold;
    color:#003366;
    display:block;
    margin-bottom:5px;
}

input,
select,
textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    box-sizing:border-box;
}

textarea{
    height:120px;
    resize:vertical;
}

.btn{
    background:#003366;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    text-decoration:none;
    display:inline-block;
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

.error{
    background:#f8d7da;
    color:#721c24;
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

.view-btn{
    background:#28a745;
    color:white;
    padding:8px 12px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
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
FINAL REPORT
</div>

<ul>
<li><a href="DashboardEngineer.php">Project Dashboard</a></li>
<li><a href="ProjectProgress.php">Project Progress</a></li>
<li><a href="ProjectDetails.php">Project Details</a></li>
<li><a href="UploadReport.php">Upload Report</a></li>
<li class="active"><a href="FinalReport.php">Report Generator</a></li>
<li><a href="AddWorkingHours.php">Add Working Hours</a></li>
</ul>

</aside>

<main class="content-area">

<div class="card">

<h2>Create Final Report</h2>

<?php if($message != "") { ?>
<div class="<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
<?php echo $message; ?>
</div>
<?php } ?>

<form method="POST">

<div class="form-group">
<label>Select Assigned Project</label>

<select name="projectID" required>
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

while($p = mysqli_fetch_assoc($projects)) {
    echo "<option value='".$p['ProjectID']."'>".htmlspecialchars($p['ProjectName'])."</option>";
}
?>

</select>
</div>

<div class="form-grid">

<div class="form-group">
<label>Project Manager</label>
<input type="text" name="projectManager" required>
</div>

<div class="form-group">
<label>Contractor</label>
<input type="text" name="contractor" required>
</div>

<div class="form-group">
<label>Original Budget (RM)</label>
<input type="number" step="0.01" name="originalBudget" required>
</div>

<div class="form-group">
<label>Final Cost (RM)</label>
<input type="number" step="0.01" name="finalCost" required>
</div>

<div class="form-group full">
<label>Objective</label>
<textarea name="objective" required></textarea>
</div>

<div class="form-group full">
<label>Cost Variance</label>
<textarea name="costVariance" required></textarea>
</div>

<div class="form-group full">
<label>Planning and Design</label>
<textarea name="planningDesign" required></textarea>
</div>

<div class="form-group full">
<label>Inspection and Testing</label>
<textarea name="inspectionTesting" required></textarea>
</div>

</div>

<button type="submit" name="save_report" class="btn">
Save Final Report
</button>

</form>

</div>

<div class="card">

<h2>Saved Final Reports</h2>

<table>
<tr>
<th>Project</th>
<th>Generated Date</th>
<th>Original Budget</th>
<th>Final Cost</th>
<th>Action</th>
</tr>

<?php
$reports = mysqli_query($conn,
"SELECT fr.*, p.ProjectName
 FROM final_report fr
 INNER JOIN project p ON fr.ProjectID = p.ProjectID
 WHERE fr.StaffID='$staffID'
 ORDER BY fr.GeneratedDate DESC");

if(mysqli_num_rows($reports) > 0) {
    while($r = mysqli_fetch_assoc($reports)) {
?>

<tr>
<td><?php echo htmlspecialchars($r['ProjectName']); ?></td>
<td><?php echo $r['GeneratedDate']; ?></td>
<td>RM <?php echo number_format($r['OriginalBudget'],2); ?></td>
<td>RM <?php echo number_format($r['FinalCost'],2); ?></td>
<td>
<a class="view-btn" href="ViewFinalReport.php?id=<?php echo $r['ReportID']; ?>" target="_blank">
View Report
</a>
</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center;'>No final report saved yet.</td></tr>";
}
?>

</table>

</div>

</main>

</div>

</body>
</html>