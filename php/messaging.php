<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();

if (!isset($_SESSION["username"]) || !isset($_SESSION["role"])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION["username"];
$role = $_SESSION["role"];

if ($role != "student" && $role != "teacher") {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 401 Unauthorized");
    echo("You cannot view this page directly. To access the messaging feature, use the links on the student or teacher portal");
    exit;
}

$action = 'read';
if (isset($_POST["action"])) {
    $action = $_POST["action"];
}

function get_user_number($conn, $user_name, $role) {
    $sql = '';
    if ($role == 'teacher') {
        $sql = 'SELECT USER_NUMBER FROM TEACHER_USER WHERE USER_NAME = ?;';
    } else if ($role == 'student') {
        $sql = 'SELECT USER_NUMBER FROM STUDENT_USER WHERE USER_NAME = ?;';
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo("Role '" . $role . "' not teacher or student");
        exit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return intval($row["USER_NUMBER"]);
}

function get_class_title($conn, $class_number) {
    $stmt = $conn->prepare("SELECT TITLE FROM EDU_CLASS WHERE CLASS_NUMBER = ?;");
    $stmt->bind_param("i", $class_number);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["TITLE"];
}

$show_post_too_long_error = false;
if ($action == 'delete') {
    if ($role != 'teacher') {
        header("HTTP/1.1 401 Unauthorized");
        echo("Only a teacher can do that.");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM MESSAGE WHERE MESSAGE_NUMBER = ?;");
    $stmt->bind_params("i", get_user_number($conn, $user_name));
    $stmt->execute();

    # Now continue rendering the page.
} else if ($action == 'post') {
    $role_column = '';
    if ($role == 'teacher') $role_column = 'TEACHER_USER_NUMBER';
    else if ($role == 'student') $role_column == 'STUDENT_USER_NUMBER';
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo("Role '" . $role . "' not teacher or student");
        exit;
    }
    if ($role_column == '') {
        header("HTTP/1.1 500 Internal Server Error");
        echo("Role '" . $role . "' not recognized");
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO MESSAGE (MESSAGE_TEXT, TIMESTAMP, CLASS_NUMBER, " . $role_column . ") VALUES (?, NOW(), ?, ?)"
    );
    $stmt->bind_param("ssi", $_POST["post_text"], $_POST["CLASS_NUMBER"], get_user_number($conn, $user_name));
    $stmt->execute();

    # Now continue rendering the page.
}

function get_messages($conn, $class_number, $is_teacher) {
    if (!isset($_POST["class_number"])) {
        header("HTTP/1.1 500 Internal Server Error");
        echo("No class number is specified.");
        exit;
    }

    $class_number = $_POST["class_number"];

    # Due to limitations of SQL syntax, I can only query for teacher messages or
    # student messages, not both at the same time. (When I try, the results are empty.)
    $sql = '';
    if ($is_teacher) {
        $sql =
            "SELECT MESSAGE.MESSAGE_NUMBER, MESSAGE.MESSAGE_TEXT, MESSAGE.TIMESTAMP, TEACHER_USER.USER_NAME " .
            "FROM MESSAGE " .
            "INNER JOIN EDU_CLASS ON EDU_CLASS.CLASS_NUMBER = MESSAGE.CLASS_NUMBER " .
            "INNER JOIN TEACHER_USER ON TEACHER_USER.USER_NUMBER = MESSAGE.TEACHER_USER_NUMBER " .
            "WHERE EDU_CLASS.CLASS_NUMBER = ?;";
    } else {
        $sql =
            "SELECT MESSAGE.MESSAGE_NUMBER, MESSAGE.MESSAGE_TEXT, MESSAGE.TIMESTAMP, STUDENT_USER.USER_NAME " .
            "FROM MESSAGE " .
            "INNER JOIN EDU_CLASS ON EDU_CLASS.CLASS_NUMBER = MESSAGE.CLASS_NUMBER " .
            "INNER JOIN STUDENT_USER ON STUDENT_USER.USER_NUMBER = MESSAGE.STUDENT_USER_NUMBER " .
            "WHERE EDU_CLASS.CLASS_NUMBER = ?;";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $class_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $fields = [];
        $fields["msgid"] = $row["MESSAGE_NUMBER"];
        $fields["timestamp"] = $row["MESSAGE_TIMESTAMP"];
        $fields["text"] = $row["MESSAGE_TEXT"];
        $fields["poster"] = $row["USER_NAME"];
        $messages[] = $fields;
    };

    return $messages;
}

function safe_text_to_integer($string) {
    if ($string == "" || $string == null) {
        header("HTTP/1.1 500 Internal Server Error");
        echo("String '" . $string . "' is not a valid integer");
        exit;
    }

    return intval($string);
}

function string_to_posix_time($string) {
    $timestamp_parts = preg_match("^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$", $string);

    $year = safe_text_to_integer($timestamp_parts[1]);
    $month = safe_text_to_integer($timestamp_parts[2]);
    $day = safe_text_to_integer($timestamp_parts[3]);
    $hour = safe_text_to_integer($timestamp_parts[4]);
    $minute = safe_text_to_integer($timestamp_parts[5]);
    $second = safe_text_to_integer($timestamp_parts[6]);

    $date_object = new DateTime();
    $date_object->setDate($year, $month, $day);
    $date_object->setTime($hour, $minute, $second, 0); # last parameter is nanoseconds
    return $date_object->getTimestamp();
}

function month_number_to_month_name($num) {
    if ($num == 1) return 'January';
    else if ($num == 2) return 'February';
    else if ($num == 3) return 'March';
    else if ($num == 4) return 'April';
    else if ($num == 5) return 'May';
    else if ($num == 6) return 'June';
    else if ($num == 7) return 'July';
    else if ($num == 8) return 'August';
    else if ($num == 9) return 'September';
    else if ($num == 10) return 'October';
    else if ($num == 11) return 'November';
    else if ($num == 12) return 'December';

    header("HTTP/1.1 500 Internal Server Error");
    echo("Month number " . $num . " not valid (expected 1-12)");
    exit;
}

function compare_message_timestamps($left, $right) {
    $left_time = $left['timestamp'];
    $right_time = $left['timestamp'];
    
    if ($left_time == $right_time) return 0;
    else if ($left_time < $right_time) return -1;
    else return 1;
}

function sort_messages($messages) {
    $result = [];

    foreach ($messages as $input_msg) {
        $output_msg = [];
        $output_msg["msgid"] = $input_msg["msgid"];
        $output_msg["text"] = $input_msg["text"];
        $output_msg["poster"] = $input_msg["poster"];
        $output_msg["timestamp"] = string_to_posix_time($input_msg["timestamp"]);

        $now = new DateTimeImmutable(); # defaults to current time
        $post_time = new DateTimeImmutable($output_msg["timestamp"]);

        $user_date = '';
        list($now_year, $now_month, $now_day) = sscanf($now->format("%Y-%m-%d"), "%d-%d-%d");
        list($post_year, $post_month, $post_day) = sscanf($post_time->format("%Y-%m-%d"), "%d-%d-%d");
        if ($now_year == $post_year && $now_month == $post_month && $now_day = $post_day) {
            $user_date = 'today';
        } else if ($now_year == $post_year && $now_month == $post_month && $post_day == ($now_day - 1)) {
            $user_date = 'yesterday';
        } else if ($now_year == $post_year) {
            $user_date = month_number_to_month_name($post_month) . ' ' . $post_day;
        } else {
            $user_date = month_number_to_month_name($post_month) . ' ' . $post_day . ', ' . $post_year;
        }

        $user_time = $post_time->format("%g:%m %A");
        $output_msg["date_string"] = $user_date .  ' at ' . $user_time;

        $result[] = $output_msg;
    }

    usort($result, 'compare_message_timestamps');
    return $result;
}

if (!isset($_POST["class_number"])) {
    header("HTTP/1.1 500 Internal Server Error");
    echo("class number not found");
    exit;
}

$class_number = $_POST["class_number"];
$messages = array_merge(get_messages($conn, $class_number, true), get_messages($conn, $class_number, false));
$sorted_messages = sort_messages($messages);
$class_title = get_class_title($conn, $class_number);

?>
<!DOCTYPE html> 
<html>
<head>
    <title>Messaging</title>
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

        .choice-container {
            width: 300px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .header {
            text-align: center;
            margin-bottom: -10px;
            color: #5c6bc0;
            font-size: 24px;
            font-weight: bold;
        }
        .sub-header {
            text-align: center;
            margin-bottom: 5px;
            color: #5c6bc0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 80px;
            height: 40px;
            margin-bottom: 10PX;
        }
        .button:hover {
            background-color: #5c6bc0;
        }
        .timestamp {
            color: gray;
            font-size: 9;
        }
        .whole-width {
            width: 100%;
        }
        .bold {
            font-weight: bold;
        }
        textarea {
            resize: vertical;
        }

    </style>
</head>
<body>
<div class="choice-container">
    <h1 class="header">Messaging</h1>
    <h3 class="sub-header">For class “<?= $class_title ?>”</h3>

    <table>
        <tbody>
            <?php
            foreach ($sorted_messages as $msg) { ?>
                <tr class="whole-width">
                    <td>
                        <p>
                            <span class="bold"><?= $msg["poster"] ?></span>
                            <span class="timestamp">posted <?= $msg["date_string"] ?></span>
                        </p>
                        <p>
                            <?= $msg["text"] ?>
                        </p>
                    </td>

                    <?php if ($role == 'teacher') { ?>
                        <td>
                            <form action="POST" target="messaging.php">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="msgid" value="<?= $msg["msgid"] ?>">
                                <input type="hidden" name="class_number" value="<?= $class_number ?>">
                                <button type="submit">Delete Post</button>
                            </form>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <form method="POST" action="messaging.php">
        <input type="hidden" name="action" value="post">
        <input type="hidden" name="class_number" value="<?= $class_number ?>">
        <p>
            <textarea class="whole-width" name="post_text"></textarea>
        </p>
        <p>
            <button class="button" type="submit">Post</button>
        </p>
    </form>

    <button class="button" id="back">Back</button>
</div>

    <script>
        window.onload = function() {
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "student.php";
            });
        };
    </script>
</body>
</html>
