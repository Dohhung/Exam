<?php
session_start();
require '../db.php';

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin học sinh
$user_id = $_SESSION['user_id'];

// Lấy thông tin kỳ thi được chọn
$exam_id = $_GET['exam_id'] ?? null;

// Nếu không có exam_id, chuyển đến danh sách kỳ thi
if (!$exam_id) {
    header('Location: view_exams.php');
    exit;
}

// Lấy thông tin kết quả của kỳ thi cụ thể cho học sinh
$sql = "
    SELECT 
        e.exam_id, 
        e.exam_name, 
        e.subject, 
        e.start_time, 
        e.duration, 
        er.score, 
        er.correct_answers, 
        er.wrong_answers
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.exam_id
    WHERE er.user_id = ? AND er.exam_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $exam_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo "Không tìm thấy kết quả cho kỳ thi này.";
    exit;
}

// Tính thời gian kết thúc kỳ thi
$exam_start_time = new DateTime($result['start_time']);
$exam_duration = $result['duration']; // Duration in minutes
$exam_end_time = $exam_start_time->add(new DateInterval('PT' . $exam_duration . 'M'));

// Kiểm tra thời gian hiện tại so với thời gian kết thúc kỳ thi
$current_time = new DateTime();
$show_details = $current_time > $exam_end_time; // Hiển thị chi tiết nếu kỳ thi đã kết thúc
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả kỳ thi</title>
    <link rel="stylesheet" href="../Admin/css/view_results.css">
</head>
<body>
    <div class="results-container">
        <div class="header">
            <h1>Kết quả kỳ thi</h1>
            <p>Kỳ thi: <span><?php echo htmlspecialchars($result['exam_name']); ?></span></p>
        </div>

        <div class="results-summary">
            <div class="result-item">
                <p class="label">Môn học:</p>
                <p class="value"><?php echo htmlspecialchars($result['subject']); ?></p>
            </div>
            <div class="result-item">
                <p class="label">Điểm số (%):</p>
                <p class="value"><?php echo htmlspecialchars($result['score']); ?></p>
            </div>
            <div class="result-item">
                <p class="label">Số câu đúng:</p>
                <p class="value"><?php echo htmlspecialchars($result['correct_answers']); ?></p>
            </div>
            <div class="result-item">
                <p class="label">Số câu sai:</p>
                <p class="value"><?php echo htmlspecialchars($result['wrong_answers']); ?></p>
            </div>
        </div>

        <div class="details-section">
            <?php if ($show_details): ?>
                <a href="view_exam_details.php?exam_id=<?php echo urlencode($result['exam_id']); ?>" class="btn-details">Xem chi tiết</a>
            <?php else: ?>
                <span class="not-available">Chi tiết chưa khả dụng</span>
            <?php endif; ?>
        </div>

        <div class="footer">
            <a href="view_exams.php" class="btn-back">Trở về danh sách kỳ thi</a>
        </div>
    </div>
</body>
</html>
