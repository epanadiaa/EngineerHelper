<?php
session_start();
$_SESSION['user'] = "Irfah Nadiah"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BOQ Calculation - Engineer Helper</title>
    <style>
        body {
            background: linear-gradient(135deg, #003366 0%, #001a33 100%);
            margin: 0;
            font-family: Arial, sans-serif;
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
        }

        h3 { color: #333; margin-top: 0; }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #003366;
            color: white;
            text-align: left;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        input[type="number"] {
            width: 80px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .grand-total-box {
            margin-top: 30px;
            text-align: right;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 20px;
        }

        .btn-save {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            float: right;
        }
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
            <li class="active"><a href="BOQCalculation.php">BOQ Calculation</a></li>
            <li><a href="ProjectDetails.php">Project Details</a></li>
            <li><a href="AddWorkingHours.php">Add Working Hours</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div style="text-align: right; color: white; margin-bottom: 20px;">HOME / BOQ CALCULATION</div>

        <div class="card">
            <h3>Bill of Quantities (BOQ)</h3>
            <p style="color: #666;">Project: <strong>River Overflow Mitigation</strong></p>

            <table>
                <thead>
                    <tr>
                        <th>Description of Material</th>
                        <th>Quantity</th>
                        <th>Unit Price (RM)</th>
                        <th>Total (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cement (Grade 40)</td>
                        <td><input type="number" class="qty" value="10" oninput="calculate()"></td>
                        <td><input type="number" class="price" value="25.00" oninput="calculate()"></td>
                        <td class="item-total">250.00</td>
                    </tr>
                    <tr>
                        <td>Reinforcement Steel (12mm)</td>
                        <td><input type="number" class="qty" value="50" oninput="calculate()"></td>
                        <td><input type="number" class="price" value="4.50" oninput="calculate()"></td>
                        <td class="item-total">225.00</td>
                    </tr>
                    <tr>
                        <td>River Sand (per m3)</td>
                        <td><input type="number" class="qty" value="5" oninput="calculate()"></td>
                        <td><input type="number" class="price" value="85.00" oninput="calculate()"></td>
                        <td class="item-total">425.00</td>
                    </tr>
                </tbody>
            </table>

            <div