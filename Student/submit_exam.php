<?php
session_start();
require '../db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Lấy thông tin từ form
$user_id = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'] ?? null;
$answers = $_POST['answer'] ?? [];

if (!$exam_id) {
    echo "Dữ liệu không hợp lệ: Mã kỳ thi không được cung cấp.";
    exit;
}

// Kiểm tra xem exam_id có tồn tại trong bảng exams
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    echo "Kỳ thi không tồn tại.";
    exit;
}

// Lấy thời gian bắt đầu kỳ thi và thời gian thi (duration) từ cơ sở dữ liệu
$exam_start_time = new DateTime($exam['start_time']); // start_time có sẵn từ cơ sở dữ liệu
$exam_duration = $exam['duration']; // duration tính bằng phút

// Tính toán thời gian kết thúc kỳ thi
$exam_end_time = $exam_start_time->add(new DateInterval('PT' . $exam_duration . 'M')); // Thêm duration vào thời gian bắt đầu

// Lấy thời gian hiện tại
$current_time = new DateTime();

// Kiểm tra trạng thái kỳ thi
$exam_status = ($current_time < $exam_end_time) ? 'ongoing' : 'completed';

// Khởi tạo biến tổng câu hỏi và câu đúng
$correct_answers = 0;
$total_questions = 0;
$questions_feedback = []; // Mảng chứa thông tin câu hỏi, đáp án đúng, sai

// Sử dụng prepared statement bên ngoài vòng lặp để tối ưu hiệu năng
$stmt_get_question = $conn->prepare("
    SELECT q.question_id, q.question_text, q.question_type, qo.option_id, qo.option_text, qo.is_correct 
    FROM questions q 
    LEFT JOIN question_options qo ON q.question_id = qo.question_id 
    WHERE q.exam_id = ?
");
$stmt_get_question->bind_param("i", $exam_id);
$stmt_get_question->execute();
$result_questions = $stmt_get_question->get_result();

// Duyệt qua từng câu hỏi trong kỳ thi
while ($row = $result_questions->fetch_assoc()) {
    $question_id = $row['question_id'];
    $question_text = $row['question_text'];
    $question_type = $row['question_type'];

    // Nếu câu hỏi chưa được thêm vào danh sách feedback, khởi tạo
    if (!isset($questions_feedback[$question_id])) {
        $questions_feedback[$question_id] = [
            'question_text' => $question_text,
            'question_type' => $question_type,
            'correct_options' => [],
            'user_answer' => $answers[$question_id] ?? null,
            'is_correct' => false,
            'user_answer_text' => null // Thêm trường để lưu đáp án người dùng đã chọn
        ];
        $total_questions++;
    }

    // Thêm các tùy chọn đúng vào danh sách
    if ($row['is_correct']) {
        $questions_feedback[$question_id]['correct_options'][] = [
            'option_id' => $row['option_id'],
            'option_text' => $row['option_text']
        ];
    }

    // Kiểm tra câu trả lời của học sinh và lấy nội dung đáp án người dùng
    if ($question_type === 'multiple_choice') {
        // Kiểm tra nếu đáp án người dùng khớp với đáp án hiện tại
        if ($row['option_id'] == ($answers[$question_id] ?? null)) {
            $questions_feedback[$question_id]['user_answer_text'] = $row['option_text']; // Lưu nội dung đáp án người dùng chọn
            if ($row['is_correct']) {
                $questions_feedback[$question_id]['is_correct'] = true;
                $correct_answers++;
            }
        }
    } elseif ($question_type === 'fill_in_the_blank') {
        foreach ($questions_feedback[$question_id]['correct_options'] as $correct_option) {
            if (strcasecmp(trim($correct_option['option_text']), trim($answers[$question_id] ?? '')) === 0) {
                $questions_feedback[$question_id]['is_correct'] = true;
                $correct_answers++;
                break;
            }
        }
    }

    // Lưu câu trả lời vào bảng answers
    $user_answer = $answers[$question_id] ?? null;

    // Kiểm tra nếu câu trả lời có giá trị hợp lệ (không phải NULL) cho câu hỏi trắc nghiệm
    if ($user_answer !== null) {
        // Kiểm tra nếu là câu hỏi trắc nghiệm (multiple_choice)
        if ($question_type === 'multiple_choice') {
            // Kiểm tra xem option_id có tồn tại trong bảng question_options
            $stmt_check_option = $conn->prepare("SELECT 1 FROM question_options WHERE option_id = ?");
            $stmt_check_option->bind_param("i", $user_answer);
            $stmt_check_option->execute();
            $result_check_option = $stmt_check_option->get_result();

            // Nếu option_id không tồn tại, không lưu câu trả lời
            if ($result_check_option->num_rows == 0) {
                echo "Đáp án không hợp lệ!";
                exit;
            }
        }

        // Kiểm tra xem câu trả lời đã có trong bảng answers chưa
        $stmt_check = $conn->prepare("SELECT * FROM answers WHERE user_id = ? AND question_id = ? AND exam_id = ?");
        $stmt_check->bind_param("iii", $user_id, $question_id, $exam_id);
        $stmt_check->execute();
        $existing_answer = $stmt_check->get_result()->fetch_assoc();

        // Nếu đã có câu trả lời, cập nhật; nếu chưa có thì thêm mới
        if ($existing_answer) {
            // Nếu là câu hỏi trắc nghiệm
            if ($question_type === 'multiple_choice') {
                $stmt_update = $conn->prepare("UPDATE answers SET option_id = ? WHERE user_id = ? AND question_id = ? AND exam_id = ?");
                $stmt_update->bind_param("iiii", $user_answer, $user_id, $question_id, $exam_id);
            } else {
                // Nếu là câu hỏi điền vào chỗ trống
                $stmt_update = $conn->prepare("UPDATE answers SET answer_text = ? WHERE user_id = ? AND question_id = ? AND exam_id = ?");
                $stmt_update->bind_param("siii", $user_answer, $user_id, $question_id, $exam_id);
            }
            $stmt_update->execute();
        } else {
            // Nếu là câu hỏi điền vào chỗ trống, cần lưu đáp án người dùng mà không cần option_id
            if ($question_type === 'fill_in_the_blank') {
                $stmt_insert = $conn->prepare("INSERT INTO answers (user_id, question_id, answer_text, exam_id) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("iisi", $user_id, $question_id, $answers[$question_id], $exam_id);
            } else {
                // Nếu là câu hỏi trắc nghiệm, lưu với option_id
                $stmt_insert = $conn->prepare("INSERT INTO answers (user_id, question_id, option_id, exam_id) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("iiii", $user_id, $question_id, $user_answer, $exam_id);
            }
            $stmt_insert->execute();
        }
    }


}

// Tính điểm
$wrong_answers = max(0, $total_questions - $correct_answers);
$score = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100, 2) : 0;

// Ghi nhận kết quả kỳ thi
$stmt = $conn->prepare("
    INSERT INTO exam_results (exam_id, user_id, score, correct_answers, wrong_answers) 
    VALUES (?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE 
        score = VALUES(score), 
        correct_answers = VALUES(correct_answers), 
        wrong_answers = VALUES(wrong_answers)
");
$stmt->bind_param("iiiii", $exam_id, $user_id, $score, $correct_answers, $wrong_answers);
$stmt->execute();

// Cập nhật trạng thái hoàn thành kỳ thi
$stmt = $conn->prepare("UPDATE exam_participants SET status = 'completed' WHERE exam_id = ? AND user_id = ?");
$stmt->bind_param("ii", $exam_id, $user_id);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả kỳ thi</title>
    <link rel="stylesheet" href="../Admin/css/results.css">
</head>
<body>
    <div class="results-container">
        <h1>Kết quả kỳ thi: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>
        <p><strong>Số câu đúng:</strong> <?php echo $correct_answers; ?> / <?php echo $total_questions; ?></p>
        <p><strong>Điểm số:</strong> <?php echo $score; ?>%</p>
        <hr>
        <h2>Chi tiết từng câu hỏi:</h2>
        <?php if ($exam_status === 'completed') { ?>
            <?php foreach ($questions_feedback as $question_id => $feedback): ?>
                <div class="question-feedback">
                    <p><strong>Câu hỏi:</strong> <?php echo htmlspecialchars($feedback['question_text']); ?></p>
                    <?php if ($feedback['question_type'] === 'multiple_choice'): ?>
                        <p><strong>Đáp án đúng:</strong> 
                            <?php echo implode(', ', array_map(fn($opt) => htmlspecialchars($opt['option_text']), $feedback['correct_options'])); ?>
                        </p>
                    <?php else: ?>
                        <p><strong>Đáp án đúng:</strong> 
                            <?php echo htmlspecialchars($feedback['correct_options'][0]['option_text'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                    <p><strong>Đáp án của bạn:</strong> 
                        <?php 
                        if ($feedback['question_type'] === 'multiple_choice') {
                            echo htmlspecialchars($feedback['user_answer_text'] ?? 'Không trả lời');
                        } elseif ($feedback['question_type'] === 'fill_in_the_blank') {
                            echo htmlspecialchars($feedback['user_answer'] ?? 'Không trả lời');
                        }
                        ?>
                    </p>
                    <p><strong>Kết quả:</strong> 
                        <?php echo $feedback['is_correct'] ? 'Đúng' : 'Sai'; ?>
                    </p>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php } else ?>
            <p>Chi tiết đáp án sẽ được hiển thị sau khi kỳ thi kết thúc.</p>

        <!-- Nút trở về -->
        <div class="back-button">
            <a href="view_exams.php" class="btn-back">Trở về danh sách kỳ thi</a>
        </div>
    </div>
</body>
</html>
