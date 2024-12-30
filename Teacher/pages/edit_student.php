<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Lấy thông tin học sinh
    $sql = "SELECT u.*, cs.class_id FROM users u 
            LEFT JOIN class_students cs ON u.user_id = cs.user_id 
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        echo "Học sinh không tồn tại.";
        exit;
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
} else {
    echo "ID không hợp lệ.";
    exit;
}

// Cập nhật thông tin học sinh
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $class_id = $_POST['class_id'];

    $sql_update = "UPDATE users SET username = ?, dob = ?, gender = ?, email = ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssi", $full_name, $dob, $gender, $email, $user_id);

    if ($stmt_update->execute()) {
        // Cập nhật lớp học
       // Kiểm tra và cập nhật thông tin trong bảng `class_students`
       $sql_check_class = "SELECT * FROM class_students WHERE user_id = ?";
       $stmt_check_class = $conn->prepare($sql_check_class);
       $stmt_check_class->bind_param("i", $user_id);
       $stmt_check_class->execute();
       $result_check_class = $stmt_check_class->get_result();

       if ($result_check_class->num_rows > 0) {
           // Nếu đã tồn tại, thực hiện UPDATE
           $sql_class_update = "UPDATE class_students SET class_id = ? WHERE user_id = ?";
           $stmt_class_update = $conn->prepare($sql_class_update);
           $stmt_class_update->bind_param("ii", $class_id, $user_id);
           $stmt_class_update->execute();
       } else {
           // Nếu chưa tồn tại, thực hiện INSERT
           $sql_class_insert = "INSERT INTO class_students (class_id, user_id) VALUES (?, ?)";
           $stmt_class_insert = $conn->prepare($sql_class_insert);
           $stmt_class_insert->bind_param("ii", $class_id, $user_id);
           $stmt_class_insert->execute();
       }
        echo "Thông tin học sinh đã được cập nhật.";
        header("Location: manage_student.php");
        exit;
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin học sinh</title>
    <link rel="stylesheet" href="../CSS_T/edit_student.css">

</head>
<body>
    <h1>Sửa thông tin học sinh</h1>
    <form action="edit_student.php?id=<?php echo $user_id; ?>" method="POST">
        <label for="full_name">Họ và Tên:</label><br>
        <input type="text" id="full_name" name="full_name" value="<?php echo $student['username']; ?>" required><br><br>

        <label for="dob">Ngày sinh:</label><br>
        <input type="date" id="dob" name="dob" value="<?php echo $student['dob']; ?>" required><br><br>

        <label for="gender">Giới tính:</label><br>
        <select id="gender" name="gender">
            <option value="male" <?php if ($student['gender'] === 'male') echo 'selected'; ?>>Nam</option>
            <option value="female" <?php if ($student['gender'] === 'female') echo 'selected'; ?>>Nữ</option>
            <option value="other" <?php if ($student['gender'] === 'other') echo 'selected'; ?>>Khác</option>
        </select><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>" required><br><br>

        <label for="class_id">Chọn khóa học:</label><br>
        <select id="class_id" name="class_id" required>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['class_id']; ?>" <?php if ($student['class_id'] == $class['class_id']) echo 'selected'; ?>>
                    <?php echo $class['class_name']; ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Cập nhật</button>
        <a href="manage_student.php" class="btn btn-secondary">Quay lại</a>

    </form>
</body>
</html>

<?php
$conn->close();
?>
