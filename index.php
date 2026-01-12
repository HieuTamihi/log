<?php
include 'db.php';
requireLogin();

// Tên người dùng
$username = $_SESSION['username'] ?? 'Người dùng';

// Xử lý thêm log mới
if (isset($_POST['add_log'])) {
    $name    = trim($_POST['log_name']);
    $content = trim($_POST['log_content']);
    $version = $_POST['log_version'] ?: '1.0';
    $status  = $_POST['log_status'] ?? 'open';
    $user_id = (int) getCurrentUserId();

    if ($name && $content) {
        $stmt = $conn->prepare("INSERT INTO logs (name, content, version, status, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $content, $version, $status, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm vấn đề thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Vui lòng nhập đầy đủ tên và nội dung!";
    }
    header("Location: index.php");
    exit();
}

// Thống kê
$countLogged     = $conn->query("SELECT COUNT(*) as cnt FROM logs")->fetch_assoc()['cnt'] ?? 0;
$countInProgress = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'in_progress'")->fetch_assoc()['cnt'] ?? 0;
$countNeedAction = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'open'")->fetch_assoc()['cnt'] ?? 0;

// Lấy danh sách logs (phân trang)
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;
$offset   = ($page - 1) * $perPage;
$total    = $conn->query("SELECT COUNT(*) AS total FROM logs")->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, (int)ceil($total / $perPage));

$query = "SELECT l.*, u.username AS creator, 
                 s.id AS sid, s.status AS s_status, s.user_id AS solution_creator_id,
                 su.username AS solution_creator
          FROM logs l 
          LEFT JOIN users u ON l.user_id = u.id 
          LEFT JOIN solutions s ON l.id = s.log_id 
          LEFT JOIN users su ON s.user_id = su.id 
          ORDER BY l.id DESC LIMIT $offset, $perPage";

$result = $conn->query($query);
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Xuất biến JS chứa nội dung đầy đủ (cho modal xem chi tiết)
echo '<script id="log-contents">';
echo 'const logContents = {';
foreach ($logs as $row) {
    $id = (int)$row['id'];
    $content = json_encode($row['content'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "$id: $content,";
}
echo '};';
echo '</script>';

// Thông báo sau redirect
$alert = '';
if (isset($_SESSION['success_message'])) {
    $alert = '<div class="alert success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $alert = '<div class="alert error">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Log & Solution</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- User Info -->
        <div class="user-info">
            Xin chào <strong><?= htmlspecialchars($username) ?></strong> | 
            <a href="logout.php">Đăng xuất</a>
        </div>

        <?= $alert ?>

        <!-- Stats Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $countLogged ?></span>
                <span class="stat-label">Logged</span>
            </div>
            <div class="stat-card">
                <span class="stat-number warning"><?= $countInProgress ?></span>
                <span class="stat-label">Đang xử lý</span>
            </div>
            <div class="stat-card">
                <span class="stat-number danger"><?= $countNeedAction ?></span>
                <span class="stat-label">Cần xử lý</span>
            </div>
        </div>

        <!-- Nút thêm vấn đề mới -->
        <div class="center-action-container">
            <button onclick="openWizard()" class="hero-btn">Thêm vấn đề mới</button>
            <p>Bấm để ghi lại vấn đề bạn đang gặp phải</p>
        </div>

        <!-- Toggle danh sách -->
        <div class="bottom-sheet-toggle" id="toggleListBtn">
            <span id="toggleIcon">☰</span> Xem danh sách vấn đề (<?= $countLogged ?>)
        </div>

        <!-- Danh sách vấn đề - Lovable style -->
        <div id="logsListContainer" class="logs-modal">
            <div class="logs-header">
                <h2>Các Vấn Đề Lặp Lại</h2>
                <span class="logs-count"><?= count($logs) ?> vấn đề</span>
                <button class="close-list-btn" title="Đóng">Đóng</button>
            </div>

            <div class="logs-list">
                <?php foreach ($logs as $row):
                    $status_class = $row['status'] === 'open' ? 'new' : ($row['status'] === 'in_progress' ? 'in-progress' : 'done');
                    $status_text  = $row['status'] === 'open' ? 'Mới' : ($row['status'] === 'in_progress' ? 'Đang xử lý' : 'Hoàn thành');
                    $short_name   = mb_strlen($row['name']) > 60 ? mb_substr($row['name'], 0, 57) . '...' : $row['name'];
                    $has_solution = !empty($row['sid']);
                ?>
                    <div class="log-card">
                        <div class="log-status-tag <?= $status_class ?>"><?= $status_text ?></div>
                        <div class="log-title">
                            <a href="log_detail.php?id=<?= (int)$row['id'] ?>">
                                <?= htmlspecialchars($short_name) ?>
                            </a>
                        </div>
                        <div class="log-meta">
                            <span class="log-frequency">1× Hàng ngày</span>
                            <span class="log-time">khoảng 2 giờ trước</span>
                        </div>
                        <div class="log-actions">
                            <?php if ($has_solution): ?>
                                <a href="solution_detail.php?id=<?= (int)$row['sid'] ?>" class="btn btn-small">Xem Giải Pháp</a>
                            <?php else: ?>
                                <a href="add_solution.php?log_id=<?= (int)$row['id'] ?>" class="btn btn-small btn-primary">Tạo Giải Pháp</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($logs)): ?>
                    <div class="empty-state">Hiện chưa có vấn đề nào được ghi nhận.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Wizard thêm log -->
        <div id="addLogWizard" class="wizard-overlay">
            <div class="wizard-container">
                <form method="POST" id="wizardForm">
                    <div class="wizard-step active" id="step1">
                        <input type="text" name="log_name" class="big-input" placeholder="Tên vấn đề..." required autofocus>
                        <textarea name="log_content" class="big-textarea" placeholder="Mô tả chi tiết..." required></textarea>
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="closeWizard()">Hủy</span>
                            <button type="button" class="btn" onclick="nextStep()">Tiếp tục</button>
                        </div>
                    </div>

                    <div class="wizard-step" id="step2">
                        <h2>Chi tiết bổ sung</h2>
                        <label>Trạng thái</label>
                        <div class="chip-group">
                            <label class="chip-option selected" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="open" checked> Mở
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="in_progress"> Đang xử lý
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="done"> Hoàn thành
                            </label>
                        </div>
                        <label>Phiên bản</label>
                        <input type="text" name="log_version" value="1.0">
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="prevStep()">Quay lại</span>
                            <button type="submit" name="add_log" class="btn">Hoàn thành</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal xem nội dung đầy đủ -->
        <div id="contentModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeContentModal">&times;</span>
                <h2>Nội dung chi tiết</h2>
                <div class="content-wrapper">
                    <div id="fullContentDisplay" class="content-text"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('toggleListBtn');
        const container = document.getElementById('logsListContainer');
        const icon = document.getElementById('toggleIcon');

        // Toggle danh sách
        toggleBtn?.addEventListener('click', () => {
            container.classList.toggle('show');
            icon.textContent = container.classList.contains('show') ? '▼' : '☰';
        });

        document.querySelector('.close-list-btn')?.addEventListener('click', () => {
            container.classList.remove('show');
            icon.textContent = '☰';
        });

        // Modal nội dung
        document.querySelectorAll('.content-preview').forEach(item => {
            item.addEventListener('click', () => {
                const id = parseInt(item.dataset.logId);
                const modal = document.getElementById('contentModal');
                const display = document.getElementById('fullContentDisplay');

                display.textContent = (logContents && logContents[id]) ? logContents[id] : 'Không tìm thấy nội dung.';
                modal.style.display = 'block';
                requestAnimationFrame(() => modal.classList.add('show'));
            });
        });

        document.getElementById('closeContentModal')?.addEventListener('click', () => {
            const modal = document.getElementById('contentModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('fullContentDisplay').textContent = '';
            }, 180);
        });

        window.addEventListener('click', e => {
            if (e.target.id === 'contentModal') {
                const modal = document.getElementById('contentModal');
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 180);
            }
        });
    });

    // Wizard functions
    function openWizard() { document.getElementById('addLogWizard').style.display = 'block'; }
    function closeWizard() { document.getElementById('addLogWizard').style.display = 'none'; }
    function nextStep() { /* logic wizard */ }
    function prevStep() { /* logic wizard */ }
    function selectChip(el) { /* logic chip */ }
    </script>
</body>
</html>