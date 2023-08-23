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

function getNextAssignmentNumber($conn) {
    $query = "SELECT MAX(ASSIGNMENT_NUMBER) AS max_assignment_number FROM ASSIGNMENT";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextAssignmentNumber = $row['max_assignment_number'] + 1;
        return $nextAssignmentNumber;
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
        <h1 class="header">Add Assignment</h1>
        <form action="upload_assignment.php" method="post" enctype="multipart/form-data">
            <div class="form-label">Select Class:</div>
            <select name="classNumber">
                <?php
                // Fetch classes taught by the teacher
                $teacherNumber = $_SESSION["USER_NUMBER"];
                $classQuery = "SELECT CLASS_NUMBER, TITLE FROM EDU_CLASS WHERE TEACHER_NUMBER = ?";
                $stmt = $conn->prepare($classQuery);
                $stmt->bind_param("i", $teacherNumber);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["CLASS_NUMBER"] . '">' . $row["TITLE"] . '</option>';
                }

                $stmt->close();
                ?>
            </select>
            <div class="form-label">Assignment Number:</div>
            <input type="text" name="assignmentNumber" value="<?php echo getNextAssignmentNumber($conn); ?>" readonly>

            <div class="form-label">Upload Assignment:</div>
            <input type="file" name="file" required>
            <input type="submit" value="Upload Assignment" class="button">
        </form>
    </div>
</body>
</html>