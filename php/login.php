<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();


$show_login_error = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];
    $username = $_POST["username"]; 
    $password = $_POST["password"];

    $login_valid = 0;
    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT USER_PASSWORD FROM STUDENT_USER WHERE USER_NAME = ?;");
        $stmt->bind_param("s", $username);
        $results = $stmt->execute();

        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            header("Location: /debedu/student.php");
            exit;
        } else {
            header("HTTP/1.1 401 Unauthorized");
            $show_login_error = 1;
        }
    } else if ($role === 'teacher') {
        $stmt = $conn->prepare("SELECT USER_PASSWORD FROM TEACHER_USER WHERE USER_NAME = ?;");
        $stmt->bind_param("s", $username);
        $results = $stmt->execute();

        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            header("Location: /debedu/teacher.php");
            exit;
        } else {
            header("HTTP/1.1 401 Unauthorized");
            $show_login_error = 1;
        }
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

        .failure-box {
            width: 300px;
            padding: 16px;
            margin-bottom: 32px;
            background-color: salmon;
            border-color: red;
            border-radius: 8px;
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
    <?php if ($show_login_error) {?>
        <div class="failure-box">
            The username or password is not valid.
        </div>
    <?php } ?>

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
