<?php
include 'db.php';
requireLogin();  // Nếu bạn đã có hàm này

// Hiển thị tên người dùng (đã đăng nhập)
$username = $_SESSION['username'] ?? 'Người dùng';

// XỬ LÝ THÊM LOG - SỬ DỤNG PRG ĐỂ TRÁNH RESUBMIT
if (isset($_POST['add_log'])) {
    $name = trim($_POST['log_name']);
    $content = trim($_POST['log_content'] ?? '');
    $version = $_POST['log_version'] ?: '1.0';
    $status = $_POST['log_status'] ?? 'open';
    $frequency = $_POST['frequency'] ?? '';
    $user_id = (int) getCurrentUserId();

    // Only name is required, content is optional
    if (!empty($name)) {
        // Add frequency info to content if provided
        if (!empty($frequency)) {
            $content = "[Frequency: " . ucfirst($frequency) . "] " . $content;
        }

        $stmt = $conn->prepare("INSERT INTO logs (name, content, version, status, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $content, $version, $status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm vấn đề thành công!";
        } else {
            $_SESSION['error_message'] = "Lỗi khi thêm: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Vui lòng nhập tiêu đề!";
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

        <!-- Page Header - Lovable Style -->
        <h1 class="page-title">Leverage Fluency</h1>
        <p class="page-subtitle">Repeated problem capture</p>

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
            <?php if (!empty($lastLogName)): ?>
                <!-- Task Completed Card - Lovable Style -->
                <div class="task-completed-card">
                    <div class="task-completed-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>Task completed</span>
                    </div>
                    <div class="task-completed-title"><?php echo htmlspecialchars($lastLogName); ?></div>
                </div>
            <?php endif; ?>

            <!-- Hero Button - Lovable Style (Not Fixed) -->
            <button onclick="openWizard()" class="hero-btn-inline">
                Simulate task completion
            </button>
            <p class="hero-hint">In production, this flow triggers automatically</p>

            <?php if ($countLogged > 0): ?>
                <!-- View Problems Button - Lovable Style -->
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
                    View problems (<?php echo $countLogged; ?>)
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
                    <!-- Hidden fields for additional data -->
                    <input type="hidden" name="log_version" value="1.0">
                    <input type="hidden" name="log_status" id="hiddenStatus" value="open">
                    <input type="hidden" name="cost_type" id="hiddenCostType" value="">
                    <input type="hidden" name="emotion_level" id="hiddenEmotionLevel" value="">
                    <input type="hidden" name="frequency" id="hiddenFrequency" value="">

                    <!-- Step 1: Did this just repeat? (FIRST - like Lovable) -->
                    <div class="wizard-step active" id="step1">
                        <h2 class="wizard-question centered">Did this just repeat?</h2>

                        <div class="button-stack">
                            <button type="button" class="stack-btn secondary" onclick="selectRepeatOption('same')">Same
                                as last time</button>
                            <button type="button" class="stack-btn primary"
                                onclick="selectRepeatOption('yes')">Yes</button>
                            <button type="button" class="stack-btn outline"
                                onclick="selectRepeatOption('no')">No</button>
                        </div>
                    </div>

                    <!-- Step 2: Problem Description -->
                    <div class="wizard-step" id="step2">
                        <h2 class="wizard-question">What's happening?</h2>
                        <p class="wizard-hint">Describe the problem you're experiencing</p>

                        <input type="text" name="log_name" class="big-input" placeholder="Brief title for this issue..."
                            required>
                        <textarea name="log_content" class="big-textarea"
                            placeholder="Describe what went wrong and any context that might help..."></textarea>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="goToStep(1)">Back</span>
                            <button type="button" class="btn" onclick="goToStep(3)">Continue</button>
                        </div>
                    </div>

                    <!-- Step 3: How often does this happen? (NEW) -->
                    <div class="wizard-step" id="step3">
                        <h2 class="wizard-question">How often does this happen?</h2>

                        <div class="frequency-grid">
                            <button type="button" class="frequency-btn"
                                onclick="selectFrequency('daily')">Daily</button>
                            <button type="button" class="frequency-btn"
                                onclick="selectFrequency('weekly')">Weekly</button>
                            <button type="button" class="frequency-btn"
                                onclick="selectFrequency('monthly')">Monthly</button>
                            <button type="button" class="frequency-btn" onclick="selectFrequency('rare')">Rare</button>
                        </div>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="goToStep(2)">Back</span>
                        </div>
                    </div>

                    <!-- Step 4: Cost Type Selection (like Lovable) -->
                    <div class="wizard-step" id="step4">
                        <h2 class="wizard-question">What does this cost when it repeats?</h2>
                        <p class="wizard-hint">Select the primary impact of this issue</p>

                        <div class="icon-selector" id="costTypeGroup">
                            <div class="icon-option" onclick="selectCostType(this, 'time')">
                                <div class="icon-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <span class="icon-label">Time</span>
                            </div>
                            <div class="icon-option" onclick="selectCostType(this, 'money')">
                                <div class="icon-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <span class="icon-label">Money</span>
                            </div>
                            <div class="icon-option" onclick="selectCostType(this, 'errors')">
                                <div class="icon-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                                        </path>
                                        <line x1="12" y1="9" x2="12" y2="13"></line>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                </div>
                                <span class="icon-label">Errors</span>
                            </div>
                            <div class="icon-option" onclick="selectCostType(this, 'stress')">
                                <div class="icon-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 15h8"></path>
                                        <circle cx="9" cy="9" r="1"></circle>
                                        <circle cx="15" cy="9" r="1"></circle>
                                    </svg>
                                </div>
                                <span class="icon-label">Stress</span>
                            </div>
                            <div class="icon-option" onclick="selectCostType(this, 'reputation')">
                                <div class="icon-box">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <span class="icon-label">Reputation</span>
                            </div>
                        </div>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="goToStep(3)">Back</span>
                        </div>
                    </div>

                    <!-- Step 5: Emotion Level (Auto-submit on select) -->
                    <div class="wizard-step" id="step5">
                        <h2 class="wizard-question">How frustrated are you?</h2>
                        <p class="wizard-hint">Click to log and we'll save your problem</p>

                        <div class="emotion-selector" id="emotionGroup">
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('very_frustrated')">
                                <span class="emotion-emoji">😤</span>
                                <span class="emotion-label">Very Frustrated</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('frustrated')">
                                <span class="emotion-emoji">😠</span>
                                <span class="emotion-label">Frustrated</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('annoyed')">
                                <span class="emotion-emoji">😕</span>
                                <span class="emotion-label">Annoyed</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('neutral')">
                                <span class="emotion-emoji">😐</span>
                                <span class="emotion-label">Neutral</span>
                            </div>
                            <div class="emotion-option" onclick="selectEmotionAndSubmit('fine')">
                                <span class="emotion-emoji">🙂</span>
                                <span class="emotion-label">It's Fine</span>
                            </div>
                        </div>

                        <div class="wizard-actions">
                            <span class="wizard-back" onclick="goToStep(4)">Back</span>
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

            // Last log data for "Same as last time" feature
            const lastLogData = {
                name: <?php echo json_encode($lastLogName); ?>,
                content: <?php echo json_encode($lastLogContent); ?>
            };

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
                document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                document.querySelectorAll('.emotion-option').forEach(opt => opt.classList.remove('selected'));
                document.querySelectorAll('.stack-btn').forEach(btn => btn.classList.remove('selected'));
                document.querySelectorAll('.frequency-btn').forEach(btn => btn.classList.remove('selected'));
                // Reset form
                document.getElementById('wizardForm').reset();
                document.getElementById('hiddenCostType').value = '';
                document.getElementById('hiddenEmotionLevel').value = '';
                document.getElementById('hiddenFrequency').value = '';
                currentStep = 1;
            }

            // Step 1: "Did this just repeat?" options
            function selectRepeatOption(option) {
                // Visual feedback
                event.currentTarget.classList.add('selected');

                if (option === 'same') {
                    // Same as last time - auto-fill with last log and submit immediately
                    if (lastLogData.name) {
                        document.querySelector('input[name="log_name"]').value = lastLogData.name;
                        document.querySelector('textarea[name="log_content"]').value = lastLogData.content || 'Repeated issue';
                        document.getElementById('hiddenCostType').value = 'recurring';
                        document.getElementById('hiddenEmotionLevel').value = 'neutral';
                        document.getElementById('hiddenStatus').value = 'in_progress';

                        // Submit form after brief animation
                        setTimeout(() => {
                            document.getElementById('hiddenSubmit').click();
                        }, 300);
                    } else {
                        // No previous log, go to step 2
                        setTimeout(() => goToStep(2), 200);
                    }
                } else if (option === 'yes') {
                    // Yes - go to describe problem
                    setTimeout(() => goToStep(2), 200);
                } else {
                    // No - close wizard
                    setTimeout(() => closeWizard(), 200);
                }
            }

            function goToStep(stepNum) {
                const currentStepEl = document.getElementById(`step${currentStep}`);
                const nextStepEl = document.getElementById(`step${stepNum}`);

                // Validation for step 2 (only title is required)
                if (currentStep === 2 && stepNum > 2) {
                    const name = document.querySelector('input[name="log_name"]').value;
                    if (!name) {
                        alert("Please enter a title!");
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

                    // Focus on input if going to step 2
                    if (stepNum === 2) {
                        setTimeout(() => document.querySelector('input[name="log_name"]').focus(), 100);
                    }
                }, 250);
            }

            // Cost Type Selection - Auto advance to step 5
            function selectCostType(el, value) {
                // Remove selection from all
                document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                // Add selection to clicked
                el.classList.add('selected');
                // Set hidden value
                document.getElementById('hiddenCostType').value = value;

                // Auto advance to step 5 after a brief delay
                setTimeout(() => {
                    goToStep(5);
                }, 300);
            }

            // Frequency Selection - Auto advance to step 4 (cost)
            function selectFrequency(value) {
                // Remove selection from all
                document.querySelectorAll('.frequency-btn').forEach(btn => btn.classList.remove('selected'));
                // Add selection to clicked
                event.currentTarget.classList.add('selected');
                // Set hidden value
                document.getElementById('hiddenFrequency').value = value;

                // Auto advance to step 4 after a brief delay
                setTimeout(() => {
                    goToStep(4);
                }, 300);
            }

            // Emotion Selection - Auto submit
            function selectEmotionAndSubmit(value) {
                // Visual feedback
                event.currentTarget.classList.add('selected');
                document.getElementById('hiddenEmotionLevel').value = value;

                // Map emotion to status
                const statusMap = {
                    'very_frustrated': 'open',
                    'frustrated': 'open',
                    'annoyed': 'in_progress',
                    'neutral': 'in_progress',
                    'fine': 'closed'
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
        </script>
</body>

</html>