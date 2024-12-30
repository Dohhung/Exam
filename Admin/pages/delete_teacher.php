<?php
include '../../db.php';

// Kiểm tra nếu có ID của giáo viên
if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];

    // Kiểm tra xem giáo viên có tồn tại trong cơ sở dữ liệu không
    $check_sql = "SELECT * FROM users WHERE user_id = $teacher_id AND role = 'teacher'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Nếu giáo viên tồn tại, thực hiện xóa
        $sql = "DELETE FROM users WHERE user_id = $teacher_id AND role = 'teacher'";

        if ($conn->query($sql) === TRUE) {
            echo "Giáo viên đã được xóa thành công.";
        } else {
            echo "Lỗi khi xóa giáo viên: " . $conn->error;
        }
    } else {
        // Nếu không tìm thấy giáo viên
        echo "Không tìm thấy giáo viên hoặc giáo viên không hợp lệ.";
    }
} else {
    // Nếu không có ID
    echo "Không tìm thấy ID giáo viên.";
}

// Quay lại trang quản lý giáo viên sau khi xóa
header("Location: manage_teacher.php");
exit();
?>
