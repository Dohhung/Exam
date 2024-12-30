<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlineexamsystem";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy `question_id` từ URL
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id <= 0) {
    die("Question ID không hợp lệ.");
}

// Lấy thông tin câu hỏi từ cơ sở dữ liệu
$sql_get_question = "SELECT * FROM questions WHERE question_id = ?";
$stmt = $conn->prepare($sql_get_question);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();

if (!$question) {
    die("Không tìm thấy câu hỏi.");
}

// Lấy danh sách các đáp án
$sql_get_options = "SELECT * FROM question_options WHERE question_id = ?";
$stmt_options = $conn->prepare($sql_get_options);
$stmt_options->bind_param("i", $question_id);
$stmt_options->execute();
$options_result = $stmt_options->get_result();
$options = $options_result->fetch_all(MYSQLI_ASSOC);

// Xử lý khi form được gửi để cập nhật câu hỏi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];

    $sql_update = "UPDATE questions 
                   SET question_text = ?, 
                       question_type = ?, 
                   WHERE question_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $question_text, $question_type, $question_id);

    if ($stmt_update->execute()) {
        echo "<p style='color: green;'>Câu hỏi đã được cập nhật thành công!</p>";
        header("Location: question_exam.php?exam_id=" . $question['exam_id']);
        exit();
    } else {
        echo "<p style='color: red;'>Lỗi khi cập nhật câu hỏi: " . $conn->error . "</p>";
    }
}

// Xử lý thêm đáp án
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $option_text = $_POST['option_text'];
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;

    $sql_add_option = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
    $stmt_add_option = $conn->prepare($sql_add_option);
    $stmt_add_option->bind_param("isi", $question_id, $option_text, $is_correct);

    if ($stmt_add_option->execute()) {
        echo "<p style='color: green;'>Đáp án đã được thêm thành công!</p>";
        header("Location: edit_question.php?id=$question_id");
        exit();
    } else {
        echo "<p style='color: red;'>Lỗi khi thêm đáp án: " . $conn->error . "</p>";
    }
}

// Xử lý xóa đáp án
if (isset($_GET['delete_option'])) {
    $option_id = (int)$_GET['delete_option'];

    $sql_delete_option = "DELETE FROM question_options WHERE option_id = ?";
    $stmt_delete_option = $conn->prepare($sql_delete_option);
    $stmt_delete_option->bind_param("i", $option_id);

    if ($stmt_delete_option->execute()) {
        echo "<p style='color: green;'>Đáp án đã được xóa thành công!</p>";
        header("Location: question_exam.php?exam_id=" . $question['exam_id']);
        exit();
    } else {
        echo "<p style='color: red;'>Lỗi khi xóa đáp án: " . $conn->error . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa câu hỏi</title>
    <link rel="stylesheet" href="../CSS_T/edit_question.css">

</head>
<body>
    <h1>Chỉnh sửa Câu Hỏi</h1>
    <form method="post" action="edit_question.php?id=<?php echo $question_id; ?>">
        <div>
            <label for="question_text">Câu hỏi:</label><br>
            <textarea name="question_text" id="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
        </div>
        <br>
        <div>
            <label for="question_type">Loại câu hỏi:</label><br>
            <select name="question_type" id="question_type" required>
                <option value="multiple_choice" <?php echo $question['question_type'] === 'multiple_choice' ? 'selected' : ''; ?>>Trắc nghiệm</option>
                <option value="short_answer" <?php echo $question['question_type'] === 'short_answer' ? 'selected' : ''; ?>>Điền vào chỗ trống</option>
                <option value="true_false" <?php echo $question['question_type'] === 'true_false' ? 'selected' : ''; ?>>Đúng/Sai</option>
            </select>
        </div>
        <br>
        <div>
            <label for="topic">Chủ đề:</label><br>
            <input type="text" name="topic" id="topic" value="<?php echo htmlspecialchars($question['topic']); ?>">
        </div>
        <br>
        <div>
            <label for="difficulty">Độ khó:</label>
            <select name="difficulty" id="difficulty" required>
                <option value="easy" <?php echo $question['difficulty'] === 'easy' ? 'selected' : ''; ?>>Dễ</option>
                <option value="medium" <?php echo $question['difficulty'] === 'medium' ? 'selected' : ''; ?>>Trung bình</option>
                <option value="hard" <?php echo $question['difficulty'] === 'hard' ? 'selected' : ''; ?>>Khó</option>
            </select>
        </div>
    </form>
    <br>
    <table border="1">
        <tr>
            <th>Đáp án</th>
            <th>Đúng/Sai</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($options as $option): ?>
            <tr>
                <td><?php echo htmlspecialchars($option['option_text']); ?></td>
                <td><?php echo $option['is_correct'] ? 'Đúng' : 'Sai'; ?></td>
                <td>
                    <a href="edit_question.php?id=<?php echo $question_id; ?>&delete_option=<?php echo $option['option_id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa đáp án này không?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    
    <form method="post" action="edit_question.php?id=<?php echo $question_id; ?>">
        <h3>Thêm đáp án mới</h3>
        <div>
            <label for="option_text">Đáp án:</label>
            <input type="text" name="option_text" id="option_text" required>
        </div>
        <div>
            <label for="is_correct">Đúng:</label>
            <input type="checkbox" name="is_correct" id="is_correct" value="1">
        </div>
        <br>
        <button type="submit" name="add_option">Thêm đáp án</button>
    </form>
    <br>
    <button onclick="window.location.href='question_exam.php?exam_id=<?php echo $question['exam_id']; ?>';">Cập nhật</button>
</body>
</html>
