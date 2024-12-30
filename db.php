<?php
$servername = "localhost";
$username = "root";  // Tên người dùng MySQL của bạn
$password = "";      // Mật khẩu MySQL
$dbname = "onlineexamsystem";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tạo cơ sở dữ liệu
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    //echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error;
}

// Sử dụng cơ sở dữ liệu đã tạo
$conn->select_db($dbname);

// Thiết lập mã hóa UTF-8
$conn->set_charset("utf8mb4");

// Tạo các bảng
$queries = [
    // Bảng người dùng
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        dob DATE DEFAULT NULL,
        gender ENUM('male', 'female', 'other') DEFAULT 'other',
        role ENUM('student', 'teacher', 'admin') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Bảng kỳ thi
    "CREATE TABLE IF NOT EXISTS exams (
        exam_id INT AUTO_INCREMENT PRIMARY KEY,
        exam_name VARCHAR(255) NOT NULL,
        subject VARCHAR(100),
        start_time DATETIME NOT NULL,
        duration INT NOT NULL,
        created_by INT,
        FOREIGN KEY (created_by) REFERENCES users(user_id),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Bảng câu hỏi
    "CREATE TABLE IF NOT EXISTS questions (
        question_id INT AUTO_INCREMENT PRIMARY KEY,
        question_text TEXT NOT NULL,
        question_type ENUM('multiple_choice', 'fill_in_the_blank') NOT NULL,
        exam_id INT,
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Bảng tùy chọn câu trả lời
    "CREATE TABLE IF NOT EXISTS question_options (
        option_id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        option_text TEXT NOT NULL,
        is_correct BOOLEAN NOT NULL,
        FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
    )",

    // Bảng quản lý học sinh tham gia kỳ thi
    "CREATE TABLE IF NOT EXISTS exam_participants (
        exam_participant_id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT,
        user_id INT,
        status ENUM('not_started', 'in_progress', 'completed') NOT NULL,
        start_time DATETIME,
        end_time DATETIME,
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",
    
    // Bảng lưu tiến trình làm bài của học sinh
    "CREATE TABLE IF NOT EXISTS exam_progress (
        progress_id INT AUTO_INCREMENT PRIMARY KEY,
        exam_participant_id INT,
        question_id INT,
        user_answer TEXT,
        answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (exam_participant_id) REFERENCES exam_participants(exam_participant_id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
    )",

   // Bảng quản lý lớp học của giáo viên
    "CREATE TABLE IF NOT EXISTS classes (
        class_id INT AUTO_INCREMENT PRIMARY KEY,
        class_name VARCHAR(255) NOT NULL,
        teacher_id INT,
        FOREIGN KEY (teacher_id) REFERENCES users(user_id)
    )",

    // Bảng học sinh trong lớp
    "CREATE TABLE IF NOT EXISTS class_students (
        class_student_id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT,
        user_id INT,
        FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )",

    // Bảng kết quả kỳ thi
    "CREATE TABLE IF NOT EXISTS exam_results (
        result_id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT NOT NULL,
        user_id INT NOT NULL,
        score DECIMAL(5,2) NOT NULL,
        correct_answers INT NOT NULL,
        wrong_answers INT NOT NULL,
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )",

    "CREATE TABLE IF NOT EXISTS answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        question_id INT NOT NULL,
        option_id INT,
        exam_id INT NOT NULL,
        answer_text TEXT,
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
        FOREIGN KEY (question_id) REFERENCES questions(question_id),
        FOREIGN KEY (option_id) REFERENCES question_options(option_id)
    )"
];

// Thực thi các câu lệnh tạo bảng
foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        //echo "Table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
}

// Dữ liệu mẫu cho bảng users
$sql_users = "
INSERT INTO users (username, password, email, dob, gender, role) VALUES
('nguyenvanA', '" . password_hash('123456', PASSWORD_BCRYPT) . "', 'nguyenvana@example.com', '2000-01-01', 'male', 'student'),
('tranthithB', '" . password_hash('123456', PASSWORD_BCRYPT) . "', 'tranthithb@example.com', '1995-05-15', 'female', 'teacher'),
('admin1', '" . password_hash('adminpass', PASSWORD_BCRYPT) . "', 'admin@example.com', '1980-12-10', 'other', 'admin');
";
//$conn->query($sql_users);

// Dữ liệu mẫu cho bảng exams
$sql_exams = "
INSERT INTO exams (exam_name, subject, start_time, duration, created_by) VALUES
('Kỳ thi Toán học - Học kỳ 1', 'Toán học', '2024-12-20 08:00:00', 60, 2),
('Kỳ thi Địa lý - Học kỳ 1', 'Địa lý', '2024-12-22 09:00:00', 45, 2);
";
//$conn->query($sql_exams);

// Dữ liệu mẫu cho bảng questions
$sql_questions = "
INSERT INTO questions (question_text, question_type, exam_id) VALUES
('Hà Nội là thủ đô của quốc gia nào?', 'multiple_choice', 2),
('Điền từ: Trái đất quay quanh ___.', 'fill_in_the_blank', 1);
";
//$conn->query($sql_questions);

// Dữ liệu mẫu cho bảng question_options
$sql_question_options = "
INSERT INTO question_options (question_id, option_text, is_correct) VALUES
(1, 'Việt Nam', TRUE),
(1, 'Thái Lan', FALSE),
(1, 'Lào', FALSE),
(1, 'Campuchia', FALSE),
(2, 'Mặt trời', TRUE),
(2, 'Mặt trăng', TRUE);
";
//$conn->query($sql_question_options);

// Dữ liệu mẫu cho bảng exam_progress (Tiến trình thi)
$sql_exam_progress = "
INSERT INTO exam_progress (exam_participant_id, question_id, user_answer) VALUES
(1, 1, 'Việt Nam'),
(1, 2, '4');
";
//$conn->query($sql_exam_progress);

// Dữ liệu mẫu cho bảng classes (Lớp học)
$sql_classes = "
INSERT INTO classes (class_name, teacher_id) VALUES
('Lớp Toán học nâng cao', 2),
('Lớp Địa lý cơ bản', 2);
";
//$conn->query($sql_classes);

// Dữ liệu mẫu cho bảng class_students (Học sinh trong lớp)
$sql_class_students = "
INSERT INTO class_students (class_id, user_id) VALUES
(1, 1),
(2, 1);
";
//$conn->query($sql_class_students);

// Dữ liệu mẫu cho bảng exam_participants
$sql_exam_participants = "
INSERT INTO exam_participants (exam_id, user_id, status) VALUES
(1, 1, 'not_started'),
(2, 1, 'not_started');
";
//$conn->query($sql_exam_participants);

//echo "Database setup completed successfully.";
?>
