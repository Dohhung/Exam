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

// Lấy thông tin kỳ thi dựa trên ID
$exam_id = $_GET['id'];
$sql_exam = "SELECT * FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql_exam);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();
$stmt->close();

// Xử lý cập nhật kỳ thi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $subject = $_POST['subject'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];

    $sql_update = "UPDATE exams SET exam_name = ?, subject = ?, start_time = ?, duration = ? WHERE exam_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssii", $exam_name, $subject, $start_time, $duration, $exam_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Cập nhật kỳ thi thành công.</p>";
        header("Location: manage_exam.php");
        exit();
    } else {
        echo "<p style='color: red;'>Lỗi cập nhật kỳ thi: " . $conn->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa kỳ thi</title>
    <link rel="stylesheet" href="../CSS/edit_exam.css">

</head>
<body>
    <h1>Chỉnh sửa kỳ thi</h1>

    <form action="edit_exam.php?id=<?php echo $exam_id; ?>" method="POST">
        <label for="exam_name">Tên kỳ thi:</label><br>
        <input type="text" id="exam_name" name="exam_name" value="<?php echo $exam['exam_name']; ?>" required><br><br>

        <label for="subject">Môn học:</label><br>
        <input type="text" id="subject" name="subject" value="<?php echo $exam['subject']; ?>" required><br><br>

        <label for="start_time">Ngày giờ thi:</label><br>
        <input type="datetime-local" id="start_time" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($exam['start_time'])); ?>" required><br><br>

        <label for="duration">Thời lượng (phút):</label><br>
        <input type="number" id="duration" name="duration" value="<?php echo $exam['duration']; ?>" required><br><br>

        <button type="submit">Lưu thay đổi</button>

        <a href="manage_exam.php" class="btn btn-secondary">Quay lại</a>

    </form>

</body>
</html>
