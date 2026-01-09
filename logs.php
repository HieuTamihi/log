<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Danh sách Logs</h1>
    <table>
        <tr><th>ID</th><th>Name</th><th>Version</th><th>Status</th><th>Actions</th></tr>
        <?php
        $sql = "SELECT * FROM logs";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . (int)$row['id'] . "</td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['version']) . "</td><td>" . htmlspecialchars($row['status']) . "</td><td>";
            // Lấy solutions liên quan
            $sql_sol = "SELECT id, name FROM solutions WHERE log_id = " . (int)$row['id'];
            $res_sol = $conn->query($sql_sol);
            while ($sol = $res_sol->fetch_assoc()) {
                echo "<a href='solution_detail.php?id=" . (int)$sol['id'] . "'>Xem Solution: " . htmlspecialchars($sol['name']) . "</a><br>";
            }
            echo "</td></tr>";
        }
        ?>
    </table>
    <a href="index.php">Quay lại</a>
</body>
</html>