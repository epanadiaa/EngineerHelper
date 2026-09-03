<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Client') {
    header("Location: UserLogin.php");
    exit();
}

$clientID = $_SESSION['clientID'];
$message = "";

if (isset($_POST['upload_boq'])) {

    $projectID = $_POST['projectID'];

    if (isset($_FILES['boqFile']) && $_FILES['boqFile']['error'] == 0) {

        $folder = "uploads/BOQ/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $originalName = basename($_FILES['boqFile']['name']);
        $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = ['pdf', 'csv', 'xlsx'];

        if (!in_array($fileType, $allowed)) {
            $message = "Only PDF, CSV and XLSX BOQ files are allowed.";
        } else {

            $newFileName = time() . "_" . $originalName;
            $filePath = $folder . $newFileName;

            if (move_uploaded_file($_FILES['boqFile']['tmp_name'], $filePath)) {

                mysqli_query($conn,
                "INSERT INTO project_document
                (P_DocName, P_FileType, P_UploadDate, ProjectID, P_FilePath)
                VALUES
                ('BOQ', '$fileType', CURDATE(), '$projectID', '$filePath')");

                $message = "BOQ uploaded successfully.";
            } else {
                $message = "Failed to upload BOQ file.";
            }
        }
    } else {
        $message = "Please select a BOQ file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload BOQ</title>

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

.header a{
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.container{
    max-width:1000px;
    margin:30px auto;
}

.card{
    background:white;
    border-radius:25px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,0.25);
    margin-bottom:25px;
}

h2{
    color:#003366;
    margin-top:0;
}

label{
    font-weight:bold;
    color:#003366;
}

select,
input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    margin-top:8px;
    margin-bottom:15px;
}

.btn{
    background:#003366;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

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
</style>
</head>

<body>

<header class="header">
<h1>Engineer Helper Client Portal</h1>

<div>
<a href="ClientDashboard.php">Dashboard</a>
&nbsp;&nbsp;
<a href="ClientProjectDetails.php">Projects</a>
&nbsp;&nbsp;
<a href="LogOut.php">Logout</a>
</div>
</header>

<div class="container">

<div class="card">

<h2>Upload BOQ</h2>

<?php if($message != "") { ?>
<div class="<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'error'; ?>">
<?php echo $message; ?>
</div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

<label>Select Project</label>
<select name="projectID" required>
<option value="">-- Select Your Project --</option>

<?php
$projects = mysqli_query($conn,
"SELECT ProjectID, ProjectName
 FROM project
 WHERE ClientID='$clientID'
 AND Status!='Rejected'
 ORDER BY ProjectID DESC");

while($row = mysqli_fetch_assoc($projects)) {
    echo "<option value='".$row['ProjectID']."'>".$row['ProjectName']."</option>";
}
?>

</select>

<label>Upload BOQ File</label>
<input type="file" name="boqFile" accept=".pdf,.csv,.xlsx" required>

<button type="submit" name="upload_boq" class="btn">
Upload BOQ
</button>

</form>

</div>

<div class="card">

<h2>Uploaded BOQ Files</h2>

<table>
<tr>
<th>Project</th>
<th>File Type</th>
<th>Upload Date</th>
<th>Action</th>
</tr>

<?php
$boqDocs = mysqli_query($conn,
"SELECT d.*, p.ProjectName
 FROM project_document d
 INNER JOIN project p
 ON d.ProjectID = p.ProjectID
 WHERE p.ClientID='$clientID'
 AND d.P_DocName='BOQ'
 ORDER BY d.P_UploadDate DESC");

if(mysqli_num_rows($boqDocs) > 0) {
    while($doc = mysqli_fetch_assoc($boqDocs)) {
?>

<tr>
<td><?php echo htmlspecialchars($doc['ProjectName']); ?></td>
<td><?php echo strtoupper($doc['P_FileType']); ?></td>
<td><?php echo $doc['P_UploadDate']; ?></td>
<td>
<a href="<?php echo $doc['P_FilePath']; ?>" target="_blank">View</a>
</td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center;'>No BOQ uploaded yet.</td></tr>";
}
?>

</table>

</div>

</div>

</body>
</html>