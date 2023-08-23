<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

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
// Check if the user is a teacher
if (isset($_SESSION["role"]) && $_SESSION["role"] === 'teacher') {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Process the assignment upload
        $classNumber = $_POST['classNumber'];
        $student = $_POST["studentNumber"];

            // Insert assignment into the database
            $insertQuery = "INSERT INTO STUDENT_IN_CLASS ( STUDENT_NUMBER, CLASS_NUMBER) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii",  $student, $classNumber);

            if ($stmt->execute()) {
                echo "Student uploaded successfully!";
            } else {
                echo "Error adding student: " . $stmt->error;
            }

            $stmt->close();
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    echo("You need to be a teacher to do this.");
    exit;
}
?>
