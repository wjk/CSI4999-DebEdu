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
function getNextClassNumber($conn) {
    $query = "SELECT MAX(CLASS_NUMBER) AS max_class_number FROM EDU_CLASS";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextClassNumber = $row['max_class_number'] + 1;
        return $nextClassNumber;
    } else {
        return 1; // Default if no assignments exist yet
    }
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
            font-size: 14px;
            background-color: #5c6bc0;
            color: white;
            border-radius: 10px;
            border: none;
            width: 100%;
            height: 40px;
        }

        .button:hover {
            background-color: #36448f;
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class="header">Add Class</h1>
        <form action="create_class.php" method="post" enctype="multipart/form-data">
            <div class="form-label">New class semester:</div>
            <select name="semester">
            <option value="Fall 2023">Fall 2023</option>
            <option value="Spring 2023">Spring 2023</option>
            <option value="Summer 2023">Summer 2023</option>
            <option value="Fall 2022">Fall 2022</option>
            <option value="Spring 2022">Spring 2022</option>
    </select>
            <div class="form-label">Class Number:</div>
            <input type="text" name="classNumber" value="<?php echo getNextClassNumber($conn); ?>" readonly>
            <div class="form-label">Teacher Number:</div>
            <input type="text" name="teacherID" value="<?php echo get_teacher_id($conn, $_SESSION["username"]); ?>" readonly>
            <div class="form-label">Class title (ie. csi-4999):</div>
            <input type="text" name="classTitle" required>
            <div class="form-label">Class Description (ie. Senior Capstone Project):</div>
            <input type="text" name="classDesc" required>

            <input type="submit" value="Upload Assignment" class="button">
        </form>
    </div>
</body>
</html>