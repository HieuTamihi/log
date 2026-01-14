<?php
include 'db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT l.*, u.username AS creator FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
$stmt->close();
$freq_raw = $log['frequency'] ?? 'daily';
$frequency_text = match ($freq_raw) {
    'daily'   => 'Hàng ngày',
    'weekly'  => 'Hàng tuần',
    'monthly' => 'Hàng tháng',
    'rare'    => 'Hiếm khi',
    default   => 'Hàng ngày'
};
if (!$log) die("Không tìm thấy vấn đề.");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($log['name']) ?> | Quản Lý Log</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary">← Quay lại danh sách</a>

        <div class="detail-header">
            <span class="log-status-tag <?= $log['status'] === 'open' ? 'new' : ($log['status'] === 'in_progress' ? 'in-progress' : 'done') ?>">
                <?= $log['status'] === 'open' ? 'Mới' : ($log['status'] === 'in_progress' ? 'Đang xử lý' : 'Hoàn thành') ?>
            </span>
            <h1><?= htmlspecialchars($log['name']) ?></h1>
        </div>

        <div class="detail-stats">
            <div class="stat-item">
                <strong>1×</strong>
                <span>Lần ghi nhận</span>
            </div>
            <div class="stat-item">
                <strong><?= $frequency_text ?></strong>
                <span>Tần suất</span>
            </div>
        </div>

        <div class="detail-timeline">
            <h3>DÒNG THỜI GIAN</h3>
            <p><strong>Lần đầu ghi nhận:</strong> <?= date('d/m/Y', strtotime($log['created_at'] ?? 'now')) ?></p>
            <p><strong>Lần gần nhất:</strong> khoảng 2 giờ trước</p>
        </div>

        <div class="code-block">
            <strong>Mô tả chi tiết:</strong><br>
            <?= nl2br(htmlspecialchars($log['content'])) ?>
        </div>

        <!-- Nút hành động -->
        <?php if (!empty($log['sid'])): ?>
            <a href="solution_detail.php?id=<?= (int)$log['sid'] ?>" class="btn btn-primary" style="margin-top: 24px;">Xem Giải Pháp</a>
        <?php else: ?>
            <a href="add_solution.php?log_id=<?= (int)$log['id'] ?>" class="btn btn-primary" style="margin-top: 24px;">Tạo Giải Pháp</a>
        <?php endif; ?>
    </div>
</body>

</html>