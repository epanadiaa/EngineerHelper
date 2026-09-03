<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: UserLogin.php");
    exit();
}

$message = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'documents';

/* DOCUMENT UPLOAD */
if (isset($_POST['upload_doc'])) {
    $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);
    $doc_name = mysqli_real_escape_string($conn, $_POST['doc_name']);
    
    if (isset($_FILES['proj_file']) && $_FILES['proj_file']['error'] == 0) {
        $file_name = $_FILES['proj_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (!in_array($file_ext, $allowed_ext)) {
            $message = "<div class='alert error'>File type not allowed. Use PDF, Word, Excel, or image files only.</div>";
        } elseif ($_FILES['proj_file']['size'] > $max_size) {
            $message = "<div class='alert error'>File is too large (max 10MB).</div>";
        } else {
        $target_dir = "uploads/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . time() . "_" . basename($file_name);
        
        if (move_uploaded_file($_FILES['proj_file']['tmp_name'], $target_file)) {
            $today = date('Y-m-d');

            $insertDoc = "INSERT INTO project_document 
            (P_DocName, P_FileType, P_UploadDate, ProjectID, P_FilePath) 
            VALUES 
            ('$doc_name', '$file_ext', '$today', '$project_id', '$target_file')";

            if (mysqli_query($conn, $insertDoc)) {
                $message = "<div class='alert success'>Document uploaded successfully!</div>";
            } else {
                $message = "<div class='alert error'>Database Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='alert error'>Failed to move uploaded file.</div>";
        }
        }
    }
}

/* ADD STAFF */
if (isset($_POST['admin_add_staff'])) {
    $s_username = trim(mysqli_real_escape_string($conn, $_POST['staff_username']));
    $s_password = password_hash($_POST['staff_password'], PASSWORD_DEFAULT);
    $s_role = mysqli_real_escape_string($conn, $_POST['staff_role']);
    $s_position = trim(mysqli_real_escape_string($conn, $_POST['staff_position']));

    $checkStaff = mysqli_query($conn, "SELECT 1 FROM staff WHERE Username = '$s_username'");

    if (mysqli_num_rows($checkStaff) > 0) {
        $message = "<div class='alert error'>Staff Username already exists.</div>";
    } else {
        $insertStaff = "INSERT INTO staff (Username, Password, Role, Position) 
        VALUES ('$s_username', '$s_password', '$s_role', '$s_position')";

        if (mysqli_query($conn, $insertStaff)) {
            $message = "<div class='alert success'>New staff registered successfully!</div>";
        }
    }
}

/* ADD CLIENT */
if (isset($_POST['admin_add_client'])) {
    $person = trim(mysqli_real_escape_string($conn, $_POST['person']));
    $company = trim(mysqli_real_escape_string($conn, $_POST['company']));
    $phone = trim(mysqli_real_escape_string($conn, $_POST['phone']));
    $address = trim(mysqli_real_escape_string($conn, $_POST['address']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $c_username = trim(mysqli_real_escape_string($conn, $_POST['client_username']));
    $c_password = password_hash($_POST['client_password'], PASSWORD_DEFAULT);

    $checkUser = mysqli_query($conn, "SELECT 1 FROM client_account WHERE Username = '$c_username'");
    $checkEmail = mysqli_query($conn, "SELECT 1 FROM client WHERE ClientEmail = '$email'");

    if (mysqli_num_rows($checkUser) > 0) {
        $message = "<div class='alert error'>Client Username already taken.</div>";
    } elseif (mysqli_num_rows($checkEmail) > 0) {
        $message = "<div class='alert error'>Client email is already registered.</div>";
    } else {
        mysqli_query($conn, 
        "INSERT INTO client 
        (PersonInCharge, ClientCompany, ClientPhoneNum, ClientAddress, ClientEmail) 
        VALUES 
        ('$person', '$company', '$phone', '$address', '$email')");

        $newClientID = $conn->insert_id;
        
        mysqli_query($conn, 
        "INSERT INTO client_account 
        (ClientID, Username, Password) 
        VALUES 
        ('$newClientID', '$c_username', '$c_password')");

        $message = "<div class='alert success'>Client account registered successfully!</div>";
    }
}

/* DELETE STAFF */
if (isset($_GET['delete_staff'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_staff']);
    mysqli_query($conn, "DELETE FROM staff WHERE StaffID = '$id'");
    header("Location: DashboardAdmin.php?tab=accounts");
    exit();
}

/* DELETE CLIENT */
if (isset($_GET['delete_client'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_client']);
    mysqli_query($conn, "DELETE FROM client WHERE ClientID = '$id'");
    header("Location: DashboardAdmin.php?tab=accounts");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Engineer Helper</title>

<style>
body {
    background:#002b5c;
    margin:0;
    font-family:'Segoe UI', Arial, sans-serif;
    display:flex;
    flex-direction:column;
    min-height:100vh;
}

.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    background:rgba(0,0,0,0.2);
    color:white;
}

.header h1 {
    font-size:2.2rem;
    margin:0;
}

.nav-links a {
    color:white;
    text-decoration:none;
    font-weight:bold;
    text-transform:uppercase;
    font-size:0.85rem;
}

.layout-container {
    display:flex;
    flex:1;
    overflow:hidden;
}

.sidebar {
    width:280px;
    background:white;
    padding:30px 25px;
    box-sizing:border-box;
    height:calc(100vh - 80px);
    overflow-y:auto;
}

.sidebar h2 {
    color:#003366;
    font-size:1.25rem;
    margin-bottom:20px;
    font-weight:bold;
}

.sidebar .page-title {
    color:#003366;
    font-size:1.6rem;
    font-weight:900;
    text-transform:uppercase;
    margin-bottom:40px;
}

.sidebar ul {
    list-style:none;
    padding:0;
    margin:0;
}

.sidebar li {
    font-size:1.15rem;
    font-weight:800;
    margin-bottom:45px;
    text-transform:uppercase;
    color:#d1d1d1;
}

.sidebar li a {
    text-decoration:none;
    color:inherit;
    display:block;
}

.sidebar li.active,
.sidebar li:hover {
    color:#003366;
}

.content-area {
    flex:1;
    padding:40px;
    box-sizing:border-box;
    overflow:auto;
    height:calc(100vh - 80px);
}

.card {
    background:white;
    border-radius:40px;
    padding:35px;
    margin-bottom:25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

.card h3 {
    color:#003366;
    margin-top:0;
    font-size:1.6rem;
    margin-bottom:20px;
    border-bottom:2px solid #f0f0f0;
    padding-bottom:10px;
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
    margin-bottom:15px;
}

th {
    background:#003366;
    color:white;
    padding:14px;
    text-align:left;
    text-transform:uppercase;
    font-size:0.85rem;
}

td {
    padding:14px;
    border-bottom:1px solid #eee;
    color:#444;
    font-size:0.95rem;
}

.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.form-group {
    margin-bottom:20px;
}

.form-group.full-width {
    grid-column:span 2;
}

.form-group label {
    display:block;
    margin-bottom:8px;
    font-weight:bold;
    color:#003366;
}

input,
select,
textarea {
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    font-size:1rem;
    box-sizing:border-box;
    outline:none;
}

textarea {
    height:80px;
    resize:vertical;
}

.btn {
    background:#004aad;
    color:white;
    padding:12px 25px;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    text-transform:uppercase;
}

.btn:hover {
    background:#003366;
}

.btn.danger {
    background:#dc3545;
    font-size:0.8rem;
    padding:6px 12px;
    text-decoration:none;
    border-radius:5px;
    color:white;
    display:inline-block;
}

.alert {
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    font-weight:bold;
}

.alert.success {
    background:#d4edda;
    color:#155724;
}

.alert.error {
    background:#f8d7da;
    color:#721c24;
}

.report-summary {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.summary-box {
    background:#f8f9fa;
    border-left:6px solid #003366;
    padding:20px;
    border-radius:15px;
    text-align:center;
}

.summary-box h2 {
    margin:0;
    color:#004aad;
    font-size:2.5rem;
}

.summary-box p {
    margin:5px 0 0;
    color:#666;
    font-weight:bold;
    text-transform:uppercase;
}

.section-split {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:30px;
    margin-bottom:30px;
}

@media (max-width:1024px) {
    .section-split {
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<header class="header">
    <h1>Engineer Helper</h1>
    <div class="nav-links">
        <a href="LogOut.php">LOG OUT</a>
    </div>
</header>

<div class="layout-container">

<aside class="sidebar">
    <h2>Administration</h2>
    <div class="page-title">Admin Hub</div>

    <ul>
        <li class="<?php echo $active_tab == 'documents' ? 'active' : ''; ?>">
            <a href="DashboardAdmin.php?tab=documents">Manage Documents</a>
        </li>

        <li class="<?php echo $active_tab == 'accounts' ? 'active' : ''; ?>">
            <a href="DashboardAdmin.php?tab=accounts">Manage Users</a>
        </li>

        <li class="<?php echo $active_tab == 'reports' ? 'active' : ''; ?>">
            <a href="DashboardAdmin.php?tab=reports">System Reports</a>
        </li>
    </ul>
</aside>

<main class="content-area">

<?php echo $message; ?>

<?php if ($active_tab == 'documents'): ?>

<div class="card">
    <h3>Upload Project Documents</h3>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label>Target Project</label>
            <select name="project_id" required>
                <option value="">-- Choose Project --</option>

                <?php
                $projs = mysqli_query($conn, "SELECT ProjectID, ProjectName FROM project ORDER BY ProjectName ASC");
                while($p = mysqli_fetch_assoc($projs)) {
                    echo "<option value='".$p['ProjectID']."'>".htmlspecialchars($p['ProjectName'])."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Document Name</label>
            <input type="text" name="doc_name" placeholder="e.g., Supporting Document" required>
        </div>

        <div class="form-group">
            <label>Select Document File</label>
            <input type="file" name="proj_file" required>
        </div>

        <button type="submit" name="upload_doc" class="btn">Upload Document</button>

    </form>
</div>

<div class="card">
    <h3>Uploaded Documents</h3>

    <table>
        <tr>
            <th>Doc ID</th>
            <th>Project</th>
            <th>Document Name</th>
            <th>File Type</th>
            <th>Upload Date</th>
            <th>Action</th>
        </tr>

        <?php
        $docs = mysqli_query($conn,
        "SELECT d.*, p.ProjectName
        FROM project_document d
        INNER JOIN project p ON d.ProjectID = p.ProjectID
        ORDER BY d.P_UploadDate DESC");

        if(mysqli_num_rows($docs) > 0) {
            while($d = mysqli_fetch_assoc($docs)) {
        ?>

        <tr>
            <td><?php echo $d['DocID']; ?></td>
            <td><?php echo htmlspecialchars($d['ProjectName']); ?></td>
            <td><?php echo htmlspecialchars($d['P_DocName']); ?></td>
            <td><?php echo strtoupper($d['P_FileType']); ?></td>
            <td><?php echo $d['P_UploadDate']; ?></td>
            <td>
                <a href="<?php echo $d['P_FilePath']; ?>" target="_blank">View</a>
            </td>
        </tr>

        <?php
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>No documents uploaded yet.</td></tr>";
        }
        ?>
    </table>
</div>

<?php endif; ?>

<?php if ($active_tab == 'accounts'): ?>

<div class="section-split">

    <div class="card">
        <h3>Add New Staff</h3>

        <form method="POST">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="staff_username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="staff_password" required>
            </div>

            <div class="form-group">
                <label>System Role</label>
                <select name="staff_role">
                    <option value="Engineer">Engineer</option>
                    <option value="Admin">Admin</option>
                    <option value="Boss">Boss</option>
                </select>
            </div>

            <div class="form-group">
                <label>Position</label>
                <input type="text" name="staff_position" placeholder="e.g., Civil Engineer" required>
            </div>

            <button type="submit" name="admin_add_staff" class="btn" style="width:100%;">
                Register Staff
            </button>

        </form>
    </div>

    <div class="card">
        <h3>Add New Client</h3>

        <form method="POST">

            <div class="form-group">
                <label>Person In Charge</label>
                <input type="text" name="person" required>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address"></textarea>
            </div>

            <div class="form-group">
                <label>Account Username</label>
                <input type="text" name="client_username" required>
            </div>

            <div class="form-group">
                <label>Account Password</label>
                <input type="password" name="client_password" required>
            </div>

            <button type="submit" name="admin_add_client" class="btn" style="width:100%;">
                Register Client
            </button>

        </form>
    </div>

</div>

<div class="card">
    <h3>Registered Staff</h3>

    <table>
        <tr>
            <th>Staff ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Position</th>
            <th>Action</th>
        </tr>

        <?php
        $staffs = mysqli_query($conn, "SELECT * FROM staff ORDER BY StaffID ASC");
        while($s = mysqli_fetch_assoc($staffs)) {
        ?>

        <tr>
            <td><?php echo $s['StaffID']; ?></td>
            <td><?php echo htmlspecialchars($s['Username']); ?></td>
            <td><?php echo htmlspecialchars($s['Role']); ?></td>
            <td><?php echo htmlspecialchars($s['Position']); ?></td>
            <td>
                <a href="DashboardAdmin.php?tab=accounts&delete_staff=<?php echo $s['StaffID']; ?>"
                   class="btn danger"
                   onclick="return confirm('Confirm delete staff?')">
                   Delete
                </a>
            </td>
        </tr>

        <?php } ?>
    </table>
</div>

<div class="card">
    <h3>Registered Clients</h3>

    <table>
        <tr>
            <th>Client ID</th>
            <th>Company</th>
            <th>Person In Charge</th>
            <th>Email</th>
            <th>Action</th>
        </tr>

        <?php
        $clients = mysqli_query($conn, "SELECT * FROM client ORDER BY ClientID ASC");
        while($c = mysqli_fetch_assoc($clients)) {
        ?>

        <tr>
            <td><?php echo $c['ClientID']; ?></td>
            <td><?php echo htmlspecialchars($c['ClientCompany']); ?></td>
            <td><?php echo htmlspecialchars($c['PersonInCharge']); ?></td>
            <td><?php echo htmlspecialchars($c['ClientEmail']); ?></td>
            <td>
                <a href="DashboardAdmin.php?tab=accounts&delete_client=<?php echo $c['ClientID']; ?>"
                   class="btn danger"
                   onclick="return confirm('Confirm delete client?')">
                   Delete
                </a>
            </td>
        </tr>

        <?php } ?>
    </table>
</div>

<?php endif; ?>

<?php if ($active_tab == 'reports'): ?>

<div class="card">
    <h3>System Reports</h3>

    <?php
    $totalProjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM project"))['total'];
    $activeProjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM project WHERE Status='In Progress'"))['total'];
    $totalStaff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM staff WHERE Role='Engineer'"))['total'];
    $totalDocs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM project_document"))['total'];
    ?>

    <div class="report-summary">
        <div class="summary-box">
            <h2><?php echo $totalProjects; ?></h2>
            <p>Total Projects</p>
        </div>

        <div class="summary-box">
            <h2><?php echo $activeProjects; ?></h2>
            <p>Active Projects</p>
        </div>

        <div class="summary-box">
            <h2><?php echo $totalStaff; ?></h2>
            <p>Engineers</p>
        </div>

        <div class="summary-box">
            <h2><?php echo $totalDocs; ?></h2>
            <p>Documents</p>
        </div>
    </div>

    <h3>Project Records</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Project Name</th>
            <th>Location</th>
            <th>Status</th>
            <th>Client</th>
        </tr>

        <?php
        $reportRun = mysqli_query($conn,
        "SELECT p.*, c.ClientCompany
        FROM project p
        LEFT JOIN client c ON p.ClientID = c.ClientID
        ORDER BY p.ProjectID DESC");

        while($r = mysqli_fetch_assoc($reportRun)) {
        ?>

        <tr>
            <td><?php echo $r['ProjectID']; ?></td>
            <td><b><?php echo htmlspecialchars($r['ProjectName']); ?></b></td>
            <td><?php echo htmlspecialchars($r['Place']); ?></td>
            <td><?php echo htmlspecialchars($r['Status']); ?></td>
            <td><?php echo htmlspecialchars($r['ClientCompany']); ?></td>
        </tr>

        <?php } ?>
    </table>

    <br>
    <button onclick="window.print()" class="btn">Print Report</button>

</div>

<?php endif; ?>

</main>
</div>

</body>
</html>