<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy `exam_id` từ URL
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
if ($exam_id <= 0) {
    die("Exam ID không hợp lệ. Vui lòng kiểm tra URL.");
}


// Lấy thông tin kỳ thi
$sql_exam = "SELECT * FROM exams WHERE exam_id = $exam_id";
$result_exam = $conn->query($sql_exam);
if ($result_exam->num_rows == 0) {
    die("Không tìm thấy kỳ thi với ID: $exam_id");
}
$exam = $result_exam->fetch_assoc();

// Lấy danh sách câu hỏi thuộc kỳ thi này
$sql_questions = "SELECT * FROM questions WHERE exam_id = $exam_id";
$result_questions = $conn->query($sql_questions);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem Đề Thi</title>
    <link rel="stylesheet" href="../CSS/view_exam.css"> <!-- Nếu có file CSS riêng -->

</head>
<body>
    <div class="container">
        <h1>Đề Thi: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>
        <h2>Môn học: <?php echo htmlspecialchars($exam['subject']); ?></h2>
        <p>Thời gian bắt đầu: <?php echo htmlspecialchars($exam['start_time']); ?></p>
        <p>Thời lượng: <?php echo htmlspecialchars($exam['duration']); ?> phút</p>

        <h2>Bài Kiểm Tra</h2>
        <?php if ($result_questions->num_rows > 0): ?>
            <?php while ($question = $result_questions->fetch_assoc()): ?>
                <div class="question">
                    <p><strong>Câu hỏi:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                    <p><strong>Loại câu hỏi:</strong> <?php echo ucfirst($question['question_type']); ?></p>

                    <!-- Lấy đáp án của câu hỏi -->
                    <?php
                    $question_id = $question['question_id'];
                    $sql_options = "SELECT * FROM question_options WHERE question_id = $question_id";
                    $result_options = $conn->query($sql_options);
                    ?>
                    <?php if ($result_options->num_rows > 0): ?>
                        <div class="answers">
                            <strong>Đáp án:</strong>
                            <ul>
                                <?php while ($option = $result_options->fetch_assoc()): ?>
                                    <li>
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                        <?php if ($option['is_correct']): ?>
                                            <strong>(Đúng)</strong>
                                        <?php endif; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p><em>Không có đáp án nào được cung cấp.</em></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không có câu hỏi nào trong kỳ thi này.</p>
        <?php endif; ?>

        <button onclick="window.location.href='manage_exam.php';">Quay lại</button>
    </div>
</body>
</html>
