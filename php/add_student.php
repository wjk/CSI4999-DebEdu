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

function is_in_class($conn, $student_id, $class_id) {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS COUNT FROM STUDENT_IN_CLASS WHERE STUDENT_NUMBER = ? AND CLASS_NUMBER = ?;"
    );
    $stmt->bind_param("ii", $student_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["COUNT"] > 0;
}

// Check if the user is a teacher
if (isset($_SESSION["role"]) && $_SESSION["role"] === 'teacher') {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $classNumber = $_POST['classNumber'];
        $student = $_POST["studentNumber"];

        if ($classNumber == null || $student == null) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Class number and/or student number is null";
            exit;
        }

        if (is_in_class($conn, $student, $classNumber)) {
            echo "That student is already in that class.";
        } else {
            // Insert student into the database
            $insertQuery = "INSERT INTO STUDENT_IN_CLASS (STUDENT_NUMBER, CLASS_NUMBER, GRADE) VALUES (?, ?, 100)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii",  $student, $classNumber);

            if ($stmt->execute()) {
                echo "Student added successfully!";
            } else {
                echo "Error adding student: " . $stmt->error;
            }

            $stmt->close();
        }
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    echo("You need to be a teacher to do this.");
    exit;
}
?>
