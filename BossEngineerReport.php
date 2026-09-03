<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Boss') {
    header("Location: UserLogin.php");
    exit();
}

$search = "";

if(isset($_GET['report_search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['report_search']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Boss Engineer Reports</title>

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

.download{
    background:#28a745;
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
}

td{
    padding:14px;
    border-bottom:1px solid #ddd;
}

.badge{
    padding:6px 12px;
    border-radius:15px;
    color:white;
    font-size:13px;
    font-weight:bold;
}

.proposal{
    background:#ff9800;
}

.finalreport{
    background:#28a745;
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
    <a href="BossEngineerReport.php" class="active">Engineer Reports</a>
    <a href="BossViewPerformanceTracker.php">Performance Reports</a></div>

<div class="content">

<div class="card">

<h2>Project Uploaded Files</h2>

<form method="GET" class="search-box">

<input
type="text"
name="report_search"
placeholder="Search by project name or assigned engineer"
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit" class="btn">Search</button>

<a href="BossEngineerReport.php" class="btn reset">Reset</a>

</form>

</div>

<div class="card">

<h2>Project Proposals</h2>

<table>
<tr>
<th>Doc ID</th>
<th>Project Name</th>
<th>Assigned Engineer</th>
<th>Client</th>
<th>File Type</th>
<th>Upload Date</th>
<th>Action</th>
</tr>

<?php
$proposalQuery = mysqli_query($conn,
"SELECT
    d.*,
    p.ProjectName,
    s.Username AS EngineerName,
    c.ClientCompany
 FROM project_document d
 INNER JOIN project p
 ON d.ProjectID = p.ProjectID
 LEFT JOIN staff s
 ON p.StaffID = s.StaffID
 LEFT JOIN client c
 ON p.ClientID = c.ClientID
 WHERE
 (
    d.P_DocName = 'Project Proposal'
    OR d.P_DocName LIKE '%Proposal%'
 )
 AND
 (
    p.ProjectName LIKE '%$search%'
    OR s.Username LIKE '%$search%'
 )
 ORDER BY d.P_UploadDate DESC");

if(mysqli_num_rows($proposalQuery) > 0) {
    while($row = mysqli_fetch_assoc($proposalQuery)) {
?>

<tr>
<td><?php echo $row['DocID']; ?></td>

<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td>
<?php echo $row['EngineerName'] ? htmlspecialchars($row['EngineerName']) : "Unassigned"; ?>
</td>

<td><?php echo htmlspecialchars($row['ClientCompany']); ?></td>

<td>
<span class="badge proposal">
<?php echo htmlspecialchars($row['P_FileType']); ?>
</span>
</td>

<td><?php echo $row['P_UploadDate']; ?></td>

<td>
<?php if(!empty($row['P_FilePath'])) { ?>
<a class="btn download" href="<?php echo $row['P_FilePath']; ?>" target="_blank">View</a>
<?php } else { echo "-"; } ?>
</td>
</tr>

<?php
    }
} else {
?>

<tr>
<td colspan="7" style="text-align:center;">
No proposal files found.
</td>
</tr>

<?php } ?>

</table>

</div>

<div class="card">

<h2>Final Reports</h2>

<table>
<tr>
<th>Doc ID</th>
<th>Project Name</th>
<th>Assigned Engineer</th>
<th>Client</th>
<th>File Type</th>
<th>Upload Date</th>
<th>Action</th>
</tr>

<?php
$finalQuery = mysqli_query($conn,
"SELECT
    d.*,
    p.ProjectName,
    s.Username AS EngineerName,
    c.ClientCompany
 FROM project_document d
 INNER JOIN project p
 ON d.ProjectID = p.ProjectID
 LEFT JOIN staff s
 ON p.StaffID = s.StaffID
 LEFT JOIN client c
 ON p.ClientID = c.ClientID
 WHERE
 (
    d.P_DocName = 'Final Report'
    OR d.P_DocName LIKE '%Final%'
 )
 AND
 (
    p.ProjectName LIKE '%$search%'
    OR s.Username LIKE '%$search%'
 )
 ORDER BY d.P_UploadDate DESC");

if(mysqli_num_rows($finalQuery) > 0) {
    while($row = mysqli_fetch_assoc($finalQuery)) {
?>

<tr>
<td><?php echo $row['DocID']; ?></td>

<td><?php echo htmlspecialchars($row['ProjectName']); ?></td>

<td>
<?php echo $row['EngineerName'] ? htmlspecialchars($row['EngineerName']) : "Unassigned"; ?>
</td>

<td><?php echo htmlspecialchars($row['ClientCompany']); ?></td>

<td>
<span class="badge finalreport">
<?php echo htmlspecialchars($row['P_FileType']); ?>
</span>
</td>

<td><?php echo $row['P_UploadDate']; ?></td>

<td>
<?php if(!empty($row['P_FilePath'])) { ?>
<a class="btn download" href="<?php echo $row['P_FilePath']; ?>" target="_blank">View</a>
<?php } else { echo "-"; } ?>
</td>
</tr>

<?php
    }
} else {
?>

<tr>
<td colspan="7" style="text-align:center;">
No final reports found.
</td>
</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>
</html>