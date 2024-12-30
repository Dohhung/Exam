<?php
include '../db.php';
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa và có phải là giáo viên không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header('Location: login.php'); // Nếu không phải giáo viên, chuyển hướng về trang đăng nhập
    exit();
}

// Lấy dữ liệu tổng số
$sql_exams = "SELECT COUNT(*) AS total_exams FROM exams";
$result_exams = $conn->query($sql_exams);
$exams_count = $result_exams->fetch_assoc()['total_exams'];

$sql_courses = "SELECT COUNT(*) AS total_courses FROM classes WHERE teacher_id = (SELECT user_id FROM users WHERE username = '{$_SESSION['username']}')";
$result_courses = $conn->query($sql_courses);
$courses_count = $result_courses->fetch_assoc()['total_courses'];

$sql_students = "
    SELECT COUNT(DISTINCT class_students.user_id) AS total_students 
    FROM class_students 
    JOIN classes ON classes.class_id = class_students.class_id 
    WHERE classes.teacher_id = (SELECT user_id FROM users WHERE username = '{$_SESSION['username']}')
";
$result_students = $conn->query($sql_students);
$students_count = $result_students->fetch_assoc()['total_students'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="CSS_T/teacher_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Cột trái: Chức năng -->
        <div class="sidebar">
            <h2>Teacher Panel</h2>
            <ul class="function-list">
                <li><a href="pages/manage_course.php">Quản lý Khóa Học</a></li>
                <li><a href="pages/manage_student.php">Quản lý Học Sinh</a></li>
                <li><a href="pages/manage_exam.php">Quản lý Kỳ Thi</a></li>
                <li><a href="pages/exam_history.php">Lịch sử Kỳ Thi</a></li>
                <li><a href="pages/exam_result.php">Kết quả Kỳ Thi</a></li>
                <li><a href="../logout.php">Đăng xuất</a></li>
            </ul>
        </div>

        <!-- Cột phải: Thông tin tổng quát -->
        <div class="main-content">
            <div class="stat-box exams">
                <h3>Kỳ thi</h3>
                <p><?php echo $exams_count; ?> Kỳ thi</p>
            </div>
            <div class="stat-box courses">
                <h3>Khóa học</h3>
                <p><?php echo $courses_count; ?> Khóa học</p>
            </div>
            <div class="stat-box students">
                <h3>Học sinh</h3>
                <p><?php echo $students_count; ?> Học sinh</p>
            </div>
        </div>
    </div>
</body>
</html>
