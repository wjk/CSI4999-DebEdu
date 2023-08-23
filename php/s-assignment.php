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
function get_student_id($conn, $user_name) {
    $stmt = $conn->prepare(
        "SELECT USER_NUMBER " .
        "FROM STUDENT_USER " .
        "WHERE USER_NAME = ?;"
    );
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["USER_NUMBER"];
}

function get_assignment_data($conn, $student_id) {
    $stmt = $conn->prepare(
        "SELECT EDU_CLASS.TITLE, ASSIGNMENT.ASSIGNMENT_NUMBER, ASSIGNMENT.CLASS_NUMBER, ASSIGNMENT_FOR_CLASS.GRADE, " .
        "CASE WHEN ASSIGNMENT_FOR_CLASS.SUBMISSION IS NULL THEN 'N' ELSE 'Y' END AS 'Submission' " .
        "FROM ASSIGNMENT_FOR_CLASS " .
        "INNER JOIN ASSIGNMENT ON ASSIGNMENT_FOR_CLASS.ASSIGNMENT_NUMBER = ASSIGNMENT.ASSIGNMENT_NUMBER " .
        "INNER JOIN EDU_CLASS ON ASSIGNMENT.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER " .
        "WHERE ASSIGNMENT_FOR_CLASS.STUDENT_NUMBER = ? " .
        "ORDER BY ASSIGNMENT.CLASS_NUMBER, ASSIGNMENT.ASSIGNMENT_NUMBER;"
    );
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    return $assignments;
}
function get_assignment_number($conn, $student_id) {
    $stmt = $conn->prepare(
        "SELECT MAX(ASSIGNMENT_NUMBER) as max_num " .
        "FROM ASSIGNMENT_FOR_CLASS " .
        "WHERE STUDENT_NUMBER = ?;"
    );
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["max_num"];
}

$seen_open_assignment = false;

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


    </style>
</head>
<body>
<div class="choice-container">
    <h1 class="header">Student Portal</h1>
    <h3 class="sub-header">Assignment Submission</h3>

    <!-- Displaying class titles, descriptions, and grades -->
    <table>
    <thead>
        <tr>
            <th>Class Title</th>
            <th>Number</th>
            <th>Submitted</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $assignments_data = get_assignment_data($conn, get_student_id($conn, $_SESSION["username"]));
        foreach ($assignments_data as $assignment) { ?>
            <tr>
                <td><?= $assignment["TITLE"] ?></td>
                <td><?= $assignment["ASSIGNMENT_NUMBER"] ?></td>
                <td><?= $assignment["Submission"] ?></td>
            </tr>
        <?php
            if ($assignment["Submission"] == 'N') { $seen_open_assignment = true; }
        }
        ?>
    </tbody>
</table>
<?php if ($seen_open_assignment) { ?>
    <h3 class="sub-header">Submit Assignment</h3>
    <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
        <p>
            <label for="number">Assignment number:</label>
            <select name="assignment_id"> 
                <?php 
                    $max_number = get_assignment_number($conn, get_student_id($conn, $_SESSION["username"]));
                    for($i = 1; $i <= $max_number; $i++) {
                        echo "<option value=\"$i\">$i</option>";
                    }
                ?>
            </select>
        </p>

        <p>
            <input type="file" name="file" required>
        </p>

        <p>
            <input type="submit" value="Submit Assignment">
        </p>
    </form>
<?php } else { ?>
    <h3 class="sub-header">No assignments are open.</h3>
<?php } ?>
    <button class="button" id="back">Back</button>
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
