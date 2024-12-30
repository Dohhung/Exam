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

// Lấy `exam_id` từ URL
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
if ($exam_id <= 0) {
    die("Exam ID không hợp lệ.");
}

// Đảm bảo rằng bạn truy vấn lại cơ sở dữ liệu và lấy giá trị của 'difficulty' để hiển thị
$sql_get_questions = "SELECT * FROM questions WHERE exam_id = ?";
$stmt_get_questions = $conn->prepare($sql_get_questions);
$stmt_get_questions->bind_param("i", $exam_id);
$stmt_get_questions->execute();
$questions_result = $stmt_get_questions->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách câu hỏi theo exam_id
$sql_get_questions = "SELECT * FROM questions WHERE exam_id = $exam_id";
$result = $conn->query($sql_get_questions);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_text = $conn->real_escape_string($_POST['question_text']);
    $question_type = $conn->real_escape_string($_POST['question_type']);
    $created_by = 2; // Giả sử người tạo là giáo viên có user_id = 2

    // Thêm câu hỏi vào bảng questions
    $sql = "INSERT INTO questions (exam_id, question_text, question_type) 
            VALUES ($exam_id, '$question_text', '$question_type')";
    if ($conn->query($sql) === TRUE) {
        $question_id = $conn->insert_id; // Lấy ID của câu hỏi vừa được thêm

        // Lưu các đáp án
        $options = $_POST['options']; // Mảng đáp án
        $correct_options = isset($_POST['correct_option']) ? $_POST['correct_option'] : []; // Các đáp án đúng

        foreach ($options as $index => $option_text) {
            $option_text = $conn->real_escape_string($option_text);
            $is_correct = in_array($index, $correct_options) ? 1 : 0; // Kiểm tra nếu đáp án đúng
            $sql_option = "INSERT INTO question_options (question_id, option_text, is_correct) 
                           VALUES ($question_id, '$option_text', $is_correct)";
            if (!$conn->query($sql_option)) {
                echo "<p style='color: red;'>Lỗi khi thêm đáp án: " . $conn->error . "</p>";
            }
        }

        // Sau khi câu hỏi và đáp án được lưu, chuyển hướng đến trang hiện tại để reset form
        header("Location: question_exam.php?exam_id=$exam_id");
        exit();
    } else {
        echo "<p style='color: red;'>Lỗi khi thêm câu hỏi: " . $conn->error . "</p>";
    }
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Câu Hỏi</title>
    <link rel="stylesheet" href="../CSS_T/question_exam.css"> <!-- Nếu có file CSS riêng -->
    
</head>
<body>
    <h1>Đề Thi</h1>
    <form method="post" action="question_exam.php?exam_id=<?php echo $exam_id; ?>">
        <div>
            <label for="question_text">Câu hỏi:</label>
            <textarea name="question_text" id="question_text" required></textarea>
        </div>

        <div>
            <label for="question_type">Loại câu hỏi:</label>
            <select name="question_type" id="question_type">
                <option value="multiple_choice">Trắc nghiệm</option>
                <option value="short_answer">Điền vào chỗ trống</option>
                <option value="true_false">Đúng/Sai</option>
            </select>
        </div>

        <div>
            <label for="topic">Chủ đề:</label>
            <input type="text" name="topic" id="topic">
        </div>

        <div>
            <label for="difficulty">Độ khó:</label>
            <select name="difficulty" id="difficulty">
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
        </div>

        <h3>Đáp án</h3>
        <div id="options">
            <div class="option-group">
                <input type="text" name="options[]" placeholder="Nhập đáp án" required>
                <input type="checkbox" name="correct_option[]" value="1"> Đáp án đúng
            </div>
        </div>

        <button type="button" onclick="addOption()">Thêm Đáp Án</button>
        <br><br>
        <button type="submit">Lưu Câu Hỏi</button>
    </form>

    <h2>Danh Sách Các Câu Hỏi</h2>
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Câu hỏi</th>
                    <th>Loại câu hỏi</th>
                    <th>Chủ đề</th>
                    <th>Độ khó</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['question_text']; ?></td>
                        <td><?php echo ucfirst($row['question_type']); ?></td>
                        <td><?php echo $row['topic']; ?></td>
                        <td><?php echo ucfirst($row['difficulty']); ?></td>
                        <td>
                            <a href="edit_question.php?id=<?php echo $row['question_id']; ?>" class="btn btn-warning btn-sm">Sửa</a> 
                            <a href="delete_question.php?id=<?php echo $row['question_id']; ?>&exam_id=<?php echo $exam_id; ?>" class="btn btn-danger btn-sm"onclick="return confirm('Bạn có chắc muốn xóa câu hỏi này không?')">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có câu hỏi nào cho kỳ thi này.</p>
    <?php endif; ?>

    <script>
        // Hàm thêm đáp án mới
        function addOption() {
            const optionsDiv = document.getElementById("options");
            const optionGroup = document.createElement("div");
            optionGroup.classList.add("option-group");
            optionGroup.innerHTML = `
                <input type="text" name="options[]" placeholder="Nhập đáp án" required>
                <input type="checkbox" name="correct_option[]" value="1"> Đáp án đúng
            `;
            optionsDiv.appendChild(optionGroup);
        }
    </script>
    <button onclick="window.location.href='manage_exam.php';">Quay lại</button>

</body>
</html>
