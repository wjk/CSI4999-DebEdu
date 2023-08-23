<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

include('common/mysql-connect.php');
$conn = connect_to_database();

if (isset($_POST['submit'])) {
    $student_id = get_student_id($conn, $_SESSION["username"]);
    $assignment_id = $_POST['assignment_id'];

    // Handle file upload
    $target_dir = "uploads/"; // Specify the directory to store uploaded files
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // Insert submission record into the database
        $insertQuery = "INSERT INTO ASSIGNMENT_FOR_CLASS (STUDENT_NUMBER, ASSIGNMENT_NUMBER, SUBMISSION) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iis", $student_id, $assignment_id, $target_file);

        if ($stmt->execute()) {
            echo "Assignment submitted successfully!";
        } else {
            echo "Error submitting assignment.";
        }
    } else {
        echo "Error uploading file.";
    }
}

?>
