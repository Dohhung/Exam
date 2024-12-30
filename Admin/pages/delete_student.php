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

    // Xóa học sinh khỏi bảng class_students
    $sql_delete_class_student = "DELETE FROM class_students WHERE user_id = ?";
    $stmt_class_student = $conn->prepare($sql_delete_class_student);
    $stmt_class_student->bind_param("i", $user_id);
    $stmt_class_student->execute();

    // Xóa học sinh khỏi bảng users
    $sql_delete_user = "DELETE FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_delete_user);
    $stmt_user->bind_param("i", $user_id);

    if ($stmt_user->execute()) {
        echo "Học sinh đã được xóa.";
        header("Location: manage_student.php");
        exit;
    } else {
        echo "Lỗi xóa học sinh: " . $conn->error;
    }
} else {
    echo "ID không hợp lệ.";
    exit;
}

$conn->close();
?>
