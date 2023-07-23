<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();


$CORRECT_ADMIN_CODE = '61472147';


$show_code_failure = 0;
$show_success = 0;
$username = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $code = $_POST["admincode"];

    if ($code == $CORRECT_ADMIN_CODE) {
        $show_code_failure = 1;
    } else {
        $password_hash = password_hash($password, PASSWORD_ARGON2I);

        if ($role === 'student') {
            $stmt = $conn->prepare("INSERT INTO STUDENT_USER(USER_NAME, USER_PASSWORD) VALUES (?, ?);");
            $stmt->bind_param("ss", $username, $password_hash);
            $stmt->execute();
        } else if (role === 'teacher') {
            $stmt = $conn->prepare("INSERT INTO TEACHER_USER(USER_NAME, USER_PASSWORD) VALUES (?, ?);");
            $stmt->bind_param("ss", $username, $password_hash);
            $stmt->execute();
        } else {
            die("Unknown role: " . $role);
        }

        $show_success = 1;
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

        .success-box {
            width: 300px;
            padding: 16px;
            margin-bottom: 32px;
            background-color: lightgreen;
            border-color: darkgreen;
            border-radius: 8px;
        }

        .failure-box {
            width: 300px;
            padding: 16px;
            margin-bottom: 32px;
            background-color: lightgreen;
            border-color: darkgreen;
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
        
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php if ($show_code_failure) {?>
        <div class="failure-box">
            The secure administration code is incorrect.
        </div>
    <?php } else if ($show_success) {?>
        <div class="success-box">
            User <span class="bold"><?php $username ?></span> has been successfully created.
        </div>
    <?php } ?>

    <div class="login-container">
        <!-- login form -->
        <form id="login-form" method="POST" action="new-user.php">
            <label for="username">New User Name:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="admincode">Secure Code:</label>
            <input type="password" id="admincode" name="admincode" required>

            <!-- radio buttons for teacher and student -->
            <input type="radio" id="teacher" name="role" value="teacher" required>
            <label for="teacher">Teacher</label>
            <input type="radio" id="student" name="role" value="student" required>
            <label for="student">Student</label>

            <button type="submit">Create User</button>
        </form>
    </div>
</body>
</html>
