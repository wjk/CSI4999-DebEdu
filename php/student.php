<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

// Pull user from session
if(isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
} else {
    header("Location: login.php");
    exit;
}

// Ensure the user is a student
if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] != 'student') {
        header("Location: /debedu/teacher.php");
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
    <title>Student Portal</title>
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
        .header-2 {
            text-align: center;
            color: #5c6bc0;
            font-size: 12px;
            font-weight: bold;
        }
        .button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 120px;
            height: 80px;
            margin-bottom: 50PX;
        }
        .button:hover {
            background-color: #5c6bc0;
        }

    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class ="header">Student Portal</h1>
        <h2 class ="header-2">Welcome, <?= $_SESSION["username"] ?></h1>
        <button class="button" id = "grades">View Grades</button>

        <button class="button" id = "contact">Class Roster</button>
        <button class="button" id = "classes">Class Schedule</button>

        <a href="s-user-details.php">Your Account</a><br>
        <a href="logout.php">Log Out</a>
    </div>

    <script>
        window.onload = function() {
            document.getElementById('grades').addEventListener('click', function(event) {
                window.location.href = "s-grades.php";
            });
            document.getElementById('classes').addEventListener('click', function(event) {
                window.location.href = "s-classes.php";
            });
            document.getElementById('contact').addEventListener('click', function(event) {
                window.location.href = "s-contact.php";
            });
        };
    </script>
</body>
</html>
