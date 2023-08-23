<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

// Ensure the user is a teacher
if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] != 'teacher') {
        header("Location: /debedu/teacher.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

function userNumberFromUserName($conn, $username) {
    $stmt = $conn->prepare("SELECT USER_NUMBER FROM TEACHER_USER WHERE USER_NAME = ?;");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $values = $result->fetch_assoc();
    return $values['USER_NUMBER'];
}
function getSemesters($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT SEMESTER FROM EDU_CLASS;");
    $stmt->execute();

    $result = $stmt->get_result();
    $semesters = [];
    while($row = $result->fetch_assoc()) {
        $semesters[] = $row['SEMESTER'];
    }
    return $semesters;
}
function get_teacher_id($conn, $user_name) {
    $stmt = $conn->prepare(
        "SELECT USER_NUMBER " .
        "FROM TEACHER_USER " .
        "WHERE USER_NAME = ?;"
    );
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["USER_NUMBER"];
}

function get_all_students($conn) {
    $stmt = $conn->prepare("SELECT USER_NUMBER, REAL_NAME FROM STUDENT_USER;");
    $stmt->execute();

    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    return $students;
}

function get_classes($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT CLASS_NUMBER, DESCRIPTION, TITLE, SEMESTER " .
        "FROM EDU_CLASS " .
        "WHERE TEACHER_NUMBER = ?"
    );
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    return $classes;
}

$user_name = $_SESSION['username'];
$teacher_id = get_teacher_id($conn, $user_name);

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
            width: 400px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }

        .header {
            color: #5c6bc0;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-label {
            text-align: left;
            color: #5c6bc0;
            font-size: 14px;
            margin-bottom: 5px;
        }

        select, input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .button {
            font-size: 12px;
            display: block;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 80px;
            height: 40px;
            margin-bottom: 10PX;
            margin-top: 10PX;
        }

        .button:hover {
            background-color: #36448f;
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class="header">Add Student to Class</h1>
        <form action="add_student.php" method="post" enctype="multipart/form-data">
            <label for="classNumber" class="form-label">Class:</label>
            <select id="classNumber">
                <?php
                $classes = get_classes($conn, $teacher_id);
                foreach ($classes as $class) {
                    echo "<option value=\"" . $class['CLASS_NUMBER'] . "\">" . $class['TITLE'] . " (" . $class['SEMESTER'] . ")</option>";
                }
                ?>
            </select>

            <label for="studentNumber" class="form-label">Student:</label>
            <select id="studentNumber">
                <?php
                $students = get_all_students($conn);
                foreach ($students as $student) {
                    echo "<option value=\"" . $student['USER_NUMBER'] . "\">" . $student['REAL_NAME'] . "</option>";
                }
                ?>
            </select>

            <input type="submit" value="Add to Class" class="button">
        </form>
        <br>

        <div class="button-container">
        <button class="button" id = "back">Back</button>
    </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "t-schedule.php";
            });
        };
        </script>
</body>
</html>