<?php
include 'db.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$current_user_id = (int)getCurrentUserId();

// 1. LẤY DỮ LIỆU HIỆN TẠI & THÔNG TIN VẤN ĐỀ LIÊN QUAN
$stmt = $conn->prepare("
    SELECT S.*, L.name AS log_name, L.content AS log_content 
    FROM solutions S 
    JOIN logs L ON S.log_id = L.id 
    WHERE S.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['error_message'] = "Giải pháp không tồn tại.";
    header("Location: index.php");
    exit();
}

// 2. KIỂM TRA QUYỀN SỞ HỮU
$is_sol_owner = ($current_user_id === (int)$row['user_id']);

// 3. XỬ LÝ CẬP NHẬT (Chỉ chủ sở hữu mới được sửa)
if (isset($_POST['update_solution']) && $is_sol_owner) {
    $new_name = trim($_POST['name']);
    $new_content = trim($_POST['content']);
    $new_version = trim($_POST['version']) ?: $row['version'];
    $new_status = $_POST['status'];

    // Nếu có thay đổi so với bản cũ -> Lưu vào lịch sử trước khi ghi đè
    if ($new_name !== $row['name'] || $new_content !== $row['content'] || $new_status !== $row['status']) {
        $hist = $conn->prepare("INSERT INTO solution_history (solution_id, name, content, version, status) VALUES (?, ?, ?, ?, ?)");
        $hist->bind_param("issss", $id, $row['name'], $row['content'], $row['version'], $row['status']);
        $hist->execute();
        $hist->close();
    }

    // Cập nhật bản ghi hiện tại
    $up = $conn->prepare("UPDATE solutions SET name = ?, content = ?, version = ?, status = ? WHERE id = ?");
    $up->bind_param("ssssi", $new_name, $new_content, $new_version, $new_status, $id);

    if ($up->execute()) {
        $_SESSION['msg'] = "Cập nhật giải pháp thành công!";
    } else {
        $_SESSION['error_message'] = "Lỗi khi cập nhật giải pháp.";
    }
    $up->close();
    header("Location: solution_detail.php?id=$id");
    exit();
}

// 4. XỬ LÝ XÓA
if (isset($_POST['delete_solution']) && $is_sol_owner) {
    // Xóa lịch sử trước (nếu DB không để Cascade)
    $conn->query("DELETE FROM solution_history WHERE solution_id = $id");
    if ($conn->query("DELETE FROM solutions WHERE id = $id")) {
        $_SESSION['success_message'] = "Đã xóa giải pháp vĩnh viễn.";
        header("Location: index.php");
    } else {
        $_SESSION['error_message'] = "Không thể xóa giải pháp này.";
        header("Location: solution_detail.php?id=$id");
    }
    exit();
}

// 5. CHUẨN BỊ THÔNG BÁO TOAST
$toast_msg = '';
$toast_type = 'info';
if (isset($_SESSION['success_message'])) {
    $toast_msg = $_SESSION['success_message'];
    $toast_type = 'success';
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $toast_msg = $_SESSION['error_message'];
    $toast_type = 'error';
    unset($_SESSION['error_message']);
} elseif (isset($_SESSION['msg'])) {
    $toast_msg = $_SESSION['msg'];
    $toast_type = 'success';
    unset($_SESSION['msg']);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($row['name']) ?> | Chi tiết</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="padding-bottom: 100px;">
        <div style="margin-top: 20px;">
            <a href="index.php" class="wizard-back">← Quay lại danh sách</a>
        </div>

        <div class="detail-header">
            <span class="log-status-tag <?= $row['status'] === 'done' ? 'done' : 'in-progress' ?>">
                <?= $row['status'] === 'done' ? 'Hoàn thành' : ($row['status'] === 'testing' ? 'Đang kiểm tra' : 'Bản nháp') ?>
            </span>
            <h1><?= htmlspecialchars($row['log_name']) ?></h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">Giải pháp: <strong><?= htmlspecialchars($row['name']) ?></strong> (v<?= htmlspecialchars($row['version']) ?>)</p>
        </div>

        <!-- Nội dung chính -->
        <div class="card" style="margin-bottom: 24px;">
            <h2 style="font-size: 14px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 12px;">Vấn Đề Gốc</h2>
            <div class="code-block" style="margin-bottom: 24px; background: rgba(0,0,0,0.2);">
                <?= nl2br(htmlspecialchars($row['log_content'])) ?>
            </div>

            <h2 style="font-size: 14px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 12px;">Chi Tiết Giải Pháp</h2>
            <div class="code-block" style="border-left: 3px solid var(--accent-color);">
                <?= nl2br(htmlspecialchars($row['content'])) ?>
            </div>

            <?php if ($is_sol_owner): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <button id="openUpdateModal" class="btn btn-primary" style="width: 100%; max-width: 300px;">
                        Cập Nhật Giải Pháp
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lịch sử thay đổi -->
        <div class="card">
            <h2 style="font-size: 16px; margin-bottom: 16px;">Lịch sử thay đổi</h2>
            <div style="overflow-x: auto;">
                <table style="min-width: 500px;">
                    <thead>
                        <tr>
                            <th>Phiên Bản</th>
                            <th>Trạng Thái</th>
                            <th>Nội dung cũ</th>
                            <th>Ngày sửa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $hist_stmt = $conn->prepare("SELECT * FROM solution_history WHERE solution_id = ? ORDER BY changed_at DESC LIMIT 3");
                        $hist_stmt->bind_param("i", $id);
                        $hist_stmt->execute();
                        $resHist = $hist_stmt->get_result();

                        if ($resHist->num_rows == 0): ?>
                            <tr>
                                <td colspan='4' style="text-align:center; color: var(--text-secondary);">Chưa có lịch sử chỉnh sửa.</td>
                            </tr>
                            <?php else:
                            while ($h = $resHist->fetch_assoc()):
                                $stt_text = $h['status'] === 'done' ? 'Hoàn thành' : ($h['status'] === 'testing' ? 'Đang kiểm tra' : 'Nháp');
                            ?>
                                <tr>
                                    <td>v<?= htmlspecialchars($h['version']) ?></td>
                                    <td><?= $stt_text ?></td>
                                    <td style="font-size: 12px; color: var(--text-secondary);"><?= mb_strimwidth(htmlspecialchars($h['content']), 0, 50, "...") ?></td>
                                    <td style="font-size: 12px;"><?= date('d/m H:i', strtotime($h['changed_at'])) ?></td>
                                </tr>
                        <?php endwhile;
                        endif;
                        $hist_stmt->close(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hành động nguy hiểm -->
        <?php if ($is_sol_owner): ?>
            <div class="detail-actions" style="margin-top: 40px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                <p style="font-size: 12px; color: var(--error); margin-bottom: 12px;">Vùng nguy hiểm:</p>
                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn giải pháp này?');">
                    <button type="submit" name="delete_solution" class="btn btn-danger" style="width: 100%;">Xóa giải pháp</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Cập Nhật (Chỉ render nếu là chủ sở hữu) -->
    <?php if ($is_sol_owner): ?>
        <div id="updateModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <span class="close">&times;</span>
                <h2 style="margin-bottom: 20px;">Cập Nhật Giải Pháp</h2>
                <form method="POST">
                    <label>Tên giải pháp</label>
                    <input type="text" name="name" class="big-input" style="font-size: 16px; padding: 12px;" value="<?= htmlspecialchars($row['name']) ?>" required>

                    <label>Nội dung hướng dẫn</label>
                    <textarea name="content" class="big-textarea" style="font-size: 15px; min-height: 200px;" required><?= htmlspecialchars($row['content']) ?></textarea>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 10px;">
                        <div>
                            <label>Phiên bản</label>
                            <input type="text" name="version" class="big-input" style="font-size: 16px; padding: 12px;" value="<?= htmlspecialchars($row['version']) ?>">
                        </div>
                        <div>
                            <label>Trạng thái</label>
                            <select name="status" class="big-input" style="font-size: 16px; padding: 12px;">
                                <option value="draft" <?= $row['status'] == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                <option value="testing" <?= $row['status'] == 'testing' ? 'selected' : '' ?>>Đang kiểm tra</option>
                                <option value="done" <?= $row['status'] == 'done' ? 'selected' : '' ?>>Hoàn thành</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 24px; display: flex; flex-direction: column; gap: 10px;">
                        <button type="submit" name="update_solution" class="btn btn-primary" style="width: 100%;">Lưu Thay Đổi</button>
                        <button type="button" id="cancelUpdate" class="btn btn-secondary" style="width: 100%;">Hủy bỏ</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script src="style.js"></script>
    <script>
        // Kích hoạt Toast từ Server
        <?php if ($toast_msg): ?>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof showToast === "function") {
                    showToast("<?= addslashes($toast_msg) ?>", "<?= $toast_type ?>");
                }
            });
        <?php endif; ?>

        // Xử lý Modal
        const modal = document.getElementById("updateModal");
        const openBtn = document.getElementById("openUpdateModal");
        const closeBtn = document.querySelector(".close");
        const cancelBtn = document.getElementById("cancelUpdate");

        if (openBtn) {
            openBtn.onclick = () => {
                modal.style.display = "block";
                setTimeout(() => modal.classList.add("show"), 10);
            };
        }

        const closeModal = () => {
            modal.classList.remove("show");
            setTimeout(() => modal.style.display = "none", 300);
        };

        if (closeBtn) closeBtn.onclick = closeModal;
        if (cancelBtn) cancelBtn.onclick = closeModal;
        window.onclick = (e) => {
            if (e.target === modal) closeModal();
        };
    </script>
</body>

</html>