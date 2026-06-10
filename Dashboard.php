<?php
// 1. ALL PHP LOGIC GOES HERE AT THE VERY TOP
session_start();

// Mock data for design purposes
$_SESSION['user'] = "Irfah Nadiah"; 
$_SESSION['role'] = "Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engineer Helper Dashboard</title>
    <style>
        body {
            background: #002b5c; /* Solid dark blue background matching your reference */
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex; 
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Top Header Navigation - Updated to match first pic */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: rgba(0, 0, 0, 0.2); /* Subtle dark overlay for the header area */
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

        /* Main Container Layout */
        .layout-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar Styling - White block on the left */
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

        .sidebar ul { 
            list-style: none; 
            padding: 0; 
            margin: 0;
        }

        .sidebar li {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
            color: #d1d1d1; /* Faded gray for inactive */
            transition: color 0.3s ease;
        }

        .sidebar li a {
            text-decoration: none;
            color: inherit; 
            display: block;
        }

        .sidebar li.active { 
            color: #003366; /* Dark blue for active */
        }

        .sidebar li:hover {
            color: #003366;
        }

        /* Content Area Styling (Dark Blue Area) */
        .content-area {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Search Bar Placeholder */
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

        /* Main White Card inside the Blue Area */
        .card {
            background: white;
            border-radius: 45px; 
            padding: 50px;
            flex-grow: 1;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow-y: auto;
        }

        .card h3 { 
            color: #333; 
            font-size: 1.8rem; 
            margin-top: 0;
        }

        hr {
            border: 0;
            border-top: 1px solid #eee;
            margin: 20px 0;
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
        <div class="sidebar">
            <h2>Hi, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <div class="page-title">Project Dashboard</div>
            
            <ul>
                <li class="active"><a href="Dashboard.php">Project Dashboard</a></li>
                <li><a href="ProjectProgress.php">Project Progress</a></li>
                <li><a href="UploadReport.php">Upload Report</a></li>
                <li><a href="BOQCalculation.php">BOQ Calculation</a></li>
                <li><a href="ProjectDetails.php">Project Details</a></li>
                <li><a href="AddWorkingHours.php">Add Working Hours</a></li>
            </ul>
        </div>

        <div class="content-area">
            <div class="search-container">
                <span style="color:white; margin-right: 10px;">🔍</span>
                <input type="text" placeholder="search">
            </div>

            <div class="card">
                <h3>Project Progress Overview</h3>
                <hr>
                <p style="color: #666; font-size: 1.1rem;">Your charts and project statistics will be displayed here.</p>
            </div>
        </div>
    </div>

</body>
</html>