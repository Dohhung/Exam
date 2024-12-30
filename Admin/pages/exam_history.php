<?php
include '../../db.php';

// Lấy danh sách kỳ thi
$result = $conn->query("SELECT * FROM exams");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lịch sử kỳ thi</title>
    <link rel="stylesheet" href="../CSS/exam_history.css">
</head>
<body>
<div class="container mt-5">
    <h2>Lịch sử kỳ thi</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên kỳ thi</th>
                <th>Môn học</th>
                <th>Thời gian bắt đầu</th>
                <th>Thời lượng</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['exam_id'] ?></td>
                    <td><?= $row['exam_name'] ?></td>
                    <td><?= $row['subject'] ?></td>
                    <td><?= $row['start_time'] ?></td>
                    <td><?= $row['duration'] ?> phút</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <button onclick="window.location.href='../admin_dashboard.php';">Quay lại</button>
</div>

</body>
</html>
