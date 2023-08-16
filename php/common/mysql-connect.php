<?php

function connect_to_database() {
    $servername = "localhost";
    $username = "DebEdu";
    $password = "DebEduService1";
    $dbname = "DebEdu";

    // Create the connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        header("HTTP/1.1 500 Internal Server Error");
        die("Failed to connect to database: " . $conn->connect_error);
        exit;
    }

    return $conn;
}
?>
