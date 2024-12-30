<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id > 0) {
    // Xóa câu hỏi và đáp án liên quan
    $sql_delete_options = "DELETE FROM question_options WHERE question_id = $question_id";
    $conn->query($sql_delete_options);

    $sql_delete_question = "DELETE FROM questions WHERE question_id = $question_id";
    $conn->query($sql_delete_question);

    echo "<p style='color: green;'>Xóa câu hỏi thành công!</p>";
    header("Location: question_exam.php?exam_id=" . $_GET['exam_id']);
    exit();
} else {
    echo "<p style='color: red;'>ID câu hỏi không hợp lệ.</p>";
}
?>
