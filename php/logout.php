<?php
session_start();

$_SESSION["username"] = null;
$_SESSION["role"] = null;

header("Location: /debedu/index.php");
?>
