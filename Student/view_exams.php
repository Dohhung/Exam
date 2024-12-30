<?php
session_start();
require '../db.php';

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy thời gian hiện tại
$current_time = new DateTime();

// Khởi tạo biến $exams
$exams = [];

// Lấy danh sách kỳ thi mà học sinh có thể tham gia
$user_id = $_SESSION['user_id'];
$sql = "
    SELECT 
        e.exam_id, 
        e.exam_name, 
        e.subject, 
        e.start_time, 
        e.duration, 
        ADDTIME(e.start_time, SEC_TO_TIME(e.duration * 60)) AS end_time,
        COALESCE(ep.status, 'not_started') AS user_status
    FROM exams e
    LEFT JOIN exam_participants ep ON e.exam_id = ep.exam_id AND ep.user_id = ?
    ORDER BY e.start_time DESC
";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Chuyển đổi start_time và end_time sang đối tượng DateTime
            $start_time = new DateTime($row['start_time']);
            $end_time = new DateTime($row['end_time']);

            // Thêm logic xác định trạng thái kỳ thi
            if ($current_time < $start_time) {
                $row['exam_status'] = 'not_started'; // Kỳ thi chưa bắt đầu
            } elseif ($current_time >= $start_time && $current_time <= $end_time) {
                $row['exam_status'] = 'ongoing'; // Kỳ thi đang diễn ra
            } else {
                $row['exam_status'] = 'completed'; // Kỳ thi đã kết thúc
            }

            $exams[] = $row;
        }
    }
    $stmt->close();
} else {
    echo "Lỗi truy vấn cơ sở dữ liệu.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách kỳ thi</title>
    <link rel="stylesheet" href="../Admin/CSS/viewstudent_exam.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Danh sách các kỳ thi</h2>
            <p>Chọn kỳ thi bạn muốn tham gia</p>
        </div>
        <?php if (!empty($exams)) { ?>
        <ul class="exam-list">
            <?php foreach ($exams as $exam) { ?>
            <li class="exam-item">
                <div class="exam-info">
                    <div>
                        <h3 class="exam-title"><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                        <p class="exam-subject">Môn học: <?php echo htmlspecialchars($exam['subject']); ?></p>
                        <p class="exam-start">Bắt đầu: <?php echo htmlspecialchars($exam['start_time']); ?></p>
                        <p class="exam-end">Kết thúc: <?php echo htmlspecialchars($exam['end_time']); ?></p>
                    </div>
                    <div>
                        <p class="exam-time">Thời gian: <?php echo htmlspecialchars($exam['duration']); ?> phút</p>
                        <p class="exam-status">Trạng thái: 
                            <?php 
                            echo $exam['exam_status'] === 'not_started' ? 'Chưa bắt đầu' : 
                                 ($exam['exam_status'] === 'ongoing' ? 'Đang diễn ra' : 'Đã kết thúc');
                            ?>
                        </p>
                    </div>
                </div>
                <div class="exam-actions">
                    <?php if ($exam['exam_status'] === 'ongoing') { ?>
                        <a href="exam.php?exam_id=<?php echo urlencode($exam['exam_id']); ?>" class="btn">Bắt đầu thi</a>
                    <?php } elseif ($exam['exam_status'] === 'completed' && $exam['user_status'] === 'completed') { ?>
                        <a href="view_results.php?exam_id=<?php echo urlencode($exam['exam_id']); ?>" class="btn">Xem kết quả</a>
                    <?php } else { ?>
                        <button class="btn disabled" disabled>
                            <?php echo $exam['exam_status'] === 'not_started' ? 'Chưa bắt đầu' : 'Đã kết thúc'; ?>
                        </button>
                    <?php } ?>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } else { ?>
        <p class="no-exams">Hiện tại không có kỳ thi nào khả dụng. Vui lòng kiểm tra lại sau!</p>
        <?php } ?>

        <!-- Back Button -->
        <div class="exam-actions">
            <a href="student_dashboard.php" class="btn-first">Trở về </a>
        </div>
        <div class="footer">
            <p>© 2024 Online Exam System</p>
        </div>
    </div>
</body>
</html>