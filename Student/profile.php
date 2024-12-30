<?php
session_start();
require '../db.php';

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin người dùng từ session
$user_id = $_SESSION['user_id'];

// Xử lý cập nhật thông tin
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = $_POST['email'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];

        $sql = "UPDATE users SET email = ?, dob = ?, gender = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $email, $dob, $gender, $user_id);

        if ($stmt->execute()) {
            $message = "Thông tin cá nhân đã được cập nhật.";
        } else {
            $message = "Cập nhật thất bại: " . $conn->error;
        }
        $stmt->close(); // Đóng statement sau khi sử dụng
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Kiểm tra mật khẩu hiện tại
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close(); // Đóng statement sau khi sử dụng

        if (password_verify($current_password, $hashed_password)) {
            // Cập nhật mật khẩu mới
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_hashed_password, $user_id);

            if ($stmt->execute()) {
                $message = "Mật khẩu đã được thay đổi.";
            } else {
                $message = "Thay đổi mật khẩu thất bại: " . $conn->error;
            }
            $stmt->close(); // Đóng statement sau khi sử dụng
        } else {
            $message = "Mật khẩu hiện tại không đúng.";
        }
    }
}

// Lấy thông tin cá nhân từ database
$sql = "SELECT username, email, dob, gender FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close(); // Đóng statement sau khi sử dụng
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="../Admin/css/profile.css"> <!-- CSS -->
</head>
<body>
    <div class="container">
        <h2>Thông tin cá nhân</h2>
        <div class="text-first mt-4">
            <a href="student_dashboard.php" class="btn">Trở về</a>
        </div>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="dob">Ngày sinh</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>">

            <label for="gender">Giới tính</label>
            <select id="gender" name="gender">
                <option value="male" <?php if ($user['gender'] === 'male') echo 'selected'; ?>>Nam</option>
                <option value="female" <?php if ($user['gender'] === 'female') echo 'selected'; ?>>Nữ</option>
                <option value="other" <?php if ($user['gender'] === 'other') echo 'selected'; ?>>Khác</option>
            </select>

            <button type="submit" name="update_profile">Cập nhật thông tin</button>
        </form>

        <h3>Thay đổi mật khẩu</h3>
        <form method="POST">
            <label for="current_password">Mật khẩu hiện tại</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">Mật khẩu mới</label>
            <input type="password" id="new_password" name="new_password" required>

            <button type="submit" name="change_password">Thay đổi mật khẩu</button>
        </form>
    </div>
</body>
</html>
