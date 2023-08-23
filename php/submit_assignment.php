<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

include('common/mysql-connect.php');
$conn = connect_to_database();

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

if (isset($_POST['assignment_id'])) {
    $student_id = get_student_id($conn, $_SESSION["username"]);
    $assignment_id = $_POST['assignment_id'];

    // Handle file upload
    $target_dir = "uploads/"; // Specify the directory to store uploaded files
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $fp = fopen($target_file, 'r');
        $content = '';
        if (filesize($target_file) > 0) {
            $content = fread($fp, filesize($target_file));
        }
        fclose($fp);

        // Insert submission record into the database
        $insertQuery = "UPDATE ASSIGNMENT_FOR_CLASS SET SUBMISSION = ? WHERE STUDENT_NUMBER = ? AND ASSIGNMENT_NUMBER = ?;";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("bii", $content, $student_id, $assignment_id);

        if ($stmt->execute()) {
            echo "Assignment submitted successfully!";
        } else {
            echo "Error submitting assignment.";
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo "assignment_id parameter not set";
    print_r($_POST);
    exit;
}

?>
