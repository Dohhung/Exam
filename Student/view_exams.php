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
    <title>Kết quả kỳ thi</title>
    <link rel="stylesheet" href="../Admin/css/view_results.css">
</head>
<body>
    <div class="results-container">
        <h1 class="page-title">Kết quả kỳ thi</h1>
        <div class="table-container">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Tên kỳ thi</th>
                        <th>Môn học</th>
                        <th>Điểm số (%)</th>
                        <th>Số câu đúng</th>
                        <th>Số câu sai</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                        <td><?php echo htmlspecialchars($result['subject']); ?></td>
                        <td><?php echo htmlspecialchars($result['score']); ?></td>
                        <td><?php echo htmlspecialchars($result['correct_answers']); ?></td>
                        <td><?php echo htmlspecialchars($result['wrong_answers']); ?></td>
                        <td>
                            <?php if ($show_details): ?>
                                <a href="view_exam_details.php?exam_id=<?php echo urlencode($result['exam_id']); ?>" class="btn-details">Xem chi tiết</a>
                            <?php else: ?>
                                <span class="not-available">Chưa có chi tiết</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="back-button">
            <a href="view_exams.php" class="btn-back">Trở về danh sách kỳ thi</a>
        </div>
    </div>
</body>
</html>
