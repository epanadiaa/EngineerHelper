<?php
session_start();
// Replace with your database connection or session check
$_SESSION['user'] = "clientname"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engineer Helper - Project Details</title>
    <style>
        body {
            background: #003366; /* Deep blue background from image */
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
            padding: 10px 50px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
        }

        .header h1 { font-size: 2.2rem; margin: 0; }

        .nav-links {
            display: flex;
            gap: 40px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            text-transform: uppercase;
        }

        /* Main Layout Body */
        .layout-container {
            display: flex;
            flex: 1;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 { color: #003366; font-size: 1rem; margin-bottom: 20px; }
        .sidebar .project-title { 
            color: #003366; 
            font-size: 1.6rem; 
            font-weight: 800;
            text-transform: uppercase;
        }

        /* Center Content Area */
        .content-area {
            flex: 1;
            padding: 30px 50px;
            display: flex;
            flex-direction: column;
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
            margin-bottom: 40px;
        }

        .search-container input {
            background: transparent;
            border: none;
            color: white;
            padding: 8px;
            width: 100%;
            outline: none;
            font-size: 1.1rem;
        }

        .search-container input::placeholder { color: white; }

        /* Button Navigation Row */
        .button-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .view-btn {
            background: #7395AE; /* Muted blue/gray from image */
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 35px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            text-align: center;
            width: 200px;
            line-height: 1.2;
            transition: transform 0.2s;
        }

        .view-btn:hover {
            transform: scale(1.05);
            background: #557A95;
        }

        /* Main White Display Card */
        .display-card {
            background: white;
            border-radius: 50px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }

        /* Loading Spinner Placeholder */
        .loader {
            border: 12px solid #f3f3f3;
            border-top: 12px solid #555;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Engineer Helper</h1>
        <div class="nav-links">
            <a href="Dashboard.php">Home</a>
            <a href="#">About</a>
            <a href="logout.php">Log Out</a>
        </div>
    </header>

    <div class="layout-container">
        <aside class="sidebar">
            <h2>Hi, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <div class="project-title">Project Details</div>
        </aside>

        <main class="content-area">
            
            <div class="search-container">
                <span style="color:white; font-size: 1.2rem; margin-right: 10px;">🔍</span>
                <input type="text" placeholder="search">
            </div>

            <div class="button-row">
                <button class="view-btn">view project<br>proposal</button>
                <button class="view-btn">view project<br>SRS</button>
                <button class="view-btn">view final report</button>
            </div>

            <div class="display-card">
                <div class="loader"></div>
            </div>

        </main>
    </div>

</body>
</html>