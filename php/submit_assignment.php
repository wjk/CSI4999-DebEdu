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
    $filename = $_FILES["file"]["tmp_name"];

    if (empty($filename)) {
        header("HTTP/1.1 500 Internal Server Error");
        echo "No file selected for upload";
        exit;
    }

    $content = file_get_contents($filename);
    // Insert submission record into the database
    $insertQuery = "UPDATE ASSIGNMENT_FOR_CLASS SET SUBMISSION = ? WHERE ASSIGNMENT_NUMBER = ? AND STUDENT_NUMBER = ?;";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("bii", $content, $student_id, $assignment_id);

    if ($stmt->execute()) {
        echo "Assignment submitted successfully!";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Error submitting assignment.";
    }
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error uploading file.";
    exit;
}

?>
