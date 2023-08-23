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
// Get Student ID
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
// Get Title, Semester, Assignment Number, Class Number, and Grade for student in class.
function get_assignment_data($conn, $student_id) {
    $stmt = $conn->prepare(
        "SELECT EDU_CLASS.TITLE, EDU_CLASS.SEMESTER, ASSIGNMENT.ASSIGNMENT_NUMBER, ASSIGNMENT.CLASS_NUMBER, ASSIGNMENT_FOR_CLASS.GRADE, " .
        "CASE WHEN ASSIGNMENT_FOR_CLASS.SUBMISSION IS NULL THEN 'No' ELSE 'Yes' END AS 'Submission' " .
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
//Get Semester Data connected to Student Number for sorting
function get_student_semesters($conn, $student_id) {
    $stmt = $conn->prepare(
        "SELECT DISTINCT EDU_CLASS.SEMESTER " .
        "FROM STUDENT_IN_CLASS " .
        "INNER JOIN EDU_CLASS ON STUDENT_IN_CLASS.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER " .
        "WHERE STUDENT_IN_CLASS.STUDENT_NUMBER = ?;"
    );
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $semesters = [];
    while ($row = $result->fetch_assoc()) {
        $semesters[] = $row['SEMESTER'];
    }
    return $semesters;
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
    <h3 class="sub-header">Grade View</h3>

    <!-- Displaying class titles, descriptions, grades, and semester -->
    <!-- Semester choice container -->
    <div>
        <label for="semesterSelect">Select Semester:</label>
        <!-- All selection -->
        <select id="semesterSelect" name="semester" onchange="filter();"><option value="All" selected>All</option>
            <?php 
            // Call Function for array of semester associated with student ID
            $student_semesters = get_student_semesters($conn, get_student_id($conn, $_SESSION["username"]));
            // Loop to create semester choice for each associacion made earlier
            foreach ($student_semesters as $semester) { ?>
                <option value="<?= $semester ?>"><?= $semester ?></option>
            <?php } ?>
        </select>
    </div>
    <table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Number</th>
            <th>Grade</th>
            <th>Submitted</th>
            <th>Semester</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // Call Function for array of assignment data associated with student ID
        $assignments_data = get_assignment_data($conn, get_student_id($conn, $_SESSION["username"]));
        // Loop to create row for each associacion made earlier
        foreach ($assignments_data as $assignment) { ?>
            <tr>
                <td><?= $assignment["TITLE"] ?></td>
                <td><?= $assignment["ASSIGNMENT_NUMBER"] ?></td>
                <td><?= $assignment["GRADE"] ?></td>
                <td><?= $assignment["Submission"] ?></td>
                <td><?= $assignment["SEMESTER"] ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

    <button class="button" id="back">Back</button>
</div>
    <script>
        function filter() {
            //Get selected semester
            var selectedSemester = document.getElementById('semesterSelect').value;
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
                window.location.href = "student.php";
            });
        };
    </script>
</body>
</html>
