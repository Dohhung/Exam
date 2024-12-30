<?php
include '../../db.php';

if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];

    // Lấy thông tin giáo viên
    $result = $conn->query("SELECT * FROM users WHERE user_id = $teacher_id AND role = 'teacher'");
    $teacher = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $update_sql = "UPDATE users SET username = '$username', email = '$email'";

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $update_sql .= ", password = '$hashed_password'";
        }

        $update_sql .= " WHERE user_id = $teacher_id";

        if ($conn->query($update_sql) === TRUE) {
            echo "Thông tin giáo viên đã được cập nhật.";
        } else {
            echo "Lỗi: " . $conn->error;
        }
    }
} else {
    echo "Không tìm thấy giáo viên.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Giáo viên</title>
    <link rel="stylesheet" href="../CSS/edit_teacher.css">
</head>
<body>
<div class="container mt-5">
    <h2>Cập nhật thông tin Giáo Viên</h2>

    <form method="POST">
        <div class="form-group">
            <label for="username">Họ và tên GV:</label>
            <input type="text" id="username" name="username" value="<?= $teacher['username'] ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $teacher['email'] ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu (để trống nếu không thay đổi):</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>

        <button type="submit" name="update_teacher" class="btn btn-primary">Cập nhật giáo viên</button>
        <div class="text-center mt-4">
       <br>
        <a href="manage_teacher.php" class="btn btn-secondary">Quay lại</a>
        </div>

    </form>
</div>
</body>
</html>
