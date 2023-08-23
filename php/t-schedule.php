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
// Get Semester information
function get_semesters($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT DISTINCT EDU_CLASS.SEMESTER 
         FROM EDU_CLASS 
         WHERE TEACHER_NUMBER = ?"
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
// Get all class titles for the semesters the teacher is teaching in
function get_classes($conn, $teacher_id) {
    $stmt = $conn->prepare( 
        "SELECT TITLE , SEMESTER
        FROM EDU_CLASS 
        WHERE TEACHER_NUMBER = ?"
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
function get_class_data($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT EDU_CLASS.CLASS_NUMBER, EDU_CLASS.TITLE, 
        EDU_CLASS.SEMESTER, EDU_CLASS.DESCRIPTION 
        FROM EDU_CLASS
        WHERE TEACHER_NUMBER = ?"
    );
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    return $grades;
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
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class ="header">Teacher Portal</h1>
        <h3 class ="sub-header">Grade View</h3>
        <select id="selection"onchange="applyFilters();"><option value="All" selected>All</option>
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
                    <th>Semester</th>
                    <th>Title</th>
                    <th>Description</th>

                </tr>
            </thead>
            <tbody>
            <?php 
            $classes = get_class_data($conn, get_teacher_id($conn, $_SESSION["username"]));
            foreach ($classes as $class_info) { ?>
                <tr>
                    <td><?= $class_info["SEMESTER"] ?></td>
                    <td><?= $class_info["TITLE"] ?></td>
                    <td><?= $class_info["DESCRIPTION"] ?></td>                      

                </tr>
            <?php } ?>
        </tbody>
        </table>
        <div class="button-container">
        <button class="button" id = "back">Back</button>
        <button class="button" id = "create">Create Class</button>
        <button class="button" id = "student">Add Students</button>

    </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
                document.getElementById('create').addEventListener('click', function(event) {
                window.location.href = "t-create.php";
                
            });
            document.getElementById('student').addEventListener('click', function(event) {
                window.location.href = "t-add.php";
                
            });
        };
        var preloadedClass = <?php echo json_encode(get_classes($conn, get_teacher_id($conn, $_SESSION["username"]))); ?>;
        function applyFilters() {
            var selectedSemester = document.getElementById('selection').value;
            var Dropdown = document.getElementById('selection');


            var rows = document.querySelectorAll('table tbody tr');

            rows.forEach(function(row) {
                var semester = row.querySelector('td:nth-child(1)').innerText;

                var semesterMatch = selectedSemester === 'All' || semester === selectedSemester;

                if (semesterMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }); 
           
        }
        const radioButtons = document.querySelectorAll('input[type="radio"]');
        function handleRadioChange(event) {
            applyFilters();
    }
    radioButtons.forEach(radioButton => {
        radioButton.addEventListener('change', handleRadioChange);
    });
    </script>
</body>
</html>
