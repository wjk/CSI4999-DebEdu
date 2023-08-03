<?php
session_start();

if (isset($_SESSION["username"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] === 'student') {
        header("Location: /debedu/student.php");
    } else if ($_SESSION["role"] === 'teacher') {
        header("Location: /debedu/teacher.php");
    } else {
        header("Location: /debedu/login.php");
    }
} else {
    header("Location: /debedu/login.php");
}

?>
