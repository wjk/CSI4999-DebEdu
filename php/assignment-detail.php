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
$assignmentId = $_GET['id'] ?? null;

if (!$assignmentId) {
    die("Assignment ID not provided.");
}
// Fetch assignment details
$stmt = $conn->prepare("SELECT * FROM ASSIGNMENT WHERE ASSIGNMENT_NUMBER = ?");
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

// Fetch students associated with this assignment
$stmt = $conn->prepare("SELECT STUDENT_USER.USER_NUMBER, STUDENT_USER.REAL_NAME, ASSIGNMENT_FOR_CLASS.GRADE 
                        FROM ASSIGNMENT_FOR_CLASS 
                        JOIN STUDENT_USER ON ASSIGNMENT_FOR_CLASS.STUDENT_NUMBER = STUDENT_USER.USER_NUMBER
                        WHERE ASSIGNMENT_FOR_CLASS.ASSIGNMENT_NUMBER = ?");
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
        <h1 class="header">Assignment Details</h1>
        <form action="t-complete.php" method="post" enctype="multipart/form-data">
            <div class="form-label">Selected Assignment:</div>
            <input type="text" name="classTitle" value="<?= $assignment['TITLE'] ?>" readonly>
           <div class="form-label">Assignment Description:</div>
            <input type="text" name="classTitle" value="<?= $assignment['DESCRIPTION'] ?>" readonly>
<table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student) { ?>
                    <tr>
                        <td><?= $student['REAL_NAME'] ?></td>
                        <td><input type="text" name="studentGrades[<?= $student['USER_NUMBER'] ?>]"  value="<?=$student['GRADE']?>" required>
</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
            <input type="submit" value="Upload Grades" class="button">
        </form>
    </div>
</body>
</html>
