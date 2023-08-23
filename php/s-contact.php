<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

if (!isset($_SESSION["username"])) {
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

function get_real_name($conn, $user_name) {
    $stmt = $conn->prepare(
        "SELECT STUDENT_USER.REAL_NAME AS REAL_NAME " .
        "FROM STUDENT_USER " .
        "WHERE STUDENT_USER.USER_NAME = ?;"
    );
    $stmt->bind_param("s", $user_name);

    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!isset($row["REAL_NAME"])) {
        die("REAL_NAME for user " . $user_name . " not returned from database");
    }
    return $row["REAL_NAME"];
}

function get_all_classes($conn, $user_name) {
    $stmt = $conn->prepare(
        "SELECT C.CLASS_NUMBER AS CLASS_NUMBER " .
        "FROM STUDENT_USER S " .
        "INNER JOIN STUDENT_IN_CLASS SC ON SC.STUDENT_NUMBER = S.USER_NUMBER " .
        "INNER JOIN EDU_CLASS C ON C.CLASS_NUMBER = SC.CLASS_NUMBER " .
        "WHERE S.USER_NAME = ?;"
    );
    $stmt->bind_param("s", $user_name);

    $stmt->execute();
    $result = $stmt->get_result();

    $retval = array();
    while ($row = $result->fetch_assoc()) {
        $retval[] = $row['CLASS_NUMBER'];
    }
    return $retval;
}

function get_class_details($conn, $class_number) {
    $stmt = $conn->prepare(
        "SELECT T.REAL_NAME AS TEACHER_NAME, E.TITLE AS TITLE, E.DESCRIPTION AS DESCRIPTION " .
        "FROM TEACHER_USER T " .
        "INNER JOIN EDU_CLASS E ON T.USER_NUMBER = E.TEACHER_NUMBER " .
        "WHERE E.CLASS_NUMBER = ?;"
    );
    $stmt->bind_param("i", $class_number);

    $stmt->execute();
    $result = $stmt->get_result();
    $assoc = $result->fetch_assoc();

    return array(
        "teacher_name" => $assoc["TEACHER_NAME"],
        "class_title" => $assoc["TITLE"],
        "class_description" => $assoc["DESCRIPTION"]
    );
}

function get_all_classmates($conn, $class_number) {
    $stmt = $conn->prepare(
        "SELECT S.REAL_NAME AS STUDENT_NAME " .
        "FROM STUDENT_USER S " .
        "INNER JOIN EDU_CLASS E ON E.CLASS_NUMBER = ? " .
        "INNER JOIN STUDENT_IN_CLASS SC ON SC.CLASS_NUMBER = E.CLASS_NUMBER " .
        "WHERE S.USER_NUMBER = SC.STUDENT_NUMBER;"
    );
    $stmt->bind_param("i", $class_number);

    $stmt->execute();
    $result = $stmt->get_result();

    $retval = array();
    while ($row = $result->fetch_assoc()) {
        $retval[] = $row['STUDENT_NAME'];
    }
    return $retval;
}

$user_name = $_SESSION["username"];
$real_name = get_real_name($conn, $user_name);

$classes = get_all_classes($conn, $user_name);
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
            overflow-y: auto; 
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
        th {
            background-color: #EEEEEE;
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


    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class="header">Student Portal</h1>
        <h3 class="sub-header">Contact View</h3>

        <?php
        foreach ($classes as $class_number) {
            $class_details = get_class_details($conn, $class_number);
        ?>

        <table>
            <thead>
                <tr>
                    <th>
                        <b><?php echo($class_details['class_title']) ?></b><br>
                        <?php echo($class_details['class_description']) ?>
                    </th>
                </tr>
                <tr>
                    <th>
                        Teacher: <?php echo($class_details['teacher_name']) ?>
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php foreach (get_all_classmates($conn, $class_number) as $classmate_name) { ?>
                    <tr>
                        <td><?php echo($classmate_name) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>

        <button class="button" id = "back">Back</button>

    </div>

    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "student.php";
            });
        };
    </script>
</body>
</html>
