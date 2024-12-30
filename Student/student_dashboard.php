<?php
session_start();
require '../db.php';


// Kiểm tra người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin học sinh
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển học sinh</title>
    <link rel="stylesheet" href="../Admin/css/student_dashboard.css"> <!-- CSS tùy chỉnh -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Xin chào, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p>Chào mừng bạn đến với bảng điều khiển học sinh.</p>
        </div>
        <div class="menu">
            <a href="view_exams.php">Danh sách kỳ thi</a>
            <a href="profile.php">Hồ sơ cá nhân</a>
            <a href="../logout.php">Đăng xuất</a>
        </div>
    </div>
</body>
</html>
