<?php
session_start();

if (array_key_exists("username", $_SESSION) && array_key_exists("role", $_SESSION)) {
    if ($_SESSION["role"] === 'student') {
        header("Location: /debedu/student.php");
    } else if ($_SESSION["role"] === 'teacher') {
        header("Location: /debedu/teacher.php");
    }
} else {
    header("Location: /debedu/login.php");
}

?>
