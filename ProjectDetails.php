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

$projects = mysqli_query($conn,
"SELECT DISTINCT
    p.*,
    c.ClientCompany,
    c.PersonInCharge,
    c.ClientPhoneNum,
    c.ClientEmail,
    pa.AssignedStartDate
FROM project p
LEFT JOIN project_assignment pa
ON p.ProjectID = pa.ProjectID
LEFT JOIN client c
ON p.ClientID = c.ClientID
WHERE pa.StaffID='$staffID'
OR p.StaffID='$staffID'
ORDER BY p.ProjectID DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Project Details</title>

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
    text-transform:uppercase;
    font-size:0.85rem;
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
    font-size:1rem;
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
    margin:0;
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
    display:block;
}

.content-area{
    flex:1;
    padding:40px 50px;
    overflow-y:auto;
}

.card{
    background:white;
    border-radius:45px;
    padding:40px;
    margin-bottom:30px;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

.project-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid #eee;
    padding-bottom:18px;
    margin-bottom:25px;
}

.project-header h2{
    color:#003366;
    margin:0;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
    margin-bottom:25px;
}

.info-box label{
    display:block;
    color:#777;
    font-size:0.8rem;
    font-weight:bold;
    text-transform:uppercase;
    margin-bottom:5px;
}

.info-box p{
    margin:0;
    color:#333;
    font-size:1rem;
    font-weight:500;
}

.badge{
    padding:7px 15px;
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
.new{ background:#ff9800; }
.delayed{ background:#dc3545; }
.duesoon{ background:#ff9800; }

.card.overdue-card{
    border:3px solid #dc3545;
}

.document-box{
    margin-top:20px;
    background:#f8f9fa;
    padding:20px;
    border-left:5px solid #003366;
    border-radius:20px;
}

.document-box h3{
    color:#003366;
    margin-top:0;
}

.doc-link{
    display:inline-block;
    margin:5px 10px 5px 0;
    background:#003366;
    color:white;
    padding:9px 15px;
    border-radius:10px;
    text-decoration:none;
    font-weight:bold;
}

.empty{
    background:white;
    border-radius:45px;
    padding:50px;
    text-align:center;
    color:#777;
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
<div class="page-title">Project Details</div>

<ul>
<li><a href="DashboardEngineer.php">Project Dashboard</a></li>
<li><a href="ProjectProgress.php">Project Progress</a></li>
<li class="active"><a href="ProjectDetails.php">Project Details</a></li>
<li><a href="UploadReport.php">Upload Report</a></li>
<li><a href="Finalreport.php">Report Generator</a></li>
<li><a href="AddWorkingHours.php">Add Working Hours</a></li>
</ul>

</aside>

<main class="content-area">

<?php
if(mysqli_num_rows($projects) > 0) {
    while($row = mysqli_fetch_assoc($projects)) {

        $status = $row['Status'];
        $statusClass = "pending";

        if($status == "Approved") $statusClass = "approved";
        elseif($status == "Rejected") $statusClass = "rejected";
        elseif($status == "In Progress") $statusClass = "progress";
        elseif($status == "Completed") $statusClass = "completed";

        $isNew = false;

        if(!empty($row['AssignedStartDate'])) {
            $assignedDate = strtotime($row['AssignedStartDate']);
            $sevenDaysAgo = strtotime('-7 days');

            if($assignedDate >= $sevenDaysAgo) {
                $isNew = true;
            }
        }

        // Auto-tracking flag: compare EndDate to today, skip if already completed
        $isOverdue = false;
        $isDueSoon = false;
        $daysDiff = null;

        if(!empty($row['EndDate']) && $row['Status'] !== 'Completed') {
            $daysDiff = (strtotime($row['EndDate']) - strtotime(date('Y-m-d'))) / 86400;
            if($daysDiff < 0) {
                $isOverdue = true;
            } elseif($daysDiff <= 7) {
                $isDueSoon = true;
            }
        }

        $projectID = $row['ProjectID'];
?>

<div class="card<?php echo $isOverdue ? ' overdue-card' : ''; ?>">

<div class="project-header">
    <div>
        <h2><?php echo htmlspecialchars($row['ProjectName']); ?></h2>
        <p style="color:#666;margin:5px 0 0;">
            Project ID: <?php echo $row['ProjectID']; ?>
        </p>
    </div>

    <div>
        <?php if($isOverdue) { ?>
            <span class="badge delayed"><?php echo abs(round($daysDiff)); ?> DAY(S) OVERDUE</span>
        <?php } elseif($isDueSoon) { ?>
            <span class="badge duesoon">DUE IN <?php echo round($daysDiff); ?> DAY(S)</span>
        <?php } elseif($isNew) { ?>
            <span class="badge new">NEW</span>
        <?php } ?>

        <span class="badge <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($row['Status']); ?>
        </span>
    </div>
</div>

<div class="info-grid">

<div class="info-box">
<label>Location</label>
<p><?php echo htmlspecialchars($row['Place']); ?></p>
</div>

<div class="info-box">
<label>Assigned Date</label>
<p><?php echo $row['AssignedStartDate'] ? $row['AssignedStartDate'] : "-"; ?></p>
</div>

<div class="info-box">
<label>Start Date</label>
<p><?php echo $row['StartDate']; ?></p>
</div>

<div class="info-box">
<label>End Date</label>
<p><?php echo $row['EndDate']; ?></p>
</div>

<div class="info-box">
<label>Client Company</label>
<p><?php echo htmlspecialchars($row['ClientCompany']); ?></p>
</div>

<div class="info-box">
<label>Person In Charge</label>
<p><?php echo htmlspecialchars($row['PersonInCharge']); ?></p>
</div>

<div class="info-box">
<label>Client Phone</label>
<p><?php echo htmlspecialchars($row['ClientPhoneNum']); ?></p>
</div>

<div class="info-box">
<label>Client Email</label>
<p><?php echo htmlspecialchars($row['ClientEmail']); ?></p>
</div>

</div>

<div class="document-box">
<h3>Project Documents</h3>

<?php
$docs = mysqli_query($conn,
"SELECT *
FROM project_document
WHERE ProjectID='$projectID'
ORDER BY P_UploadDate DESC");

if(mysqli_num_rows($docs) > 0) {
    while($doc = mysqli_fetch_assoc($docs)) {
?>

<a class="doc-link"
href="<?php echo $doc['P_FilePath']; ?>"
target="_blank">
<?php echo htmlspecialchars($doc['P_DocName']); ?>
</a>

<?php
    }
} else {
    echo "<p>No documents uploaded yet.</p>";
}
?>

</div>

</div>

<?php
    }
} else {
?>

<div class="empty">
<h2>No Assigned Project</h2>
<p>You currently do not have any assigned project.</p>
</div>

<?php } ?>

</main>

</div>

</body>
</html>