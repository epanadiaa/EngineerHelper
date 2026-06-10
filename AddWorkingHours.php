<?php
session_start();
$_SESSION['user'] = "Irfah Nadiah"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Working Hours - Engineer Helper</title>
    <style>
        body {
            background: linear-gradient(135deg, #003366 0%, #001a33 100%);
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex; 
            height: 100vh;
        }
        
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
            overflow-y: auto;
        }

        .card {
            background: white;
            border-radius: 40px; 
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

        h3 { color: #003366; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group { flex: 1; }

        label { display: block; font-weight: bold; color: #555; margin-bottom: 8px; font-size: 0.9rem; }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea { height: 100px; resize: none; }

        .submit-btn {
            background: #003366;
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover { background: #00509e; transform: translateY(-2px); }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #999; font-size: 0.8rem; padding: 10px; border-bottom: 2px solid #eee; }
        td { padding: 15px 10px; border-bottom: 1px solid #eee; color: #333; font-size: 0.95rem; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Engineer Helper</h2>
        <p>welcome, <?php echo $_SESSION['user']; ?>.</p>
        <ul>
            <li><a href="Dashboard.php">Project Dashboard</a></li>
            <li><a href="ProjectProgress.php">Project Progress</a></li>
            <li><a href="UploadReport.php">Upload Report</a></li>
            <li><a href="BOQCalculation.php">BOQ Calculation</a></li>
            <li><a href="ProjectDetails.php">Project Details</a></li>
            <li class="active"><a href="AddWorkingHours.php">Add Working Hours</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="text-align: right; color: white; margin-bottom: 20px;">HOME / LOG HOURS</div>

        <div class="card">
            <h3>Log Daily Working Hours</h3>
            <form action="save_hours.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="work_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hours Spent</label>
                        <input type="number" name="hours" step="0.5" min="0.5" max="24" placeholder="e.g. 7.5" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Task Category</label>
                    <select name="category">
                        <option>Site Inspection</option>
                        <option>BOQ Preparation</option>
                        <option>Documentation</option>
                        <option>Meeting with Client</option>
                        <option>Technical Design</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Work Description</label>
                    <textarea name="description" placeholder="Briefly describe what you worked on..."></textarea>
                </div>