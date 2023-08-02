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
            margin-bottom: 30px;
            color: #5c6bc0;
            font-size: 24px;
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
        <h1 class ="header">Teacher Portal</h1>
        <button class="button" id = "back">Back</button>
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
