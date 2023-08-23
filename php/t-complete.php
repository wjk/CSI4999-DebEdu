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
if (isset($_POST['studentGrades'])) {
    $grades = $_POST['studentGrades'];

    foreach ($grades as $studentId => $grade) {
            // Insert assignment into the database
            $updateQuery = "UPDATE ASSIGNMENT_FOR_CLASS SET GRADE = ? WHERE STUDENT_NUMBER = ? AND ASSIGNMENT_NUMBER = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sii", $grade, $studentId, $assignmentId);

            if ($stmt->execute()) {
                echo "Assignment graded successfully!";
            } else {
                echo "Error grading assignment: " . $stmt->error;
            }

            $stmt->close();
        }
    }
 else {
    header("HTTP/1.1 403 Forbidden");
    echo("You need to be a teacher to do this.");
    exit;
}
?>
