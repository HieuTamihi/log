<?php
include 'db.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$current_user_id = (int)getCurrentUserId();
// 1. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
$stmt = $conn->prepare("
    SELECT l.*, u.username AS creator, s.id AS sid 
    FROM logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    LEFT JOIN solutions s ON l.id = s.log_id 
    WHERE l.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$log = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$log) die("Không tìm thấy vấn đề.");

// KIỂM TRA QUYỀN SỞ HỮU
$is_log_owner = ($current_user_id === (int)$log['user_id']);
$has_solution = !empty($log['sid']);
$freq_raw = $log['frequency'] ?? 'daily';
$frequency_text = match ($freq_raw) {
    'daily'   => 'Hàng ngày',
    'weekly'  => 'Hàng tuần',
    'monthly' => 'Hàng tháng',
    'rare'    => 'Hiếm khi',
    default   => 'Hàng ngày'
};

function formatTimeAgo($datetime)
{
    if (!$datetime) return "không rõ";

    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    // Nếu thời gian ở tương lai hoặc mới tạo dưới 30 giây thì hiện "vừa xong"
    if ($diff < 30) {
        return 'vừa xong';
    }

    $intervals = [
        31536000 => 'năm',
        2592000  => 'tháng',
        604800   => 'tuần',
        86400    => 'ngày',
        3600     => 'giờ',
        60       => 'phút'
    ];

    foreach ($intervals as $secs => $label) {
        $div = $diff / $secs;
        if ($div >= 1) {
            $t = floor($div); // Dùng floor để không bị làm tròn lên (vd 59 giây không thành 1 phút)
            return $t . ' ' . $label . ' trước';
        }
    }

    return floor($diff) . ' giây trước';
}
$db_time = !empty($log['updated_at']) ? $log['updated_at'] : $log['created_at'];
$time_ago = formatTimeAgo($db_time);

// 2. XỬ LÝ XÓA (Bổ sung kiểm tra chủ sở hữu)
if (isset($_POST['delete_log'])) {
    if (!$is_log_owner) {
        $_SESSION['error_message'] = "Bạn không có quyền xóa vấn đề của người khác!";
    } else {
        $del_id = (int)$_POST['delete_log_id'];
        $checkSol = $conn->query("SELECT id FROM solutions WHERE log_id = $del_id");

        if ($checkSol->num_rows > 0) {
            $_SESSION['error_message'] = "Không thể xóa! Vấn đề này đã có giải pháp đính kèm.";
        } else {
            if ($conn->query("DELETE FROM logs WHERE id = $del_id")) {
                $_SESSION['success_message'] = "Đã xóa vấn đề thành công.";
                header("Location: index.php");
                exit();
            }
        }
    }
}

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
    <title><?= htmlspecialchars($log['name']) ?> | Chi tiết vấn đề</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary">← Quay lại danh sách</a>

        <div class="detail-header">
            <span class="log-status-tag <?= $log['status'] === 'open' ? 'new' : 'done' ?>">
                <?= $log['status'] === 'open' ? 'Mới' : 'Hoàn thành' ?>
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
            <p><strong>Lần gần nhất:</strong> <?= $time_ago ?></p>
        </div>

        <div class="card code-block">
            <strong>Mô tả:</strong><br>
            <?= nl2br(htmlspecialchars($log['content'])) ?>
        </div>

        <!-- NÚT HÀNH ĐỘNG -->
        <div class="detail-actions" style="margin-top: 40px; display: flex; gap: 12px; flex-wrap: wrap;">

            <!-- Nút tạo/xem giải pháp -->
            <?php if ($has_solution): ?>
                <a href="solution_detail.php?id=<?= $log['sid'] ?>" class="btn btn-primary">Xem Giải Pháp</a>
            <?php else: ?>
                <a href="create_solution.php?log_id=<?= $id ?>" class="btn btn-primary">Tạo Giải Pháp</a>
            <?php endif; ?>

            <!-- Nút sửa luôn luôn có -->
            <a href="edit_log.php?id=<?= $id ?>" class="btn btn-secondary">Sửa nội dung</a>

            <!-- NÚT XÓA: Chỉ hiển thị nếu CHƯA có giải pháp -->
            <?php if (!$has_solution): ?>
                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vấn đề này?');" style="display:inline;">
                    <input type="hidden" name="delete_log_id" value="<?= $id ?>">
                    <button type="submit" name="delete_log" class="btn btn-danger">Xóa vấn đề</button>
                </form>
            <?php else: ?>
                <div style="width: 100%; color: var(--text-secondary); font-size: 12px; margin-top: 10px;">
                    <i>* Không thể xóa vì vấn đề đã có giải pháp. Hãy xóa giải pháp trước nếu muốn xóa vấn đề này.</i>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <script src="style.js"></script>
</body>

</html>