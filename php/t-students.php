<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

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
// Get Teacher ID
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

function get_semesters($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT DISTINCT SEMESTER FROM EDU_CLASS WHERE TEACHER_NUMBER = ? ORDER BY SEMESTER DESC"
    );
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $semesters = [];
    while ($row = $result->fetch_assoc()) {
        $semesters[] = $row;
    }
    return $semesters;
}
function get_student_data($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT EDU_CLASS.TITLE, EDU_CLASS.SEMESTER, STUDENT_USER.EMAIL, STUDENT_USER.REAL_NAME, STUDENT_IN_CLASS.GRADE 
        FROM EDU_CLASS
        -- Left join as some data may not be present ie. EMAIL
        LEFT JOIN ASSIGNMENT ON ASSIGNMENT.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        LEFT JOIN ASSIGNMENT_FOR_CLASS ON ASSIGNMENT_FOR_CLASS.ASSIGNMENT_NUMBER = ASSIGNMENT.ASSIGNMENT_NUMBER
        LEFT JOIN STUDENT_USER ON STUDENT_USER.USER_NUMBER = ASSIGNMENT_FOR_CLASS.STUDENT_NUMBER
        LEFT JOIN STUDENT_IN_CLASS ON STUDENT_IN_CLASS.STUDENT_NUMBER = STUDENT_USER.USER_NUMBER AND STUDENT_IN_CLASS.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        WHERE EDU_CLASS.TEACHER_NUMBER = ?"
    );
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    return $assignments;
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
            width: 500px;
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
        select {
            font-size: 12px;
            padding: 10px;
            border: none;
            background-color: #f8f8f8;
            color: #444;
            margin: 10px 0;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class ="header">Teacher Portal</h1>
        <h3 class ="sub-header">Students View</h3>
    <select id="selection"onchange="filter();"><option value="All" selected>All</option>
    <?php
        // Call Function for array of semester associated with teacher ID
        $semesters = get_semesters($conn, get_teacher_id($conn, $_SESSION["username"])); 

        foreach ($semesters as $semester) {
            echo "<option value='" . $semester["SEMESTER"] . "'>" . $semester["SEMESTER"] . "</option>";
        }
    ?>
</select>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Grade</th>
                    <th>Class</th>
                    <th>Semester</th>
                </tr>
            </thead>
            <tbody>
        <?php 
        // Call Function for array of assignment data associated with teacher ID
        $student_data = get_student_data($conn, get_teacher_id($conn, $_SESSION["username"]));
        // Loop to create row for each association made earlier
        foreach ($student_data as $student) { ?>
            <tr>
                <td><?= $student["REAL_NAME"] ?></td>
                <td><?= $student["EMAIL"] ?></td>
                <td><?= $student["GRADE"] ?></td>
                <td><?= $student["TITLE"] ?></td>
                <td><?= $student["SEMESTER"] ?></td>
            </tr>
        <?php } ?>
    </tbody>
        </table>
        <div class="button-container">
        <button class="button" id = "back">Back</button>
    </div>
    </div>
    <script>
        function filter() {
            //Get selected semester
            var selectedSemester = document.getElementById('selection').value;
            //Get table data
            var rows = document.querySelectorAll('table tbody tr');
            //Loop through table data
            rows.forEach(function(row) {
                //Selects 'last-child' which is the semester column in each row
                var semester = row.querySelector('td:last-child');
                //Display all if all is selected or display only the rows that = the selected semester
                if (selectedSemester === 'All' || semester.innerText === selectedSemester) {
                    row.style.display = '';
                    //Otherwise we do not display the row
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
        };
    </script>
</body>
</html>
