<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: UserLogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Progress - Engineer Helper</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #002b5c; 
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

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

        .layout-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

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

        .content-area {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .search-container {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #000;
            border-radius: 20px;
            padding: 5px 15px;
            width: 300px;
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .search-container input {
            background: transparent;
            border: none;
            color: white;
            padding: 8px;
            width: 100%;
            outline: none;
        }

        .card {
            background: white;
            border-radius: 45px; 
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .card h3 { color: #003366; margin-top: 0; font-size: 1.4rem; }

        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Engineer Helper</h1>
        <div class="nav-links">
            <a href="DashboardEngineer.php">HOME</a>
            <a href="#">ABOUT</a>
            <a href="logout.php">LOG OUT</a>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar">
            <h2>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <div class="page-title">Project Progress</div>
            <ul>
                <li><a href="DashboardEngineer.php">Project Dashboard</a></li>
                <li class="active"><a href="ProjectProgress.php">Project Progress</a></li>
                <li><a href="ProjectDetails.php">Project Details</a></li>
                <li><a href="UploadReport.php">Upload Report</a></li>
                <li><a href="BOQCalculation.php">BOQ Calculation</a></li>
                <li><a href="AddWorkingHours.php">Add Working Hours</a></li>
            </ul>
        </aside>

        <main class="content-area">
            <div class="search-container">
                <span style="color:white; margin-right: 10px;">🔍</span>
                <input type="text" placeholder="search">
            </div>

            <div class="card">
                <h3>Overall Project Status</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3>Task Completion by Month</h3>
                <div class="chart-container">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ctx1 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['Completed', 'In Progress', 'Delayed'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: { maintainAspectRatio: false }
        });

        const ctx2 = document.getElementById('timelineChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{
                    label: 'Tasks Finished',
                    data: [12, 19, 3, 5, 20],
                    backgroundColor: '#003366'
                }]
            },
            options: { maintainAspectRatio: false }
        });
    </script>
</body>
</html>