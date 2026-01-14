<?php
include 'db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) header("Location: index.php");
$current_user_id = (int)getCurrentUserId();

$sol = $conn->query("SELECT * FROM solutions WHERE id = $id")->fetch_assoc();

if (!$sol || (int)$sol['user_id'] !== $current_user_id) {
    $_SESSION['error_message'] = "Bạn không có quyền sửa giải pháp này!";
    header("Location: index.php");
    exit();
}

// Xử lý cập nhật
if (isset($_POST['update_sol'])) {
    $content = trim($_POST['content']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE solutions SET content = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $content, $status, $id);
    if ($stmt->execute()) {
        header("Location: solution_detail.php?id=$id");
        exit();
    }
}

// Lấy dữ liệu cũ
$sol = $conn->query("SELECT * FROM solutions WHERE id = $id")->fetch_assoc();
if (!$sol) die("Không tìm thấy giải pháp.");
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
    <title>Sửa Giải Pháp</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="form-page">
    <div class="form-wrapper">
        <div class="form-card">
            <a href="solution_detail.php?id=<?= $id ?>" class="wizard-back" style="margin-bottom: 20px; display: inline-block;">← Quay lại</a>
            <h1>Chỉnh sửa giải pháp</h1>

            <form method="POST">
                <label>Nội dung giải pháp mới</label>
                <textarea name="content" class="big-textarea" rows="12" required><?= htmlspecialchars($sol['content']) ?></textarea>

                <label>Trạng thái hiện tại</label>
                <select name="status" style="margin-bottom: 30px;">
                    <option value="active" <?= $sol['status'] == 'active' ? 'selected' : '' ?>>Hiệu quả</option>
                    <option value="testing" <?= $sol['status'] == 'testing' ? 'selected' : '' ?>>Đang thử nghiệm</option>
                    <option value="archived" <?= $sol['status'] == 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                </select>

                <div class="form-actions">
                    <button type="submit" name="update_sol" class="btn btn-primary">Cập nhật ngay</button>
                    <a href="solution_detail.php?id=<?= $id ?>" class="btn btn-secondary" style="text-align:center">Hủy</a>
                </div>
            </form>
        </div>
    </div>
    <script src="style.js"></script>
</body>

</html>