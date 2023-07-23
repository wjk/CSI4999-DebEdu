<?php

function connect_to_database() {
    $servername = "localhost";
    $username = "DebEdu";
    $password = "DebEduService1";
    $dbname = "DebEdu";

    // Create the connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->cnnect_error);
    }

    return $conn;
}
?>
