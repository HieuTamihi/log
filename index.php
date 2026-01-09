<?php
include 'db.php';
requireLogin();  // Nếu bạn đã có hàm này

// Hiển thị tên người dùng (đã đăng nhập)
$username = $_SESSION['username'] ?? 'Người dùng';

// XỬ LÝ THÊM LOG - SỬ DỤNG PRG ĐỂ TRÁNH RESUBMIT
if (isset($_POST['add_log'])) {
    $name = trim($_POST['log_name']);
    $content = trim($_POST['log_content']);
    $version = $_POST['log_version'] ?: '1.0';
    $status = $_POST['log_status'] ?? 'open';
    $user_id = (int) getCurrentUserId();  // Nếu có hệ thống đăng nhập

    if (!empty($name) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO logs (name, content, version, status, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $content, $version, $status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm vấn đề thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi khi thêm: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Vui lòng nhập đầy đủ tên và nội dung!";
    }

    // QUAN TRỌNG: Redirect để tránh resubmit
    header("Location: index.php");
    exit();
}

// === LẤY THÔNG TIN THỐNG KÊ (DASHBOARD) ===
// 1. Tổng số Logged
$resTotal = $conn->query("SELECT COUNT(*) as cnt FROM logs");
$countLogged = $resTotal ? $resTotal->fetch_assoc()['cnt'] : 0;

// 2. Số Recurring (Giả sử là status = 'in_progress' hoặc có logic khác)
$resProg = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'in_progress'");
$countInProgress = $resProg ? $resProg->fetch_assoc()['cnt'] : 0;

// 3. Need Action (status = 'open')
$resOpen = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'open'");
$countNeedAction = $resOpen ? $resOpen->fetch_assoc()['cnt'] : 0;


// Hiển thị thông báo (nếu có) sau redirect
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert error">' . $_SESSION['error_message'] . '</div>';
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
    <script>
        function openTab(evt, tabName) {
            document.querySelectorAll(".tabcontent").forEach(t => t.style.display = "none");
            document.querySelectorAll(".tablink").forEach(t => t.classList.remove("active"));
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="user-info">
            Xin chào <strong><?php echo htmlspecialchars($username); ?></strong> | <a href="logout.php">Đăng xuất</a>
        </div>

        <!-- Stats Dashboard -->
        <!-- Stats Dashboard -->
        <!-- Variables $countLogged, $countInProgress, $countNeedAction are fetched at top of file -->

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo $countLogged; ?></span>
                <span class="stat-label">Logged</span>
            </div>
            <div class="stat-card">
                <span class="stat-number warning"><?php echo $countInProgress; ?></span>
                <span class="stat-label">Recurring</span>
            </div>
            <div class="stat-card">
                <span class="stat-number danger"><?php echo $countNeedAction; ?></span>
                <span class="stat-label">Need Action</span>
            </div>
        </div>

        <!-- Main Center Action -->
        <div class="center-action-container">
            <button onclick="openWizard()" class="hero-btn">
                Thêm vấn đề mới
            </button>
            <p style="margin-top: 15px; color: var(--text-secondary); font-size: 14px;">Bấm để ghi lại vấn đề bạn đang
                gặp phải</p>
        </div>

        <!-- Bottom Sheet Toggle -->
        <div class="bottom-sheet-toggle" id="toggleListBtn">
            <span id="toggleIcon">☰</span> Xem danh sách vấn đề (<?php echo $countLogged; ?>)
        </div>

        <!-- Hidden Logs List -->
        <div id="logsListContainer">
            <!-- Close Button to go back to Dashboard -->
            <button class="close-list-btn" onclick="closeLogsList()" title="Quay lại trang chủ">✕</button>

            <!-- Slider Tabs -->
            <div class="tabs">
                <button class="tablink active" onclick="openTab(event,'all')">Tất cả Logs</button>
                <button class="tablink" onclick="openTab(event,'pending')">Chưa Giải Quyết</button>
                <button class="tablink" onclick="openTab(event,'inprogress')">Solution Đang Làm</button>
                <button class="tablink" onclick="openTab(event,'done')">Solution Hoàn Thành</button>
            </div>

            <?php
            // Lấy dữ liệu có phân trang (tránh load quá nhiều dòng cùng lúc)
            $logs = [];
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            // Tổng số bản ghi để hiển thị paging
            $totalRes = $conn->query("SELECT COUNT(*) AS total FROM logs");
            $total = $totalRes ? (int) $totalRes->fetch_assoc()['total'] : 0;
            $totalPages = max(1, (int) ceil($total / $perPage));

            $query = "SELECT l.*, u.username AS creator, 
                         s.id AS sid, s.status AS s_status, s.user_id AS solution_creator_id,
                         su.username AS solution_creator
                  FROM logs l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  LEFT JOIN solutions s ON l.id = s.log_id 
                  LEFT JOIN users su ON s.user_id = su.id 
                  ORDER BY l.id DESC
                  LIMIT $offset, $perPage";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            ?>

            <!-- Tab Tất cả Logs -->
            <div id="all" class="tabcontent">
                <?php foreach ($logs as $row):
                    $creator = $row['creator'] ?? 'Không rõ';
                    ?>
                    <?php include 'templates/log_item.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Tab Đang Giải Quyết (chưa có solution) -->
            <div id="pending" class="tabcontent">
                <?php foreach ($logs as $row):
                    if ($row['sid'] === null):
                        $creator = $row['creator'] ?? 'Không rõ';
                        ?>
                        <?php include 'templates/log_item.php'; ?>
                    <?php endif;
                endforeach; ?>
            </div>

            <!-- Tab Solution Đang Làm -->
            <div id="inprogress" class="tabcontent">
                <?php foreach ($logs as $row):
                    if ($row['sid'] !== null && $row['s_status'] !== 'done'):
                        $creator = $row['creator'] ?? 'Không rõ';
                        ?>
                        <?php include 'templates/log_item.php'; ?>
                    <?php endif;
                endforeach; ?>
            </div>

            <!-- Tab Solution Hoàn Thành -->
            <div id="done" class="tabcontent">
                <?php foreach ($logs as $row):
                    if ($row['sid'] !== null && $row['s_status'] === 'done'):
                        $creator = $row['creator'] ?? 'Không rõ';
                        ?>
                        <?php include 'templates/log_item.php'; ?>
                    <?php endif;
                endforeach; ?>
            </div>

            <!-- Pagination -->
            <div style="text-align:center; margin:20px 0;">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=<?php echo $page - 1; ?>" class="btn btn-secondary">&lsaquo; Trang trước</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="index.php?page=<?php echo $page + 1; ?>" class="btn">Trang sau &rsaquo;</a>
                <?php endif; ?>
            </div><!-- End Pagination -->
        </div><!-- End #logsListContainer -->

        <!-- Wizard Overlay Thêm Log (OUTSIDE logsListContainer) -->
        <div id="addLogWizard" class="wizard-overlay">
            <div class="wizard-container">
                <form method="POST" id="wizardForm">
                    <!-- Step 1: Nhập liệu cơ bản -->
                    <div class="wizard-step active" id="step1">
                        <input type="text" name="log_name" class="big-input" placeholder="Tên vấn đề..." required
                            autofocus>
                        <textarea name="log_content" class="big-textarea p-2"
                            placeholder="Mô tả chi tiết vấn đề đang gặp phải..." required></textarea>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="closeWizard()">Hủy bỏ</span>
                            <button type="button" class="btn" onclick="nextStep()">Tiếp tục</button>
                        </div>
                    </div>

                    <!-- Step 2: Phân loại & Xác nhận -->
                    <div class="wizard-step" id="step2">
                        <h2 style="margin-bottom: 30px;">Chi tiết bổ sung</h2>

                        <label style="margin-bottom: 15px;">Trạng thái khởi tạo</label>
                        <div class="chip-group" id="statusGroup">
                            <label class="chip-option selected" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="open" checked class="chip-radio"> Mở
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="in_progress" class="chip-radio"> Đang
                                xử lý
                            </label>
                            <label class="chip-option" onclick="selectChip(this)">
                                <input type="radio" name="log_status" value="closed" class="chip-radio"> Đã đóng
                            </label>
                        </div>

                        <label>Phiên bản (Tùy chọn)</label>
                        <input type="text" name="log_version" value="1.0" class="input-field"
                            style="background:transparent; border:1px solid #374151; padding: 12px; color:white; border-radius:12px; margin-bottom: 30px; width: 100px;">

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="prevStep()">Quay lại</span>
                            <button type="submit" name="add_log" class="btn">Hoàn thành Log</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Xem Nội Dung Đầy Đủ -->
        <div id="contentModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeContentModal">&times;</span>
                <h2>Nội Dung Chi Tiết Vấn Đề</h2>
                <pre id="fullContentDisplay"
                    style="background:#f8f9fa; padding:20px; border-radius:8px; max-height:60vh; overflow-y:auto;"></pre>
            </div>
        </div>

        <script>
            function openTab(evt, tabName) {
                document.querySelectorAll(".tabcontent").forEach(t => t.style.display = "none");
                document.querySelectorAll(".tablink").forEach(t => t.classList.remove("active"));
                document.getElementById(tabName).style.display = "block";
                evt.currentTarget.classList.add("active");
            }

            // Wizard Logic with Animations
            const wizard = document.getElementById("addLogWizard");

            function openWizard() {
                wizard.style.display = 'block';
                document.querySelector('input[name="log_name"]').focus();
            }

            function closeWizard() {
                wizard.style.display = 'none';
                // Reset steps
                document.getElementById("step1").className = "wizard-step active";
                document.getElementById("step1").style.display = "block";
                document.getElementById("step2").className = "wizard-step";
                document.getElementById("step2").style.display = "none";
            }

            // Close the logs list overlay and return to dashboard
            function closeLogsList() {
                const logsListContainer = document.getElementById("logsListContainer");
                const toggleIcon = document.getElementById("toggleIcon");
                logsListContainer.classList.remove('show');
                if (toggleIcon) toggleIcon.textContent = '☰';
            }

            function nextStep() {
                const name = document.querySelector('input[name="log_name"]').value;
                const content = document.querySelector('textarea[name="log_content"]').value;
                const step1 = document.getElementById("step1");
                const step2 = document.getElementById("step2");

                if (!name || !content) {
                    alert("Vui lòng nhập tên và nội dung!");
                    return;
                }

                // Animate Step 1 Out
                step1.classList.add("step-exit-left");

                // Wait for animation, then switch
                setTimeout(() => {
                    step1.classList.remove("active", "step-exit-left");
                    step1.style.display = "none";

                    step2.style.display = "block";
                    step2.classList.add("step-enter-right", "active");
                }, 280); // slightly less than 0.3s CSS duration
            }

            function prevStep() {
                const step1 = document.getElementById("step1");
                const step2 = document.getElementById("step2");

                step2.classList.remove("step-enter-right");
                // Can add a reverse exit animation here if desired, 
                // for now just simple switch back
                step2.classList.remove("active");
                step2.style.display = "none";

                step1.style.display = "block";
                step1.classList.add("active"); // Simple fade in or just appear
            }

            // Toggle List Logic
            const toggleListBtn = document.getElementById("toggleListBtn");
            const logsListContainer = document.getElementById("logsListContainer");
            const toggleIcon = document.getElementById("toggleIcon");

            toggleListBtn && toggleListBtn.addEventListener('click', () => {
                const isShowing = logsListContainer.classList.contains('show');

                if (isShowing) {
                    logsListContainer.classList.remove('show');
                    toggleIcon.textContent = '☰';
                    // Optional: Reset scroll position after fade out?
                } else {
                    logsListContainer.classList.add('show');
                    toggleIcon.textContent = '▼';
                }
            });

            function selectChip(el) {
                // Remove selected from all siblings
                el.parentElement.querySelectorAll('.chip-option').forEach(c => c.classList.remove('selected'));
                el.classList.add('selected');
                // Check the radio inside
                el.querySelector('input').checked = true;
            }

            // Removed openWizardBtn listener as we use onclick in HTML now


            // Modal Xem Nội Dung (giữ nguyên)
            const contentModal = document.getElementById("contentModal");
            const fullContentDisplay = document.getElementById("fullContentDisplay");
            const closeContent = contentModal ? contentModal.querySelector('.close') : null;

            // Khi click vào nội dung rút gọn
            document.querySelectorAll('.content-preview').forEach(item => {
                item.addEventListener('click', function () {
                    fullContentDisplay.textContent = this.getAttribute('data-full');
                    contentModal.style.display = 'block';
                });
            });

            closeContent && closeContent.addEventListener('click', () => contentModal.style.display = 'none');

            // Close wizard on outside click? Maybe no, to focus user.
            // window.addEventListener('click', (e) => {
            //    if (e.target === contentModal) contentModal.style.display = 'none';
            // });

            document.addEventListener('DOMContentLoaded', () => {
                const activeBtn = document.querySelector('.tablink.active') || document.querySelector('.tablink');
                if (activeBtn) activeBtn.click();
            });
        </script>
</body>

</html>