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
// Get Semester information
function get_semesters($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT DISTINCT EDU_CLASS.SEMESTER 
        FROM EDU_CLASS 
        INNER JOIN STUDENT_IN_CLASS ON STUDENT_IN_CLASS.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        WHERE TEACHER_NUMBER = ? 
        ORDER BY SEMESTER DESC"
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
// Get name, email, Grade, title, and semester
function get_student_data($conn, $teacher_id) {
    $stmt = $conn->prepare(
        "SELECT EDU_CLASS.TITLE, EDU_CLASS.SEMESTER, STUDENT_USER.EMAIL, STUDENT_USER.REAL_NAME, STUDENT_IN_CLASS.GRADE 
        FROM EDU_CLASS
        INNER JOIN STUDENT_IN_CLASS ON STUDENT_IN_CLASS.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        INNER JOIN STUDENT_USER ON STUDENT_USER.USER_NUMBER = STUDENT_IN_CLASS.STUDENT_NUMBER
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
// Get all class titles for the semesters the teacher is teaching in
function get_classes($conn, $teacher_id) {
    $stmt = $conn->prepare( 
        "SELECT DISTINCT EDU_CLASS.TITLE, EDU_CLASS.SEMESTER
        FROM EDU_CLASS 
        INNER JOIN STUDENT_IN_CLASS ON STUDENT_IN_CLASS.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        WHERE EDU_CLASS.TEACHER_NUMBER = ?"
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
        <!-- Semester selection -->
    <select id="selection"onchange="applyFilters();"><option value="All" selected>All</option>
    <?php
        // Call Function for array of semester associated with teacher ID
        $semesters = get_semesters($conn, get_teacher_id($conn, $_SESSION["username"])); 

        foreach ($semesters as $semester) {
            echo "<option value='" . $semester["SEMESTER"] . "'>" . $semester["SEMESTER"] . "</option>";
        }
    ?>
</select>
<select id="classSelection"onchange="applyFilters();"style="display:none;"><option value="All" selected>All</option>
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
        // Get all classes associated with the teacher
        
        var preloadedClass = <?php echo json_encode(get_classes($conn, get_teacher_id($conn, $_SESSION["username"]))); ?>;
        function applyFilters() {
            var selectedSemester = document.getElementById('selection').value;
            var selectedClass = document.getElementById('classSelection').value;
            var classDropdown = document.getElementById('classSelection');

            var options = classDropdown.querySelectorAll('option');
            for (var i = 1; i < options.length; i++) {  // Starting from index 1 to skip the first option ("ALL")
                classDropdown.removeChild(options[i]);
            }





            var rows = document.querySelectorAll('table tbody tr');

            rows.forEach(function(row) {
                var semester = row.querySelector('td:last-child').innerText;
                var classes = row.querySelector('td:nth-child(4)').innerText;
                var semesterMatch = selectedSemester === 'All' || semester === selectedSemester;
                var classMatch = selectedClass === 'All' || classes === selectedClass;

                if (semesterMatch && classMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            // If a semester is selected, we will show display the class dropdown, and filter to show unly classes within the semester.
            if (selectedSemester != 'All') {
                var class_data = preloadedClass.filter(function(semester) {
                    return semester.SEMESTER === selectedSemester;
                });
                console.log(class_data);
                    class_data.forEach(function(row){
                        var option = document.createElement('option');
                        option.value = row.TITLE; 
                        option.textContent = row.TITLE;
                        if (row.TITLE === selectedClass) {
                            option.selected = true;
                        }
                        classDropdown.appendChild(option);
                    });
                    classDropdown.style.display = '';
                }
                 else {
                    classDropdown.style.display = 'none';
                }
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
