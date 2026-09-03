<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Client') {
    header("Location: UserLogin.php");
    exit();
}

$client_id = $_SESSION['clientID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Project Reports - Client Portal</title>
    <style>
        body { background: #002b5c; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        h2 { color: #003366; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #003366; color: white; padding: 12px; text-align: left; font-size: 0.9rem; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        .btn-download { background: #28a745; color: white; text-decoration: none; padding: 6px 12px; border-radius: 5px; font-weight: bold; font-size: 0.85rem; }
        .btn-download:hover { background: #218838; }
        .back-btn { display: inline-block; margin-bottom: 20px; color: white; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="ClientDashboard.php" class="back-btn">← Back to Dashboard</a>
        <div class="card">
            <h2>Available Handover & Progress Documentation</h2>
            <table>
                <thead>
                    <tr>
                        <th>Associated Project</th>
                        <th>Document Title</th>
                        <th>File Extension</th>
                        <th>Upload Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $docs = mysqli_query($conn, "
                        SELECT d.*, p.ProjectName 
                        FROM project_document d 
                        JOIN project p ON d.ProjectID = p.ProjectID 
                        WHERE p.ClientID = '$client_id'
                        ORDER BY d.P_UploadDate DESC
                    ");
                    
                    if(mysqli_num_rows($docs) > 0) {
                        while($dRow = mysqli_fetch_assoc($docs)) {
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($dRow['ProjectName'])."</td>";
                            echo "<td><b>".htmlspecialchars($dRow['P_DocName'])."</b></td>";
                            echo "<td>.".$dRow['P_FileType']."</td>";
                            echo "<td>".$dRow['P_UploadDate']."</td>";
                            echo "<td><a href='".htmlspecialchars($dRow['P_FilePath'])."' class='btn-download' download>Download Document</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; color:#999;'>No deliverables or reports have been uploaded for your projects yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>