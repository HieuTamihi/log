<?php
include 'db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) header("Location: index.php");
$current_user_id = (int)getCurrentUserId();
$log = $conn->query("SELECT * FROM logs WHERE id = $id")->fetch_assoc();
if (!$log || (int)$log['user_id'] !== $current_user_id) {
    $_SESSION['error_message'] = "Bạn không có quyền sửa vấn đề này!";
    header("Location: index.php");
    exit();
}
// Xử lý cập nhật
if (isset($_POST['update_log'])) {
    $name = trim($_POST['name']);
    $content = trim($_POST['content']);
    $freq = $_POST['frequency'];

    $stmt = $conn->prepare("UPDATE logs SET name = ?, content = ?, frequency = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $content, $freq, $id);
    if ($stmt->execute()) {
        header("Location: log_detail.php?id=$id");
        exit();
    }
}

// Lấy dữ liệu cũ
$log = $conn->query("SELECT * FROM logs WHERE id = $id")->fetch_assoc();
if (!$log) die("Không tìm thấy vấn đề.");

$msg = '';
$type = 'info';

if (isset($_SESSION['success_message'])) {
    $msg = $_SESSION['success_message'];
    $type = 'success';
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $msg = $_SESSION['error_message'];
    $type = 'error';
    unset($_SESSION['error_message']);
} elseif (isset($_SESSION['msg'])) { // Dành cho trang solution_detail
    $msg = $_SESSION['msg'];
    $type = 'success';
    unset($_SESSION['msg']);
}

// Nếu có tin nhắn, in ra script để gọi Toast
if ($msg): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            showToast("<?= addslashes($msg) ?>", "<?= $type ?>");
        });
    </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Vấn Đề</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="form-page">
    <div class="form-wrapper">
        <div class="form-card">
            <a href="log_detail.php?id=<?= $id ?>" class="wizard-back" style="margin-bottom: 20px; display: inline-block;">← Quay lại</a>
            <h1>Chỉnh sửa vấn đề</h1>

            <form method="POST">
                <label>Tên vấn đề ngắn gọn</label>
                <input type="text" name="name" class="big-input" value="<?= htmlspecialchars($log['name']) ?>" required>

                <label>Mô tả chi tiết</label>
                <textarea name="content" class="big-textarea" rows="8" required><?= htmlspecialchars($log['content']) ?></textarea>

                <label>Tần suất lặp lại</label>
                <select name="frequency" style="margin-bottom: 30px;">
                    <option value="daily" <?= $log['frequency'] == 'daily' ? 'selected' : '' ?>>Hàng ngày</option>
                    <option value="weekly" <?= $log['frequency'] == 'weekly' ? 'selected' : '' ?>>Hàng tuần</option>
                    <option value="monthly" <?= $log['frequency'] == 'monthly' ? 'selected' : '' ?>>Hàng tháng</option>
                    <option value="rare" <?= $log['frequency'] == 'rare' ? 'selected' : '' ?>>Hiếm khi</option>
                </select>

                <div class="form-actions">
                    <button type="submit" name="update_log" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="log_detail.php?id=<?= $id ?>" class="btn btn-secondary" style="text-align:center">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
    <script src="style.js"></script>
</body>

</html>