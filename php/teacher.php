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

function get_real_name($conn, $user_name) {
    $stmt = $conn->prepare(
        "SELECT TEACHER_USER.REAL_NAME AS REAL_NAME " .
        "FROM TEACHER_USER " .
        "WHERE TEACHER_USER.USER_NAME = ?;"
    );
    $stmt->bind_param("s", $user_name);

    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!isset($row["REAL_NAME"])) {
        die("REAL_NAME for user " . $user_name . " not returned from database");
    }
    return $row["REAL_NAME"];
}?>
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
        <h1 class ="header">Teacher Portal</h1>
        <h2 class ="header-2">Welcome, <?= get_real_name($conn, $_SESSION["username"]) ?></h1>
        <button class="button" id="assignments">Assignments</button>
        <button class="button" id = "grades">Submit Grades</button>

        <button class="button" id = "students">Students</button>
        <button class="button" id = "more">More</button>

        <a href="t-user-details.php">Your Account</a><br>
        <a href="logout.php">Log Out</a>
    </div>

    <script>
        window.onload = function() {
            document.getElementById('grades').addEventListener('click', function(event) {
                window.location.href = "t-grades.php";
            });
            document.getElementById('students').addEventListener('click', function(event) {
                window.location.href = "t-students.php";
            });
            document.getElementById('assignments').addEventListener('click', function(event) {
                window.location.href = "t-assignments.php";
            });
            document.getElementById('more').addEventListener('click', function(event) {
                window.location.href = "t-more.php";
            });
        };
    </script>
</body>
</html>
