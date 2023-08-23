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
            width: 400px; /* Increased container width for better alignment */
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); 
            text-align: center; /* Center align all contents */
        }
        .header {
            color: #5c6bc0;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px; /* Added margin for separation */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px; /* Reduced margin for spacing */
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .form-label {
            text-align: left;
            color: #5c6bc0;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .input-field {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 100px; /* Adjusted button width */
            height: 40px;
            margin-top: 10px; /* Moved margin to top for spacing */
        }
        .button:hover {
            background-color: #5c6bc0;
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
                include('common/mysql-connect.php');
                $conn = connect_to_database();

                // Fetch classes taught by the teacher
                $teacherNumber = $_SESSION["userNumber"];
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
            

            <div class="form-label">Upload Assignment:</div>
            <input type="file" name="file" required>

            <input type="submit" value="Upload Assignment" class="button">
        </form>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
        };
    </script>
</body>
</html>