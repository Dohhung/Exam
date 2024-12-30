<?php
include '../../db.php';

// Xử lý thêm khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_id'];

    $sql = "INSERT INTO classes (class_name, teacher_id) VALUES ('$class_name', $teacher_id)";
    if ($conn->query($sql) === TRUE) {
       // echo "Khóa học được thêm thành công.";
    } else {
        //echo "Lỗi: " . 
        $conn->error;
    }
}

// Lấy danh sách khóa học
$result = $conn->query("SELECT * FROM classes");

// Lấy danh sách giáo viên để hiển thị trong dropdown
$teachers_result = $conn->query("SELECT user_id, username FROM users WHERE role = 'teacher'");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khóa học</title>
    <link rel="stylesheet" href="../CSS_T/manage_course.css">
</head>
<body>
<div class="container mt-5">
    <h2>Quản lý khóa học</h2>

    <!-- Form thêm khóa học -->
    <form method="POST" class="mb-3">
        <div class="form-group">
            <label for="class_name">Tên khóa học</label>
            <input type="text" id="class_name" name="class_name" placeholder="Tên khóa học" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="teacher_id">Chọn giáo viên</label>
            <select id="teacher_id" name="teacher_id" class="form-control" required>
                <option value="">Chọn giáo viên</option>
                <?php while ($teacher = $teachers_result->fetch_assoc()) { ?>
                    <option value="<?= $teacher['user_id'] ?>"><?= $teacher['username'] ?></option>
                <?php } ?>
            </select>
        </div>

        <button type="submit" name="add_course" class="btn btn-primary">Thêm khóa học</button>
    </form>

    <!-- Danh sách khóa học -->
    <h3>Danh sách khóa học</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên khóa học</th>
                <th>Giáo viên</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Lấy thông tin giáo viên cho mỗi khóa học
            while ($row = $result->fetch_assoc()) {
                // Lấy tên giáo viên từ user_id
                $teacher_id = $row['teacher_id'];
                $teacher_result = $conn->query("SELECT username FROM users WHERE user_id = $teacher_id AND role = 'teacher'");
                $teacher = $teacher_result->fetch_assoc();
            ?>
                <tr>
                    <td><?= $row['class_id'] ?></td>
                    <td><?= $row['class_name'] ?></td>
                    <td><?= $teacher['username'] ?></td>
                    <td>
                        <a href="edit_course.php?id=<?= $row['class_id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="delete_course.php?id=<?= $row['class_id'] ?>" class="btn btn-danger btn-sm">Xóa</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<button onclick="window.location.href='../teacher_dashboard.php';">Quay lại</button>

</body>
</html>
