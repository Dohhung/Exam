<?php
session_start();
require '../db.php'; // Kết nối DB

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin kỳ thi từ URL
$exam_id = $_GET['exam_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$exam_id) {
    echo "Kỳ thi không tồn tại.";
    exit;
}

// Kiểm tra xem người dùng đã tham gia kỳ thi chưa
$stmt = $conn->prepare("SELECT status FROM exam_participants WHERE exam_id = ? AND user_id = ?");
$stmt->bind_param("ii", $exam_id, $user_id);
$stmt->execute();
$participant = $stmt->get_result()->fetch_assoc();

if ($participant && $participant['status'] === 'completed') {
    echo "Bạn đã hoàn thành kỳ thi này. Bạn không thể tham gia lại.";
    exit;
}

// Lấy thông tin kỳ thi
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Không tìm thấy thông tin kỳ thi.";
    exit;
}

// Lấy danh sách câu hỏi
$stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$questions = $stmt->get_result();

if (!$questions || $questions->num_rows == 0) {
    echo "Không tìm thấy câu hỏi cho kỳ thi này.";
    exit;
}

// Thêm thông tin tham gia kỳ thi nếu chưa có
if (!$participant) {
    $stmt = $conn->prepare("INSERT INTO exam_participants (exam_id, user_id, status) VALUES (?, ?, 'in_progress')");
    $stmt->bind_param("ii", $exam_id, $user_id);
    $stmt->execute();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['exam_name']); ?> - Online Exam</title>
    <link rel="stylesheet" href="../Admin/css/exam.css">
    <script>
        let timeLeft = <?php echo $exam['duration'] * 60; ?>;

        function countdown() {
            const timer = document.getElementById('timer');
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timer.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            if (timeLeft > 0) {
                timeLeft--;
                setTimeout(countdown, 1000);
            } else {
                document.getElementById('exam-form').submit();
            }
        }

        function validateForm() {
            const inputs = document.querySelectorAll('input[type="radio"]:required, input[type="text"]:required');
            for (const input of inputs) {
                if (input.type === 'radio' && !document.querySelector(`input[name="${input.name}"]:checked`)) {
                    alert("Vui lòng hoàn thành tất cả các câu hỏi trước khi nộp bài.");
                    return false;
                }
                if (input.type === 'text' && input.value.trim() === '') {
                    alert("Vui lòng hoàn thành tất cả các câu hỏi trước khi nộp bài.");
                    return false;
                }
            }
            return true;
        }

        function saveAnswer(questionId, value) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_answer.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(`question_id=${questionId}&option_id=${value}`);
        }

        window.onload = countdown;
    </script>
</head>
<body>
    <div class="exam-container">
        <header class="exam-header">
            <h1><?php echo htmlspecialchars($exam['exam_name']); ?></h1>
            <div id="timer" class="timer"></div>
        </header>

        <form id="exam-form" action="submit_exam.php" method="POST" class="exam-form" onsubmit="return validateForm()">
            <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
            <input type="hidden" name="time_left" id="time_left">
            <?php while ($question = $questions->fetch_assoc()): ?>
                <div class="question-container">
                    <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                    <div class="options-container">
                        <?php if ($question['question_type'] == 'multiple_choice'): ?>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM question_options WHERE question_id = ?");
                            $stmt->bind_param("i", $question['question_id']);
                            $stmt->execute();
                            $options = $stmt->get_result();
                            while ($option = $options->fetch_assoc()):
                            ?>
                                <label class="option-label">
                                    <input type="radio" name="answer[<?php echo $question['question_id']; ?>]" 
                                           value="<?php echo $option['option_id']; ?>" 
                                           onclick="saveAnswer(<?php echo $question['question_id']; ?>, <?php echo $option['option_id']; ?>)" required>
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                </label>
                            <?php endwhile; ?>
                        <?php elseif ($question['question_type'] == 'fill_in_the_blank'): ?>
                            <input type="text" name="answer[<?php echo $question['question_id']; ?>]" 
                                   placeholder="Nhập câu trả lời..." 
                                   required 
                                   onchange="saveAnswer(<?php echo $question['question_id']; ?>, this.value)">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            <div class="submit-container">
                <button type="submit" class="submit-button" onclick="document.getElementById('time_left').value = timeLeft;">Nộp bài</button>
            </div>
        </form>
    </div>
</body>
</html>
