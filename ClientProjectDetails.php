<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Client') {
    header("Location: UserLogin.php");
    exit();
}

$clientID = $_SESSION['clientID'];
$message = "";

if (isset($_POST['createProject'])) {

    $projectName = mysqli_real_escape_string($conn, $_POST['projectName']);
    $place = mysqli_real_escape_string($conn, $_POST['place']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];

    $minimumStartDate = date('Y-m-d', strtotime('+1 month'));

    if ($startDate < $minimumStartDate) {
        $message = "Start Date must be at least 1 month after proposal submission.";
    }
    elseif ($startDate > $endDate) {
        $message = "Start Date cannot be later than End Date.";
    }
    else {
        $proposalFileName = "";
        $proposalPath = "";
        $fileType = "";

        if (isset($_FILES['proposalFile']) && $_FILES['proposalFile']['error'] == 0) {

            $folder = "uploads/ProjectProposal/";

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $originalName = basename($_FILES['proposalFile']['name']);
            $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($fileType != "pdf") {
                $message = "Only PDF proposal files are allowed.";
            }
            else {
                $proposalFileName = time() . "_" . $originalName;
                $proposalPath = $folder . $proposalFileName;

                move_uploaded_file($_FILES['proposalFile']['tmp_name'], $proposalPath);

                $insertProject = mysqli_query($conn,
                "INSERT INTO project
                (ProjectName, StartDate, EndDate, Status, Place, StaffID, ClientID, ProposalFile)
                VALUES
                ('$projectName', '$startDate', '$endDate', 'Pending', '$place', NULL, '$clientID', '$proposalFileName')");

                if ($insertProject) {

                    $projectID = mysqli_insert_id($conn);

                    mysqli_query($conn,
                    "INSERT INTO project_document
                    (P_DocName, P_FileType, P_UploadDate, ProjectID, P_FilePath)
                    VALUES
                    ('Project Proposal', '$fileType', CURDATE(), '$projectID', '$proposalPath')");

                    $message = "Project proposal submitted successfully. Please wait for Boss approval.";
                }
                else {
                    $message = "Error submitting project proposal: " . mysqli_error($conn);
                }
            }
        }
        else {
            $message = "Please upload a proposal file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Client Project Details</title>

<style>
body{
    background:#002b5c;
    margin:0;
    font-family:'Segoe UI',Arial,sans-serif;
}

.header{
    background:rgba(0,0,0,0.2);
    color:white;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header h1{
    margin:0;
}

.header a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.container{
    width:95%;
    max-width:1400px;
    margin:30px auto;
}

.card{
    background:white;
    border-radius:25px;
    padding:30px;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

.card h2{
    color:#003366;
    margin-top:0;
}

input, textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    margin-top:8px;
    margin-bottom:15px;
}

small{
    color:#666;
    display:block;
    margin-top:-8px;
    margin-bottom:15px;
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

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#003366;
    color:white;
    padding:12px;
}

td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

.badge{
    padding:8px 15px;
    border-radius:20px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.pending{ background:#ff9800; }
.approved{ background:#28a745; }
.rejected{ background:#dc3545; }
.progress{ background:#007bff; }
.completed{ background:#6f42c1; }

.success{
    background:#d4edda;
    color:#155724;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

.error{
    background:#f8d7da;
    color:#721c24;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}
</style>
</head>

<body>

<header class="header">
<h1>Engineer Helper Client Portal</h1>

<div>
<a href="ClientDashboard.php">Dashboard</a>
&nbsp;&nbsp;&nbsp;
<a href="LogOut.php">Logout</a>
</div>
</header>

<div class="container">

<?php if($message != "") { ?>
<div class="<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
<?php echo $message; ?>
</div>
<?php } ?>

<div class="card">
<h2>Create New Project Proposal</h2>

<form method="POST" enctype="multipart/form-data" id="projectForm">

<label>Project Name</label>
<input type="text" name="projectName" required>

<label>Project Location</label>
<input type="text" name="place" required>

<label>Start Date</label>
<input
type="date"
name="startDate"
id="startDate"
min="<?php echo date('Y-m-d', strtotime('+1 month')); ?>"
required>
<small>Start date must be at least 1 month after proposal submission.</small>

<label>End Date</label>
<input
type="date"
name="endDate"
id="endDate"
min="<?php echo date('Y-m-d'); ?>"
required>

<label>Project Proposal File (PDF)</label>
<input type="file" name="proposalFile" accept=".pdf" required>

<button type="submit" name="createProject" class="btn">
Submit Proposal
</button>

</form>
</div>

<div class="card">
<h2>My Project Proposals</h2>

<table>
<tr>
<th>ID</th>
<th>Project Name</th>
<th>Location</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Proposal</th>
</tr>

<?php
$currentProjects = mysqli_query($conn,
"SELECT *
FROM project
WHERE ClientID='$clientID'
ORDER BY ProjectID DESC");

if(mysqli_num_rows($currentProjects) > 0) {
    while($row = mysqli_fetch_assoc($currentProjects)) {

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

<td><?php echo htmlspecialchars($row['Place']); ?></td>

<td><?php echo $row['StartDate']; ?></td>

<td><?php echo $row['EndDate']; ?></td>

<td>
<span class="badge <?php echo $class; ?>">
<?php echo $status; ?>
</span>
</td>

<td>
<?php if($doc && !empty($doc['P_FilePath'])) { ?>
<a href="<?php echo $doc['P_FilePath']; ?>" target="_blank">View Proposal</a>
<?php } else { echo "-"; } ?>
</td>
</tr>

<?php
    }
} else {
?>

<tr>
<td colspan="7" style="text-align:center;">
No project proposals submitted yet.
</td>
</tr>

<?php } ?>

</table>
</div>

<div class="card">
<h2>Project History</h2>

<table>
<tr>
<th>Project ID</th>
<th>Project Name</th>
<th>Duration</th>
<th>Status</th>
<th>Latest Progress</th>
</tr>

<?php
$history = mysqli_query($conn,
"SELECT
p.*,
(
    SELECT MAX(ProgressPercentage)
    FROM project_progress
    WHERE ProjectID=p.ProjectID
) AS LatestProgress
FROM project p
WHERE p.ClientID='$clientID'
ORDER BY p.ProjectID DESC");

if(mysqli_num_rows($history) > 0) {
    while($row = mysqli_fetch_assoc($history)) {
?>

<tr>
<td><?php echo $row['ProjectID']; ?></td>

<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td>
<?php echo $row['StartDate']; ?>
<br>to<br>
<?php echo $row['EndDate']; ?>
</td>

<td><?php echo $row['Status']; ?></td>

<td>
<?php echo $row['LatestProgress'] ? $row['LatestProgress']."%" : "0%"; ?>
</td>
</tr>

<?php
    }
} else {
?>

<tr>
<td colspan="5" style="text-align:center;">
No project history found.
</td>
</tr>

<?php } ?>

</table>
</div>

</div>

<script>
document.getElementById("projectForm").addEventListener("submit", function(e){

    let startDate = new Date(document.getElementById("startDate").value);
    let endDate = new Date(document.getElementById("endDate").value);

    let minStartDate = new Date();
    minStartDate.setMonth(minStartDate.getMonth() + 1);
    minStartDate.setHours(0,0,0,0);

    if(startDate < minStartDate)
    {
        alert("Project Start Date must be at least 1 month after proposal submission.");
        e.preventDefault();
        return;
    }

    if(startDate > endDate)
    {
        alert("Project Start Date cannot be later than End Date.");
        e.preventDefault();
        return;
    }

});
</script>

</body>
</html>