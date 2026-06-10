<?php
session_start();
// Mock data for design consistency
$_SESSION['user'] = "Irfah Nadiah"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details - Engineer Helper</title>
    <style>
        body {
            background: #002b5c; /* Solid dark blue matching reference */
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Top Header Navigation */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
        }

        .header h1 { 
            font-size: 2.2rem; 
            margin: 0; 
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Main Layout Body */
        .layout-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* White Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            padding: 30px 25px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .sidebar h2 { 
            color: #003366; 
            font-size: 0.95rem; 
            margin: 0 0 5px 0; 
        }

        .sidebar .page-title { 
            color: #003366; 
            font-size: 1.6rem; 
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
            color: #d1d1d1;
            transition: color 0.3s ease;
        }

        .sidebar li a { text-decoration: none; color: inherit; display: block; }
        .sidebar li.active { color: #003366; }
        .sidebar li:hover { color: #003366; }

        /* Content Area (Dark Blue part) */
        .content-area {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Search Bar */
        .search-container {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #000;
            border-radius: 20px;
            padding: 5px 15px;
            width: 300px;
            margin-bottom: 30px;
        }

        .search-container input {
            background: transparent;
            border: none;
            color: white;
            padding: 8px;
            width: 100%;
            outline: none;
        }

        /* Main White Card Area */
        .card {
            background: white;
            border-radius: 45px; 
            padding: 45px;
            flex-grow: 1;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow-y: auto;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .status-badge {
            background: #e1f5fe;
            color: #01579b;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .info-item label {
            display: block;
            color: #999;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-item p {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .description-box {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 20px;
            border-left: 5px solid #003366;
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Engineer Helper</h1>
        <div class="nav-links">
            <a href="Dashboard.php">HOME</a>
            <a href="#">ABOUT</a>
            <a href="logout.php">LOG OUT</a>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar">
            <h2>Hi, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <div class="page-title">Project Details</div>
            <ul>
                <li><a href="Dashboard.php">Project Dashboard</a></li>
                <li><a href="ProjectProgress.php">Project Progress</a></li>
                <li><a href="UploadReport.php">Upload Report</a></li>
                <li><a href="BOQCalculation.php">BOQ Calculation</a></li>
                <li class="active"><a href="ProjectDetails.php">Project Details</a></li>
                <li><a href="AddWorkingHours.php">Add Working Hours</a></li>
            </ul>
        </aside>

        <main class="content-area">
            <div class="search-container">
                <span style="color:white; margin-right: 10px;">🔍</span>
                <input type="text" placeholder="search">
            </div>

            <div class="card">
                <div class="detail-header">
                    <div>
                        <h1 style="color: #003366; margin: 0;">River Overflow Mitigation</h1>
                        <p style="color: #666; margin: 5px 0;">Project ID: ENG-2026-001</p>
                    </div>
                    <div class="status-badge">ACTIVE PHASE</div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label>Client Name</label>
                        <p>Department of Irrigation and Drainage (DID)</p>
                    </div>
                    <div class="info-item">
                        <label>Lead Engineer</label>
                        <p>Irfah Nadiah</p>
                    </div>
                </div>

                <div class="description-box">
                    <label style="color: #003366; font-weight: bold; display: block; margin-bottom: 10px;">Project Scope</label>
                    <p style="color: #444; line-height: 1.6; margin: 0;">
                        This project focuses on resolving critical river overflow issues. Detailed hydraulic modeling and structural reinforcement of the riverbanks are being implemented to prevent flooding during peak monsoon periods.
                    </p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>