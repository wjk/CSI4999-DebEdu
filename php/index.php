<?php
session_start();

if (strlen($_SESSION["username"]) > 0) {
    if ($_SESSION["role"] === 'student') {
        header("Location: /debedu/student.php");
    } else if ($_SESSION["role"] === 'teacher') {
        header("Location: /debedu/teacher.php");
    }
} else {
    header("Location: /debedu/login.php");
}

?>
