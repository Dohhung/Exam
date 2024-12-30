<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Lấy ID kỳ thi
$exam_id = $_GET['id'];

// Xóa kỳ thi
$sql_delete = "DELETE FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("i", $exam_id);

if ($stmt->execute()) {
    echo "<p style='color: green;'>Kỳ thi đã được xóa thành công.</p>";
} else {
    echo "<p style='color: red;'>Lỗi khi xóa kỳ thi: " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();

// Quay lại trang quản lý kỳ thi
header("Location: manage_exam.php");
exit();
?>
