<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

// Ensure the user is a teacher
if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] != 'teacher') {
        header("Location: /debedu/student.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html> 
<html>
<head>
    <title>Teacher Portal</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f2f2;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .choice-container {
            width: 300px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .header {
            text-align: center;
            margin-bottom: -10px;
            color: #5c6bc0;
            font-size: 24px;
            font-weight: bold;
        }
        .sub-header {
            text-align: center;
            margin-bottom: 5px;
            color: #5c6bc0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 80px;
            height: 40px;
            margin-bottom: 10PX;
        }
        .button:hover {
            background-color: #5c6bc0;
        }
        .button-container{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class ="header">Teacher Portal</h1>
        <h3 class ="sub-header">Grade View</h3>
        <table>
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="name">inject</td>
                    <td id="class">data</td>
                    <td id="email">here</td>
                    
                </tr>
            </tbody>
        </table>
        <div class="button-container">
        <button class="button" id = "back">Back</button>
        <button class="button" id = "nas">+</button>
        <button class="button" id = "egrade">Enter Grades</button>
    </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
        };
    </script>
</body>
</html>
