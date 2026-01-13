<?php
include 'db.php';

// Ngăn chặn trình duyệt cache trang index.php này
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
requireLogin();  // Nếu bạn đã có hàm này

// Hiển thị tên người dùng (đã đăng nhập)
$username = $_SESSION['username'] ?? 'Người dùng';

// XỬ LÝ THÊM LOG - SỬ DỤNG PRG ĐỂ TRÁNH RESUBMIT
if (isset($_POST['add_log'])) {
    $name = trim($_POST['log_name'] ?? 'Vấn đề mới');
    $content = trim($_POST['log_content'] ?? '');
    $version = $_POST['log_version'] ?: '1.0';
    $status = $_POST['log_status'] ?? 'open';
    $emotion = $_POST['emotion_level'] ?? '';
    $user_id = (int) getCurrentUserId();

    // Content is required (description)
    if (!empty($content)) {
        // Add emotion info to content if provided
        if (!empty($emotion)) {
            $emotionLabels = [
                'frustrated' => 'Rất khó chịu',
                'annoyed' => 'Hơi khó chịu',
                'neutral' => 'Bình thường'
            ];
            $emotionLabel = $emotionLabels[$emotion] ?? $emotion;
            $content = "[" . $emotionLabel . "] " . $content;
        }

        // Use content as name if name is default
        if ($name === 'Vấn đề mới' && strlen($content) > 0) {
            // Take first 50 chars of content as name
            $name = mb_substr(strip_tags($content), 0, 50);
            if (strlen($content) > 50)
                $name .= '...';
        }

        $stmt = $conn->prepare("INSERT INTO logs (name, content, version, status, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $content, $version, $status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Đã lưu vấn đề!";
        } else {
            $_SESSION['error_message'] = "Lỗi khi thêm: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Vui lòng nhập mô tả vấn đề!";
    }

    // QUAN TRỌNG: Redirect để tránh resubmit
    header("Location: index.php");
    exit();
}

// === LẤY THÔNG TIN THỐNG KÊ (DASHBOARD) ===
// 1. Tổng số Logged
$resTotal = $conn->query("SELECT COUNT(*) as cnt FROM logs");
$countLogged = $resTotal ? $resTotal->fetch_assoc()['cnt'] : 0;

// 2. Số Recurring (status = 'in_progress')
$resProg = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'in_progress'");
$countInProgress = $resProg ? $resProg->fetch_assoc()['cnt'] : 0;

// 3. Need Action (status = 'open')
$resOpen = $conn->query("SELECT COUNT(*) as cnt FROM logs WHERE status = 'open'");
$countNeedAction = $resOpen ? $resOpen->fetch_assoc()['cnt'] : 0;

// 4. Lấy vấn đề gần nhất (name + content)
$lastLogName = '';
$lastLogContent = '';
$resLast = $conn->query("SELECT name, content FROM logs ORDER BY id DESC LIMIT 1");
if ($resLast && $resLast->num_rows > 0) {
    $lastLogData = $resLast->fetch_assoc();
    $lastLogName = $lastLogData['name'];
    $lastLogContent = $lastLogData['content'];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Fluency">
    <meta name="description" content="Leverage Fluency - Track and resolve recurring problems">

    <title>Leverage Fluency</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="icons/icon-512.png">

    <link rel="stylesheet" href="style.css?v=2">

    <!-- Register Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js?v=2')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }

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
            Xin chào <strong><?php echo htmlspecialchars($username); ?></strong> | 
            <a href="#" onclick="forceReload(); return false;" style="color: #60a5fa;">🔄 Làm mới</a> | 
            <a href="logout.php">Đăng xuất</a>
        </div>

        <!-- Thông báo -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert error"><?php echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <!-- Page Header -->
        <h1 class="page-title">Leverage Fluency</h1>
        <p class="page-subtitle">Ghi lại vấn đề lặp lại</p>

        <?php if ($countLogged > 0): ?>
            <!-- Stats Dashboard - Only show if there are problems -->
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
                    <span class="stat-label">Need action</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content Area -->
        <div class="main-content-area">
            <!-- Hero Button - Tạo vấn đề mới -->
            <button onclick="openWizard()" class="hero-btn-inline">
                + Tạo vấn đề
            </button>

            <?php if ($countLogged > 0): ?>
                <!-- View Problems Button -->
                <button class="view-problems-btn" id="toggleListBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Xem vấn đề (<?php echo $countLogged; ?>)
                </button>
            <?php endif; ?>
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

        <!-- Wizard Overlay - Lovable Style Multi-Step -->
        <div id="addLogWizard" class="wizard-overlay">
            <div class="wizard-container">
                <!-- Close button -->
                <button class="wizard-close-btn" onclick="closeWizard()">✕</button>

                <form method="POST" id="wizardForm">
                    <!-- Hidden fields -->
                    <input type="hidden" name="log_version" value="1.0">
                    <input type="hidden" name="log_status" id="hiddenStatus" value="open">
                    <input type="hidden" name="log_name" id="hiddenLogName" value="Vấn đề mới">
                    <input type="hidden" name="emotion_level" id="hiddenEmotionLevel" value="">

                    <!-- Step 1: Mô tả vấn đề -->
                    <div class="wizard-step active" id="step1">
                        <h2 class="wizard-question">Mô tả vấn đề</h2>
                        <p class="wizard-hint">Ghi lại điều gì đang xảy ra</p>

                        <textarea name="log_content" class="big-textarea" placeholder="Ví dụ: Lại quên mật khẩu wifi..."
                            required></textarea>

                        <div class="wizard-actions">
                            <button type="button" class="btn" onclick="goToStep(2)">Tiếp tục</button>
                        </div>
                    </div>

                    <!-- Step 2: Mức độ khó chịu -->
                    <div class="wizard-step" id="step2">
                        <h2 class="wizard-question">Mức độ khó chịu?</h2>
                        <p class="wizard-hint">Chọn để lưu vấn đề</p>

                        <div class="emotion-selector" id="emotionGroup">
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('frustrated')">
                                <span class="emotion-emoji">😠</span>
                                <span class="emotion-label">Rất khó chịu</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('annoyed')">
                                <span class="emotion-emoji">😕</span>
                                <span class="emotion-label">Hơi khó chịu</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('neutral')">
                                <span class="emotion-emoji">😐</span>
                                <span class="emotion-label">Bình thường</span>
                            </div>
                        </div>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="goToStep(1)">Quay lại</span>
                        </div>
                    </div>

                    <!-- Hidden submit button -->
                    <button type="submit" name="add_log" id="hiddenSubmit" style="display:none;"></button>
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

            // Wizard Logic
            const wizard = document.getElementById("addLogWizard");
            let currentStep = 1;

            function openWizard() {
                wizard.style.display = 'block';
                currentStep = 1;
                resetWizard();
            }

            function closeWizard() {
                wizard.style.display = 'none';
                resetWizard();
            }

            function resetWizard() {
                // Reset all steps
                document.querySelectorAll('.wizard-step').forEach((step, index) => {
                    step.classList.remove('active', 'step-exit-left', 'step-enter-right');
                    step.style.display = index === 0 ? 'block' : 'none';
                    if (index === 0) step.classList.add('active');
                });
                // Reset selections
                document.querySelectorAll('.emotion-option').forEach(opt => opt.classList.remove('selected'));
                // Reset form
                document.getElementById('wizardForm').reset();
                document.getElementById('hiddenEmotionLevel').value = '';
                currentStep = 1;
            }

            function goToStep(stepNum) {
                const currentStepEl = document.getElementById(`step${currentStep}`);
                const nextStepEl = document.getElementById(`step${stepNum}`);

                // Validation for step 1 (description required)
                if (currentStep === 1 && stepNum > 1) {
                    const content = document.querySelector('textarea[name="log_content"]').value;
                    if (!content.trim()) {
                        alert("Vui lòng nhập mô tả vấn đề!");
                        return;
                    }
                }

                // Animate out
                currentStepEl.classList.add("step-exit-left");

                setTimeout(() => {
                    currentStepEl.classList.remove("active", "step-exit-left");
                    currentStepEl.style.display = "none";

                    nextStepEl.style.display = "block";
                    nextStepEl.classList.add("step-enter-right", "active");
                    currentStep = stepNum;
                }, 250);
            }

            // Emotion Selection - Auto submit
            function selectEmotionAndSubmit(value) {
                // Visual feedback
                event.currentTarget.classList.add('selected');
                document.getElementById('hiddenEmotionLevel').value = value;

                // Map emotion to status
                const statusMap = {
                    'frustrated': 'open',
                    'annoyed': 'in_progress',
                    'neutral': 'in_progress'
                };
                document.getElementById('hiddenStatus').value = statusMap[value] || 'open';

                // Submit form after brief animation
                setTimeout(() => {
                    document.getElementById('hiddenSubmit').click();
                }, 400);
            }

            // Close the logs list overlay
            function closeLogsList() {
                const logsListContainer = document.getElementById("logsListContainer");
                logsListContainer.classList.remove('show');
            }

            // Toggle List Logic
            const toggleListBtn = document.getElementById("toggleListBtn");
            const logsListContainer = document.getElementById("logsListContainer");

            toggleListBtn && toggleListBtn.addEventListener('click', () => {
                const isShowing = logsListContainer.classList.contains('show');
                if (isShowing) {
                    logsListContainer.classList.remove('show');
                } else {
                    logsListContainer.classList.add('show');
                }
            });

            // Modal for content preview
            const contentModal = document.getElementById("contentModal");
            const fullContentDisplay = document.getElementById("fullContentDisplay");
            const closeContent = contentModal ? contentModal.querySelector('.close') : null;

            document.querySelectorAll('.content-preview').forEach(item => {
                item.addEventListener('click', function () {
                    fullContentDisplay.textContent = this.getAttribute('data-full');
                    contentModal.style.display = 'block';
                });
            });

            closeContent && closeContent.addEventListener('click', () => contentModal.style.display = 'none');

            // Initialize tabs on load
            document.addEventListener('DOMContentLoaded', () => {
                const activeBtn = document.querySelector('.tablink.active') || document.querySelector('.tablink');
                if (activeBtn) activeBtn.click();
            });

            // Close wizard on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && wizard.style.display === 'block') {
                    closeWizard();
                }
            });
            // Hàm Force Refresh Clear Cache
            async function forceReload() {
                const btn = event.target;
                btn.innerHTML = "🔄 Đang xử lý...";
                
                try {
                    // 1. Unregister Service Workers
                    if ('serviceWorker' in navigator) {
                        const registrations = await navigator.serviceWorker.getRegistrations();
                        for (let registration of registrations) {
                            await registration.unregister();
                        }
                    }

                    // 2. Xóa Cache Storage
                    if ('caches' in window) {
                        const cacheNames = await caches.keys();
                        await Promise.all(
                            cacheNames.map(name => caches.delete(name))
                        );
                    }

                    console.log("Cache cleared!");
                } catch (e) {
                    console.error("Error clearing cache:", e);
                }

                // 3. Reload trang cực mạnh (bỏ qua cache trình duyệt)
                window.location.href = window.location.pathname + '?t=' + new Date().getTime();
            }
        </script>
</body>

</html>