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

// Lấy danh sách các khóa học
$sql_classes = "SELECT class_id, class_name FROM classes";
$result_classes = $conn->query($sql_classes);
$classes = [];
if ($result_classes->num_rows > 0) {
    while ($row = $result_classes->fetch_assoc()) {
        $classes[] = $row;
    }
}

// Xử lý thêm kỳ thi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $class_id = $_POST['class_id']; // Lấy ID khóa học từ form
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $created_by = 1; // Giả sử admin có ID là 1

    // Lấy tên lớp học làm môn học
    $class_name = '';
    foreach ($classes as $class) {
        if ($class['class_id'] == $class_id) {
            $class_name = $class['class_name'];
            break;
        }
    }

    $sql_exam = "INSERT INTO exams (exam_name, subject, start_time, duration, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_exam);
    $stmt->bind_param("sssii", $exam_name, $class_name, $start_time, $duration, $created_by);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Kỳ thi đã được thêm thành công.</p>";
    } else {
        echo "<p style='color: red;'>Lỗi thêm kỳ thi: " . $conn->error . "</p>";
    }

    $stmt->close();
}

// Lấy danh sách kỳ thi
$sql_exams = "SELECT exam_id, exam_name, subject, start_time, duration FROM exams";
$result_exams = $conn->query($sql_exams);
$exams = [];
if ($result_exams->num_rows > 0) {
    while ($row = $result_exams->fetch_assoc()) {
        $exams[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kỳ thi</title>
    <link rel="stylesheet" href="../CSS_T/manage_exam.css">

    <style>
        .exam-title {
            font-size: larger;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Quản lý kỳ thi</h1>

    <form action="manage_exam.php" method="POST">
    <h2>Thêm kỳ thi mới</h2>

        <label for="exam_name">Tên kỳ thi:</label><br>
        <input type="text" id="exam_name" name="exam_name" required><br><br>

        <label for="class_id">Khóa học:</label><br>
        <select id="class_id" name="class_id" required>
            <option value="">-- Chọn khóa học --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['class_id']; ?>">
                    <?php echo $class['class_name']; ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="start_time">Ngày giờ thi:</label><br>
        <input type="datetime-local" id="start_time" name="start_time" required><br><br>

        <label for="duration">Thời lượng (phút):</label><br>
        <input type="number" id="duration" name="duration" required><br><br>

        <button type="submit">Thêm kỳ thi</button>
    </form>

    <h2>Danh sách kỳ thi</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Tên kỳ thi</th>
            <th>Môn học</th>
            <th>Ngày giờ thi</th>
            <th>Thời lượng</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?php echo $exam['exam_id']; ?></td>
                <td class="exam-title"><?php echo $exam['exam_name']; ?></td>
                <td><?php echo $exam['subject']; ?></td>
                <td><?php echo $exam['start_time']; ?></td>
                <td><?php echo $exam['duration']; ?> phút</td>
                <td>
                    <div class="action-buttons">
                        <a href="edit_exam.php?id=<?php echo $exam['exam_id']; ?>" class="primary">Sửa</a>
                        <a href="delete_exam.php?id=<?php echo $exam['exam_id']; ?>" class="danger" onclick="return confirm('Bạn có chắc chắn muốn xóa kỳ thi này không?');">Xóa</a>
                        <a href="question_exam.php?exam_id=<?php echo $exam['exam_id']; ?>" class="success">Thêm câu hỏi</a>
                        <a href="view_exam.php?exam_id=<?php echo $exam['exam_id']; ?>" class="info">Xem đề thi</a>
                        <a href="add_exam_participants.php?exam_id=<?php echo $exam['exam_id']; ?>" class="add-student">Thêm học sinh</a>
                    </div>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>
    <button onclick="window.location.href='../teacher_dashboard.php';">Quay lại</button>
</body>
</html>

<?php
//$conn->close();
?>
