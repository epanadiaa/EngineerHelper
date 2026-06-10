<?php
session_start();
// Mock data for the session
$_SESSION['user'] = "Irfah Nadiah"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Report - Engineer Helper</title>
    <style>
        body {
            background: linear-gradient(135deg, #003366 0%, #001a33 100%);
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex; 
            height: 100vh;
        }
        
        /* Sidebar matches your Dashboard and Progress pages */
        .sidebar {
            width: 25%;
            background: white;
            padding: 40px 20px;
            color: #003366;
        }

        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li {
            font-size: 1.1rem;
            font-weight: bold;
            color: #d1d1d1;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        .sidebar li.active { color: #003366; }
        .sidebar a { text-decoration: none; color: inherit; }

        .main-content {
            width: 75%;
            padding: 40px;
        }

        /* The white card with rounded corners from your sketch */
        .card {
            background: white;
            border-radius: 40px; 
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 600px;
        }

        h3 { color: #333; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }

        .form-group { margin-bottom: 20px; }

        label { display: block; font-weight: bold; color: #003366; margin-bottom: 8px; }

        select, input[type="text"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .upload-btn {
            background: #003366;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .upload-btn:hover { background: #00509e; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Engineer Helper</h2>
        <p>welcome, <?php echo $_SESSION['user']; ?>.</p>
        <ul>
            <li><a href="Dashboard.php">Project Dashboard</a></li>
            <li><a href="ProjectProgress.php">Project Progress</a></li>
            <li class="active"><a href="UploadReport.php">Upload Report</a></li>
            <li><a href="BOQCalculation.php">BOQ Calculation</a></li>
            <li><a href="ProjectDetails.php">Project Details</a></li>
            <li><a href="AddWorkingHours.php">Add Working Hours</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="text-align: right; color: white; margin-bottom: 20px;">HOME / UPLOAD REPORT</div>

        <div class="card">
            <h3>Submit Engineering Report</h3>
            
            <form action="upload_process.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="project">Select Project</label>
                    <select name="project_name" id="project">
                        <option value="overflow">River Overflow Mitigation</option>
                        <option value="drainage">Drainage System Upgrade</option>
                        <option value="bridge">Structural Bridge Inspection</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Report Title</label>
                    <input type="text" id="title" name="report_title" placeholder="e.g. Weekly Site Analysis">
                </div>

                <div class="form-group">
                    <label for="file">Choose Document (PDF or Word)</label>
                    <input type="file" id="file" name="report_file" accept=".pdf,.doc,.docx">
                </div>

                <button type="submit" class="upload-btn">UPLOAD TO SYSTEM</button>
            </form>
        </div>
    </div>

</body>
</html>