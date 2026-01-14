<?php
include 'db.php';
requireLogin();

// Tên người dùng
$username = $_SESSION['username'] ?? 'Người dùng';

// Xử lý thêm log mới
if (isset($_POST['add_log'])) {
    $user_id = (int) getCurrentUserId();
    $repeat  = $_POST['repeat'] ?? null;

    // Khởi tạo các biến mặc định
    $name      = trim($_POST['log_name'] ?? '');
    $content   = trim($_POST['log_content'] ?? '');
    $version   = '1.0';
    $status    = 'open';
    $frequency = $_POST['frequency'] ?? 'daily';
    $emotion   = $_POST['emotion'] ?? 'binh-thuong';

    // TRƯỜNG HỢP: Giống lần trước -> Lấy dữ liệu từ bản ghi gần nhất
    if ($repeat === 'same') {
        $lastStmt = $conn->prepare("
            SELECT name, content, version, status, frequency, emotion 
            FROM logs 
            WHERE user_id = ? 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $lastStmt->bind_param("i", $user_id);
        $lastStmt->execute();
        $lastLog = $lastStmt->get_result()->fetch_assoc();
        $lastStmt->close();

        if ($lastLog) {
            // Ghi đè dữ liệu cũ vào các biến để chuẩn bị Insert bản ghi mới
            $name      = $lastLog['name'];
            $content   = $lastLog['content'];
            $version   = $lastLog['version'];
            $status    = $lastLog['status'];
            $frequency = $lastLog['frequency'];
            $emotion   = $lastLog['emotion'];

            // Cập nhật số lần lặp lại cho bản ghi GỐC (Tùy chọn)
            // $conn->query("UPDATE logs SET repeat_count = repeat_count + 1 WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
        } else {
            $_SESSION['error_message'] = "Bạn chưa có vấn đề nào trước đó để sao chép!";
            header("Location: index.php");
            exit();
        }
    }

    // Thực hiện INSERT bản ghi mới (Dù là tạo mới hoàn toàn hay Copy từ cái cũ)
    if ($name && $content) {
        $stmt = $conn->prepare("
            INSERT INTO logs 
            (name, content, version, status, user_id, repeat_status, frequency, emotion, repeat_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        // repeat_count mặc định là 1 cho bản ghi mới này
        $stmt->bind_param("ssssisss", $name, $content, $version, $status, $user_id, $repeat, $frequency, $emotion);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = ($repeat === 'same') ? "Đã ghi nhận lặp lại vấn đề trước đó!" : "Thêm vấn đề thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi hệ thống: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Vui lòng nhập đầy đủ thông tin!";
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

// Xuất biến JS chứa nội dung đầy đủ
echo '<script id="log-contents">';
echo 'const logContents = {';
foreach ($logs as $row) {
    $id = (int)$row['id'];
    $content = json_encode($row['content'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "$id: $content,";
}
echo '};';
echo '</script>';

// Thông báo
$alert = '';
if (isset($_SESSION['success_message'])) {
    $alert = '<div class="alert success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $alert = '<div class="alert error">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
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
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Log & Solution</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Fluency Log">
    <link rel="apple-touch-icon" href="icon-192.png">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <!-- User Info -->
        <div class="user-info">
            Xin chào <strong><?= htmlspecialchars($username) ?></strong> | <a href="logout.php">Đăng xuất</a>
        </div>

        <?= $alert ?>

        <!-- Stats -->
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

        <!-- Nút thêm -->
        <div class="center-action-container">
            <button onclick="openWizard()" class="hero-btn">Thêm vấn đề mới</button>
            <p>Bấm để ghi lại vấn đề bạn đang gặp phải</p>
        </div>

        <!-- Toggle danh sách -->
        <div class="bottom-sheet-toggle" id="toggleListBtn">
            <span id="toggleIcon">☰</span> Xem danh sách vấn đề (<?= $countLogged ?>)
        </div>

        <!-- Danh sách vấn đề -->
        <div id="logsListContainer" class="logs-modal">
            <div class="logs-header">
                <h2>Các Vấn Đề Lặp Lại</h2>
                <button class="close-list-btn" title="Đóng">Đóng</button>
            </div>

            <div class="logs-list">
                <?php foreach ($logs as $row):
                    $status_class = $row['status'] === 'open' ? 'new' : ($row['status'] === 'in_progress' ? 'in-progress' : 'done');
                    $status_text  = $row['status'] === 'open' ? 'Mới' : ($row['status'] === 'in_progress' ? 'Đang xử lý' : 'Hoàn thành');

                    $short_name = mb_strlen($row['name']) > 60 ? mb_substr($row['name'], 0, 57, 'UTF-8') . '...' : $row['name'];

                    $freq_raw = $row['frequency'] ?? 'daily';
                    $frequency_text = match ($freq_raw) {
                        'daily' => 'Hàng ngày',
                        'weekly' => 'Hàng tuần',
                        'monthly' => 'Hàng tháng',
                        'rare' => 'Hiếm khi',
                        default => 'Hàng ngày'
                    };

                    $db_time = !empty($row['updated_at']) ? $row['updated_at'] : $row['created_at'];
                    $time_ago = formatTimeAgo($db_time);
                    $repeat_count = $row['repeat_count'] ?? 1;
                    $has_solution = !empty($row['sid']);
                ?>
                    <div class="log-card">
                        <div class="log-left">
                            <div class="log-status-tag <?= $status_class ?>"><?= $status_text ?></div>
                            <a href="log_detail.php?id=<?= (int)$row['id'] ?>" class="log-title">
                                <?= htmlspecialchars($short_name) ?>
                            </a>
                            <div class="log-meta">
                                <span class="log-frequency"><?= $repeat_count ?>× <?= $frequency_text ?></span>
                                <!-- Hiển thị thời gian đã được format chuẩn -->
                                <span class="log-time"><?= $time_ago ?></span>
                            </div>
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
                    <!-- Bước 1: Có lặp lại không? -->
                    <div class="wizard-step active" id="step1">
                        <h2>Vấn đề này có lặp lại không?</h2>
                        <div class="chip-group">
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="repeat" value="same"> Giống lần trước
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="repeat" value="yes"> Có
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="repeat" value="no"> Không
                            </label>
                        </div>
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="closeWizard()">Hủy</span>
                            <button type="button" class="btn" onclick="handleNext()">Tiếp tục</button>
                        </div>
                    </div>

                    <!-- Bước 2: Tần suất xảy ra -->
                    <div class="wizard-step" id="step2">
                        <h2>Vấn đề xảy ra bao lâu một lần?</h2>
                        <div class="chip-group">
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="frequency" value="daily"> Hàng ngày
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="frequency" value="weekly"> Hàng tuần
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="frequency" value="monthly"> Hàng tháng
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="frequency" value="rare"> Hiếm khi
                            </label>
                        </div>
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="prevStep(1)">Quay lại</span>
                            <button type="button" class="btn" onclick="nextStep(3)">Tiếp tục</button>
                        </div>
                    </div>

                    <!-- Bước 3: Mức độ cảm xúc -->
                    <div class="wizard-step" id="step3">
                        <h2>Khi vấn đề lặp lại, bạn cảm thấy thế nào?</h2>
                        <div class="chip-group emotion-chips">
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="emotion" value="rat-tot"> Rất tốt
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="emotion" value="tot"> Tốt
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="emotion" value="binh-thuong"> Bình thường
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="emotion" value="tuc"> Tức
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="emotion" value="rat-tuc"> Rất tức
                            </label>
                        </div>
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="prevStep(2)">Quay lại</span>
                            <button type="button" class="btn" onclick="nextStep(4)">Tiếp tục</button>
                        </div>
                    </div>

                    <!-- Bước 4: Tên & Mô tả -->
                    <div class="wizard-step" id="step4">
                        <input type="text" name="log_name" class="big-input" placeholder="Tên vấn đề..." required autofocus>
                        <textarea name="log_content" class="big-textarea" placeholder="Mô tả chi tiết vấn đề..." required></textarea>
                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="prevStep(3)">Quay lại</span>
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
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('Service Worker đã sẵn sàng!', reg))
                    .catch(err => console.log('Lỗi đăng ký SW', err));
            });
        }

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
        function openWizard() {
            document.getElementById('addLogWizard').style.display = 'block';
        }

        function closeWizard() {
            document.getElementById('addLogWizard').style.display = 'none';
        }

        function handleNext() {
            const selected = document.querySelector('input[name="repeat"]:checked');
            if (!selected) {
                alert("Vui lòng chọn một lựa chọn!");
                return;
            }

            if (selected.value === 'same') {
                // Kích hoạt gửi form ngay lập tức
                // Lúc này PHP sẽ nhận được $_POST['repeat'] = 'same' và tự thực hiện logic copy
                const form = document.getElementById('wizardForm');

                // Tạo thêm input ẩn add_log để PHP nhận biết là hành động submit
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'add_log';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                form.submit();
            } else {
                // Chuyển sang bước tiếp theo nếu chọn "Có" hoặc "Không"
                nextStep(2);
            }
        }

        function nextStep(step) {
            document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
            document.getElementById(`step${step}`).classList.add('active');
        }

        function prevStep(step) {
            nextStep(step);
        }

        function selectChip(el) {
            el.parentElement.querySelectorAll('.chip-option').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            el.querySelector('input').checked = true;
        }
    </script>
</body>

</html>