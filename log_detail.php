<?php
include 'db.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$log) die("Không tìm thấy vấn đề.");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($log['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary">← Back</a>

        <div class="detail-header">
            <span class="status-tag new">New</span>
            <h1><?php echo htmlspecialchars($log['name']); ?></h1>
        </div>

        <div class="detail-stats">
            <div class="stat-item">
                <strong>1×</strong>
                <span>Times logged</span>
            </div>
            <div class="stat-item">
                <strong>Daily</strong>
                <span>Frequency</span>
            </div>
        </div>

        <div class="detail-timeline">
            <h3>TIMELINE</h3>
            <p><strong>First logged:</strong> Jan 12, 2026</p>
            <p><strong>Last occurrence:</strong> about 2 hours ago</p>
        </div>

        <!-- Nội dung đầy đủ -->
        <div class="code-block">
            <?php echo htmlspecialchars($log['content']); ?>
        </div>
    </div>
</body>
</html>