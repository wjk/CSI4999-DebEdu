<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

// Check if the user is a teacher
if (isset($_SESSION["role"]) && $_SESSION["role"] === 'teacher') {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Process the assignment upload
        $assignmentNumber = $_POST['assignmentNumber'];
        $classNumber = $_POST["classNumber"];
        $datePosted = date("Y-m-d H:i:s");
        $file = $_FILES["file"]["tmp_name"];
        $title = $_POST["assignmentTitle"];
        $desc = $_POST["assignmentDesc"];

        if (!empty($file)) {
            // Read the file contents
            $fileContent = file_get_contents($file);

            // Insert assignment into the database
            $insertQuery = "INSERT INTO ASSIGNMENT (ASSIGNMENT_NUMBER, TITLE, DESCRIPTION, CLASS_NUMBER, DATE_POSTED, DOWNLOADABLE) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("issssb", $assignmentNumber, $title, $desc, $classNumber, $datePosted, $fileContent);

            if ($stmt->execute()) {
                echo "Assignment uploaded successfully!";
            } else {
                echo "Error uploading assignment: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "No file selected.";
        }
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    echo("You need to be a teacher to do this.");
    exit;
}
?>
