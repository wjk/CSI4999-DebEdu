<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

// Ensure the user is a student
if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] != 'student') {
        header("Location: /debedu/teacher.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["username"])) {
    header("Location: /debedu/login.php");
    exit;
}

$user_name = $_SESSION["username"];
$user_type = $_SESSION["role"];

if ($user_name == '' || $user_type == '') {
    header("Location: /debedu/login.php");
    exit;
}

$show_success = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_full_name = $_POST["fullname"];

    $stmt = $conn->prepare("UPDATE STUDENT_USER SET REAL_NAME = ? WHERE USER_NAME = ?;");
    $stmt->bind_param("ss", $new_full_name, $user_name);
    $stmt->execute();

    $show_success = 1;
}

function get_full_name($user_name, $conn) {
    $stmt = $conn->prepare("SELECT REAL_NAME FROM STUDENT_USER WHERE USER_NAME = ?;");
    $stmt->bind_param("s", $user_name);

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        die("No user with name " . $user_name);
    } else if ($result->num_rows > 1) {
        die("Duplicate users with name " . $user_name);
    }

    $values = $result->fetch_assoc();
    if (!isset($values["REAL_NAME"]))
        return '';

    return $values["REAL_NAME"];
}

$full_name = get_full_name($user_name, $conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Details</title>
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
            padding: 16px;
            margin-bottom: 32px;
            background-color: lightgreen;
            border-color: darkgreen;
            border-radius: 8px;
        }

        .failure-box {
            padding: 16px;
            margin-bottom: 32px;
            background-color: salmon;
            border-color: red;
            border-radius: 8px;
        }

        .main-container {
            width: 300px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }

        .main-container input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .main-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            color: white;
            background-color: #5c6bc0;
            cursor: pointer;
        }

        .main-container button:hover {
            background-color: #3f51b5;
        }

        .main-container button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 80px;
            height: 40px;
            margin-bottom: 10PX;
        }

        .main-container button:hover {
            background-color: #5c6bc0;
        }

        .main-container .button-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .main-container label {
            margin-right: 10px;
        }

        .main-container input[type="radio"] {
            width: 20px;
            margin: 10px 0;
        }

        .main-container h1 {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <?php if ($show_success) { ?>
            <div class="success-box">
                The changes have been applied successfully.
            </div>
        <?php } ?>

        <h1>Details for <?php echo($user_name) ?></h1>
            <?php if ($full_name != '') { ?>
                <p>
                    Your real name is <span class="bold"><?php echo($full_name) ?></span>.<br>
                </p>
            <?php } ?>
            <p>
                You are a <span class="bold">student</span>.
            </p>
        </p>
        <form id="user-form" method="POST" action="s-user-details.php">
            <label for="fullname">New Real Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <button type="submit">Submit</button>
        </form>

        <div class="button-container">
            <button id="back">Back</button>
        </div>
    </div>

    <script>
        (function() {
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "student.php";
           });
        };
        })();
    </script>
</body>
</html>
