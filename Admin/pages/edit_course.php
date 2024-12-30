<?php
include '../../db.php';

// Kiểm tra xem có ID của khóa học hay không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID khóa học không hợp lệ.";
    exit;
}

$class_id = $_GET['id'];

// Lấy thông tin khóa học từ cơ sở dữ liệu
$sql = "SELECT * FROM classes WHERE class_id = $class_id";
$result = $conn->query($sql);
$class = $result->fetch_assoc();

if (!$class) {
    echo "Khóa học không tồn tại.";
    exit;
}

// Lấy danh sách giáo viên để hiển thị trong dropdown
$teachers_result = $conn->query("SELECT user_id, username FROM users WHERE role = 'teacher'");

// Xử lý cập nhật thông tin khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_id'];

    $update_sql = "UPDATE classes SET class_name = '$class_name', teacher_id = $teacher_id WHERE class_id = $class_id";

    if ($conn->query($update_sql) === TRUE) {
        echo "Cập nhật khóa học thành công.";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa khóa học</title>
    <link rel="stylesheet" href="../CSS/edit_course.css">
</head>
<body>
<div class="container mt-5">
    <h2>Sửa khóa học</h2>

    <!-- Form chỉnh sửa khóa học -->
    <form method="POST">
        <div class="form-group">
            <label for="class_name">Tên khóa học</label>
            <input type="text" id="class_name" name="class_name" placeholder="Tên khóa học" class="form-control" value="<?= $class['class_name'] ?>" required>
        </div>

        <div class="form-group">
            <label for="teacher_id">Chọn giáo viên</label>
            <select id="teacher_id" name="teacher_id" class="form-control" required>
                <option value="">Chọn giáo viên</option>
                <?php while ($teacher = $teachers_result->fetch_assoc()) { ?>
                    <option value="<?= $teacher['user_id'] ?>" <?= $teacher['user_id'] == $class['teacher_id'] ? 'selected' : '' ?>><?= $teacher['username'] ?></option>
                <?php } ?>
            </select>
        </div>

         <!-- Cả hai nút nằm trong một div để căn giữa -->
         <div class="form-buttons">
            <button type="submit" name="update_course" class="btn btn-primary">Cập nhật khóa học</button>
            <a href="manage_course.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>
   
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
