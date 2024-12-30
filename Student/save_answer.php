<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $question_id = $_POST['question_id'];
    $option_id = $_POST['option_id'];

    // Kiểm tra nếu đã có đáp án, thì cập nhật
    $stmt = $conn->prepare("
        INSERT INTO answers (user_id, question_id, option_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE option_id = VALUES(option_id)
    ");
    $stmt->bind_param("iii", $user_id, $question_id, $option_id);
    $stmt->execute();
}
