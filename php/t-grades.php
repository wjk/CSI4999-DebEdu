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
         WHERE EXISTS (
             SELECT 1 FROM ASSIGNMENT 
             WHERE ASSIGNMENT.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
         ) 
         AND EDU_CLASS.TEACHER_NUMBER = ?
         ORDER BY EDU_CLASS.SEMESTER DESC"
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
        "SELECT EDU_CLASS.CLASS_NUMBER, EDU_CLASS.TITLE as edu_title, 
        EDU_CLASS.SEMESTER, EDU_CLASS.DESCRIPTION as edu_desc, 
        ASSIGNMENT.STATUS, ASSIGNMENT.TITLE as ass_title, ASSIGNMENT.ASSIGNMENT_NUMBER as num
        FROM EDU_CLASS
        JOIN ASSIGNMENT ON ASSIGNMENT.CLASS_NUMBER = EDU_CLASS.CLASS_NUMBER
        WHERE EDU_CLASS.TEACHER_NUMBER = ?"
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
        <fieldset>
            <legend>Select a Status:</legend>

            <div>
                <input type="radio" id="All"  name="status" checked />
                <label for="All">All</label>
            </div>

            <div>
                <input type="radio" id="Complete"  name="status" />
                <label for="Completed">Completed</label>
            </div>

            <div>
                <input type="radio" id="Incomplete"  name="status" />
                <label for="Incomplete">Incomplete</label>
            </div>
        </fieldset>
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
                    <th>Semester</th>
                    <th>Class</th>
                    <th>Title</th>
                    <th>Assignment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $classes = get_class_data($conn, get_teacher_id($conn, $_SESSION["username"]));
            foreach ($classes as $class_info) { ?>
                <tr data-id="<?php echo $class_info['num']; ?>">
                    <td><?= $class_info["SEMESTER"] ?></td>
                    <td><?= $class_info["edu_title"] ?></td>
                    <td><?= $class_info["edu_desc"] ?></td>      
                    <td><?= $class_info["ass_title"] ?></td>      
                    <td><?= ($class_info["STATUS"] == 1) ? "Complete" : "Incomplete" ?></td>                    

                </tr>
            <?php } ?>
        </tbody>
        </table>
        <div class="button-container">
        <button class="button" id = "back">Back</button>
    </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
        };
        var preloadedClass = <?php echo json_encode(get_classes($conn, get_teacher_id($conn, $_SESSION["username"]))); ?>;
        function applyFilters() {
            var selectedSemester = document.getElementById('selection').value;
            var selectedClass = document.getElementById('classSelection').value;
            var classDropdown = document.getElementById('classSelection');
            var selectedStatus = document.querySelector('input[type="radio"]:checked').id;

            var options = classDropdown.querySelectorAll('option');
            for (var i = 1; i < options.length; i++) {  // Starting from index 1 to skip the first option ("ALL")
                classDropdown.removeChild(options[i]);
            }





            var rows = document.querySelectorAll('table tbody tr');

            rows.forEach(function(row) {
                var semester = row.querySelector('td:nth-child(1)').innerText;
                var classes = row.querySelector('td:nth-child(2)').innerText;
                var status = row.querySelector('td:last-child').innerText;

                var semesterMatch = selectedSemester === 'All' || semester === selectedSemester;
                var classMatch = selectedClass === 'All' || classes === selectedClass;
                var statusMatch = selectedStatus === 'All' || status === selectedStatus;

                if (semesterMatch && classMatch && statusMatch) {
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
