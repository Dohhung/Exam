<?php
include '../../db.php';

// Xử lý thêm giáo viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // Mã hóa mật khẩu
    $email = $_POST['email'];

    // Kiểm tra xem email đã tồn tại hay chưa
    $check_email = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        // Nếu email đã tồn tại, hiển thị thông báo lỗi
        //echo "Lỗi: Email này đã tồn tại. Vui lòng sử dụng email khác.";
    } else {
        // Nếu email chưa tồn tại, thực hiện thêm giáo viên
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password, email, role) VALUES ('$username', '$hashed_password', '$email', 'teacher')";
        
        if ($conn->query($sql) === TRUE) {
            //echo "Giáo viên được thêm thành công.";
        } else {
            echo "Lỗi: " . $conn->error;
        }
    }
}

// Lấy danh sách giáo viên
$result = $conn->query("SELECT * FROM users WHERE role = 'teacher'");
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giáo viên</title>
    <link rel="stylesheet" href="../CSS/manage_teacher.css">
    
</head>
<body>
    <h1> Quản lý Giáo Viên</h1>
<div class="container mt-5">
    <h2>Bảng Giáo Viên</h2>

    <!-- Form thêm giáo viên -->
    <form method="POST" class="mb-3">
        <div class="form-group">
            <label for="username">Tên giáo viên:</label>
            <input type="text" id="username" name="username" placeholder="Tên người dùng" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" placeholder="Mật khẩu" class="form-control" required>
        </div>

        <button type="submit" name="add_teacher" class="btn btn-primary">Thêm giáo viên</button>
    </form>

    <!-- Danh sách giáo viên -->
    <h3>Danh sách giáo viên</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên giáo viên</th>
                <th>Email</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <a href="edit_teacher.php?id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="delete_teacher.php?id=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm">Xóa</a>
                    </td>
                </tr>
                
            <?php } ?>
        </tbody>
    </table>
    <div class="text-center mb-4">
    <button onclick="window.location.href='../admin_dashboard.php';">Quay lại</button>

    </div>
</div>
</body>
</html>
