<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";  
$dbname = "DebEdu";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];
    $username = $_POST["username"]; 

    // store username in session
    $_SESSION["username"] = $username;
    // redirect based on role
    if($role === "student"){
        header("Location: student.php");
    }
    if($role === "teacher"){
        header("Location: teacher.php");
    }
}
?>
<!DOCTYPE html> 
<html>
<head>
    <title>Portal Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f2f2;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 300px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); 
        }

        .login-container input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd; 
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            color: white;
            background-color: #5c6bc0; 
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #3f51b5;
        }
        .login-container label {
            margin-right: 10px;
        }

        .login-container input[type="radio"] {
            width: 20px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- login form -->
        <form id="login-form" method="POST" action="login.php">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <!-- radio buttons for teacher and student -->
            <input type="radio" id="teacher" name="role" value="teacher" required>
            <label for="teacher">Teacher</label>
            <input type="radio" id="student" name="role" value="student" required>
            <label for="student">Student</label>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
