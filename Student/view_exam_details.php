<?php
session_start();
require '../db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin kỳ thi
$exam_id = $_GET['exam_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$exam_id) {
    echo "Không tìm thấy mã kỳ thi.";
    exit;
}

// Lấy thông tin kỳ thi
$stmt = $conn->prepare("SELECT exam_name FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Kỳ thi không tồn tại.";
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        q.question_id,
        q.question_text,
        q.question_type,
        qo.option_id AS correct_option_id,
        qo.option_text AS correct_option_text,
        qo.is_correct,
        a.option_id AS user_option_id,
        a.answer_text AS user_answer_text,
        qo2.option_text AS user_option_text
    FROM questions q
    LEFT JOIN question_options qo ON q.question_id = qo.question_id AND qo.is_correct = 1
    LEFT JOIN answers a ON q.question_id = a.question_id AND a.user_id = ? AND a.exam_id = ?
    LEFT JOIN question_options qo2 ON a.option_id = qo2.option_id
    WHERE q.exam_id = ?
");
$stmt->bind_param("iii", $user_id, $exam_id, $exam_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Hiển thị kết quả
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết kỳ thi</title>
    <link rel="stylesheet" href="../Admin/css/results.css">
</head>
<body>
    <div class="results-container">
        <h1>Kết quả kỳ thi: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>
        <h2>Chi tiết từng câu hỏi:</h2>
        <?php foreach ($questions as $question): ?>
            <div class="question-feedback">
                <p><strong>Câu hỏi:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                <p><strong>Đáp án đúng:</strong> 
                    <?php echo htmlspecialchars($question['correct_option_text']); ?>
                </p>
                <p><strong>Đáp án của bạn:</strong> 
                    <?php 
                    if ($question['question_type'] === 'multiple_choice') {
                        echo $question['user_option_text'] 
                            ? htmlspecialchars($question['user_option_text']) 
                            : 'Không trả lời';
                    } else {
                        echo $question['user_answer_text'] 
                            ? htmlspecialchars($question['user_answer_text']) 
                            : 'Không trả lời';
                    }
                    ?>
                </p>
                <p><strong>Kết quả:</strong> 
                    <?php 
                    if ($question['question_type'] === 'multiple_choice') {
                        echo ($question['user_option_id'] == $question['correct_option_id']) ? 'Đúng' : 'Sai';
                    } else {
                        echo (trim($question['user_answer_text']) === trim($question['correct_option_text'])) ? 'Đúng' : 'Sai';
                    }
                    ?>
                </p>
                <hr>
            </div>
        <?php endforeach; ?>

        <div class="back-button">
            <a href="view_exams.php" class="btn-back">Trở về danh sách kỳ thi</a>
        </div>
    </div>
</body>
</html>
