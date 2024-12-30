<?php
include '../../db.php';

// Kiểm tra nếu có từ khóa tìm kiếm
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Lấy danh sách kết quả thi với tìm kiếm
$sql = "
    SELECT er.result_id, e.exam_name, u.username, er.score, er.correct_answers, er.wrong_answers
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.exam_id
    JOIN users u ON er.user_id = u.user_id
    WHERE e.exam_name LIKE '%$search_query%'  -- Lọc theo tên kỳ thi
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả kỳ thi</title>
    <link rel="stylesheet" href="../CSS_T/exam_result.css">
</head>
<body>
<div class="container mt-5">
    <h2>Kết quả kỳ thi</h2>
    
    <!-- Form tìm kiếm kỳ thi -->
    <form method="get" action="exam_result.php">
        <div class="search-box">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Tìm kiếm kỳ thi..." class="form-control">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kỳ thi</th>
                <th>Thí sinh</th>
                <th>Điểm</th>
                <th>Số câu đúng</th>
                <th>Số câu sai</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['result_id'] ?></td>
                    <td><?= $row['exam_name'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['score'] ?></td>
                    <td><?= $row['correct_answers'] ?></td>
                    <td><?= $row['wrong_answers'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Nút quay lại được căn giữa -->
    <div class="button-container">
        <button onclick="window.location.href='../teacher_dashboard.php';">Quay lại</button>
    </div>
</div>
</body>
</html>
