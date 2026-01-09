<?php include 'db.php';
requireLogin();
$log_id_get = isset($_GET['log_id']) ? (int) $_GET['log_id'] : 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tạo Giải Pháp</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>Tạo Giải Pháp Mới</h1>

            <form method="POST">
                <label>Chọn vấn đề:</label>
                <select name="log_id" required>
                    <option value="">-- Chọn vấn đề --</option>
                    <?php
                    $res = $conn->query("SELECT l.id, l.name FROM logs l LEFT JOIN solutions s ON l.id = s.log_id WHERE s.id IS NULL");
                    while ($r = $res->fetch_assoc()) {
                        $sel = $r['id'] == $log_id_get ? 'selected' : '';
                        echo "<option value='{$r['id']}' $sel>{$r['id']} - {$r['name']}</option>";
                    }
                    ?>
                </select>

                <label>Tên giải pháp:</label>
                <input type="text" name="solution_name" placeholder="Tên giải pháp" required>

                <label>Nội dung:</label>
                <textarea name="solution_content" rows="8" placeholder="Nội dung giải pháp chi tiết"
                    required></textarea>

                <label>Phiên bản:</label>
                <input type="text" name="solution_version" value="1.0" placeholder="Version">

                <label>Trạng thái:</label>
                <select name="solution_status">
                    <option value="draft">Bản nháp</option>
                    <option value="testing">Đang kiểm tra</option>
                    <option value="done">Hoàn thành</option>
                </select>

                <div style="margin-top: 20px;">
                    <button type="submit" name="add_solution">Tạo Giải Pháp</button>
                    <a href="index.php" class="btn btn-secondary">Quay Lại</a>
                </div>
            </form>
        </div>

        <?php
        if (isset($_POST['add_solution'])) {
            $log_id = (int) $_POST['log_id'];
            $name = trim($_POST['solution_name']);
            $content = trim($_POST['solution_content']);
            $version = $_POST['solution_version'] ?: '1.0';
            $status = $_POST['solution_status'];

            // Validate selected log exists
            $logChk = $conn->prepare("SELECT id FROM logs WHERE id = ?");
            $logChk->bind_param("i", $log_id);
            $logChk->execute();
            $logChk->store_result();
            if ($logChk->num_rows == 0) {
                echo '<div class="alert error">Vấn đề chọn không tồn tại.</div>';
                $logChk->close();
            } else {
                $logChk->close();

                $check = $conn->prepare("SELECT id FROM solutions WHERE log_id = ?");
                $check->bind_param("i", $log_id);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    echo '<div class="alert error">Vấn đề này đã có giải pháp!</div>';
                    $check->close();
                } else {
                    $check->close();

                    // Determine valid user_id (session user must exist in Users table)
                    $user_id_raw = getCurrentUserId();
                    $valid_user_id = null;
                    if (!empty($user_id_raw)) {
                        $ucheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
                        $ucheck->bind_param("i", $user_id_raw);
                        $ucheck->execute();
                        $ucheck->store_result();
                        if ($ucheck->num_rows > 0)
                            $valid_user_id = (int) $user_id_raw;
                        $ucheck->close();
                    }

                    if ($valid_user_id) {
                        $ins = $conn->prepare("INSERT INTO solutions (log_id, user_id, name, content, version, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $ins->bind_param("iissss", $log_id, $valid_user_id, $name, $content, $version, $status);
                    } else {
                        $ins = $conn->prepare("INSERT INTO solutions (log_id, name, content, version, status) VALUES (?, ?, ?, ?, ?)");
                        $ins->bind_param("issss", $log_id, $name, $content, $version, $status);
                    }

                    if ($ins->execute()) {
                        $sid = $conn->insert_id;
                        $_SESSION['success_message'] = 'Tạo giải pháp thành công!';
                        header("Location: solution_detail.php?id=$sid");
                        exit();
                    } else {
                        echo '<div class="alert error">Lỗi khi tạo giải pháp: ' . htmlspecialchars($ins->error) . '</div>';
                    }
                    $ins->close();
                }
            }
        }
        ?>
    </div>
</body>

</html>