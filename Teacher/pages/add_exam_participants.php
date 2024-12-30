<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy danh sách kỳ thi
$sql_exams = "SELECT exam_id, exam_name FROM exams";
$result_exams = $conn->query($sql_exams);
$exams = [];
if ($result_exams->num_rows > 0) {
    while ($row = $result_exams->fetch_assoc()) {
        $exams[] = $row;
    }
}

// Lấy danh sách học sinh
$sql_users = "SELECT user_id, username FROM users WHERE role = 'student'";
$result_users = $conn->query($sql_users);
$users = [];
if ($result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Xử lý thêm học sinh vào kỳ thi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $user_ids = $_POST['user_ids']; // Mảng chứa user_id
    $status = 'not_started'; // Trạng thái mặc định

    foreach ($user_ids as $user_id) {
        $sql_insert = "INSERT INTO exam_participants (exam_id, user_id, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("iis", $exam_id, $user_id, $status);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Đã thêm học sinh ID $user_id vào kỳ thi ID $exam_id thành công.</p>";
        } else {
            echo "<p style='color: red;'>Lỗi thêm học sinh ID $user_id: " . $conn->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm học sinh vào kỳ thi</title>
    <link rel="stylesheet" href="../CSS_T/add_exam_participants.css">

</head>
<body>
    <h1>Thêm học sinh vào kỳ thi</h1>

    <form action="add_exam_participants.php" method="POST">
        <label for="exam_id">Chọn kỳ thi:</label><br>
        <select name="exam_id" id="exam_id" required>
            <option value="">-- Chọn kỳ thi --</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?php echo $exam['exam_id']; ?>">
                    <?php echo $exam['exam_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="user_ids">Chọn học sinh:</label>
        <?php foreach ($users as $user): ?>
            <input type="checkbox" name="user_ids[]" value="<?php echo $user['user_id']; ?>">
            <?php echo $user['username']; ?><br>
        <?php endforeach; ?>
        <br>

        <button type="submit">Thêm học sinh</button>
        <button onclick="window.location.href='manage_exam.php';">Quay lại</button>

    </form>
</body>
</html>
