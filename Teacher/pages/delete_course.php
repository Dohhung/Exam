<?php
include '../../db.php';

// Kiểm tra xem có ID của khóa học hay không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID khóa học không hợp lệ.";
    exit;
}

$class_id = $_GET['id'];

// Xóa khóa học khỏi cơ sở dữ liệu
$sql = "DELETE FROM classes WHERE class_id = $class_id";

if ($conn->query($sql) === TRUE) {
    echo "Khóa học đã được xóa thành công.";
} else {
    echo "Lỗi khi xóa khóa học: " . $conn->error;
}

// Chuyển hướng lại trang danh sách khóa học sau khi xóa
header("Location: manage_course.php");
exit;
?>
