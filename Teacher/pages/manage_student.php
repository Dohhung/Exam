<?php
$servername = "localhost";
$username = "root"; // Tên người dùng MySQL của bạn
$password = ""; // Mật khẩu MySQL
$dbname = "onlineexamsystem";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Thiết lập mã hóa UTF-8
$conn->set_charset("utf8mb4");

// Xử lý thêm học sinh
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $class_id = $_POST['class_id'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Kiểm tra xem email đã tồn tại chưa
    $sql_check_email = "SELECT * FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<p style='color: red;'>Email này đã được sử dụng. Vui lòng chọn email khác.</p>";
    } else {
        // Thêm học sinh vào bảng users
        $sql_user = "INSERT INTO users (username, password, email, dob, gender, role) VALUES (?, ?, ?, ?, ?, 'student')";
        $stmt = $conn->prepare($sql_user);
        $stmt->bind_param("sssss", $full_name, $password, $email, $dob, $gender);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id; // Lấy ID của người dùng vừa thêm

            // Thêm học sinh vào lớp học
            $sql_class_student = "INSERT INTO class_students (class_id, user_id) VALUES (?, ?)";
            $stmt_class = $conn->prepare($sql_class_student);
            $stmt_class->bind_param("ii", $class_id, $user_id);
            if ($stmt_class->execute()) {
                echo "<p style='color: green;'>Học sinh đã được thêm thành công.</p>";
            } else {
                echo "<p style='color: red;'>Lỗi thêm học sinh vào lớp: " . $conn->error . "</p>";
            }
            $stmt_class->close();
        } else {
            echo "<p style='color: red;'>Lỗi thêm học sinh: " . $conn->error . "</p>";
        }

        $stmt->close();
    }
    $stmt_check->close();
}


// Lấy danh sách các lớp học
$sql_classes = "SELECT class_id, class_name FROM classes";
$result_classes = $conn->query($sql_classes);
$classes = [];
if ($result_classes->num_rows > 0) {
    while ($row = $result_classes->fetch_assoc()) {
        $classes[] = $row;
    }
}

// Lấy danh sách học sinh
$sql_students = "SELECT u.user_id, u.username, u.dob, u.gender, u.email, c.class_name FROM users u 
                 LEFT JOIN class_students cs ON u.user_id = cs.user_id 
                 LEFT JOIN classes c ON cs.class_id = c.class_id WHERE u.role = 'student'";
$result_students = $conn->query($sql_students);
$students = [];
if ($result_students->num_rows > 0) {
    while ($row = $result_students->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý học sinh</title>
    <link rel="stylesheet" href="../CSS_T/manage_student.css">

</head>
<body>
    

    <!-- Form thêm học sinh trong bảng -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h2>Quản Lý Học Sinh</h2>
        </div>
        <div class="card-body">
            <form action="manage_student.php" method="POST">
                <div class="form-group">
                    <label for="full_name">Họ và tên:</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dob">Ngày sinh:</label>
                    <input type="date" id="dob" name="dob" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="gender">Giới tính:</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="class_id">Khóa học:</label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo $class['class_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success btn-block" style="display: block; margin: 20px auto;">Thêm học sinh</button>
                </form>
        </div>
    </div>

    <h2>Danh sách học sinh</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Họ và Tên</th>
            <th>Ngày sinh</th>
            <th>Giới tính</th>
            <th>Email</th>
            <th>Khóa học</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo $student['user_id']; ?></td>
                <td><?php echo $student['username']; ?></td>
                <td><?php echo $student['dob']; ?></td>
                <td><?php echo $student['gender']; ?></td>
                <td><?php echo $student['email']; ?></td>
                <td><?php echo $student['class_name']; ?></td>
                <td>
                <button class="btn btn-warning" onclick="window.location.href='edit_student.php?id=<?php echo $student['user_id']; ?>'">Sửa</button>
                <button class="btn btn-danger" onclick="if(confirm('Bạn có chắc chắn muốn xóa học sinh này không?')) window.location.href='delete_student.php?id=<?php echo $student['user_id']; ?>';">Xóa</button>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>
    <button onclick="window.location.href='../teacher_dashboard.php';">Quay lại</button>

</body>
</html>

<?php
$conn->close();
?>
