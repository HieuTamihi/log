<?php
include 'db.php';
if (!isset($_GET['id']))
    die("Không có ID");
$id = (int) $_GET['id'];

// Xử lý cập nhật
if (isset($_POST['update_solution'])) {
    $curStmt = $conn->prepare("SELECT * FROM solutions WHERE id = ?");
    $curStmt->bind_param("i", $id);
    $curStmt->execute();
    $cur = $curStmt->get_result()->fetch_assoc();
    $curStmt->close();

    $new_name = $_POST['name'];
    $new_content = $_POST['content'];
    $new_version = $_POST['version'] ?: $cur['version'];
    $new_status = $_POST['status'];

    // Nếu có thay đổi → lưu lịch sử
    if (
        $new_name !== $cur['name'] || $new_content !== $cur['content'] ||
        $new_version !== $cur['version'] || $new_status !== $cur['status']
    ) {

        $hist = $conn->prepare("INSERT INTO solution_history (solution_id, name, content, version, status) VALUES (?, ?, ?, ?, ?)");
        $hist->bind_param("issss", $id, $cur['name'], $cur['content'], $cur['version'], $cur['status']);
        $hist->execute();
        $hist->close();
    }

    // Cập nhật bản hiện tại (prepared statement)
    $up = $conn->prepare("UPDATE solutions SET name = ?, content = ?, version = ?, status = ? WHERE id = ?");
    $up->bind_param("ssssi", $new_name, $new_content, $new_version, $new_status, $id);
    $up->execute();
    $up->close();

    $_SESSION['msg'] = "Cập nhật giải pháp thành công!";
    header("Location: solution_detail.php?id=$id");
    exit();
}

// Lấy dữ liệu hiện tại (prepared statement)
$stmt = $conn->prepare("SELECT S.*, L.name AS log_name, L.content AS log_content FROM solutions S JOIN logs L ON S.log_id = L.id WHERE S.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Giải pháp không tồn tại.");
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($row['log_name']); ?> → <?php echo htmlspecialchars($row['name']); ?></h1>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert success"><?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert success"><?php echo $_SESSION['msg'];
            unset($_SESSION['msg']); ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 24px;">
            <h2>Vấn Đề</h2>
            <pre><?php echo htmlspecialchars($row['log_content']); ?></pre>

            <h2>Giải Pháp Hiện Tại</h2>
            <pre><?php echo htmlspecialchars($row['content']); ?></pre>

            <!-- Nút mở Modal Cập Nhật -->
            <div style="text-align: center; margin-top: 30px;">
                <button id="openUpdateModal" class="btn">
                    Cập Nhật Giải Pháp
                </button>
            </div>
        </div>

        <div class="card">
            <h2>Lịch Sử Thay Đổi (3 phiên bản gần nhất)</h2>
            <table>
                <tr>
                    <th>Phiên Bản</th>
                    <th>Trạng Thái</th>
                    <th>Nội Dung (rút gọn)</th>
                    <th>Thời Gian</th>
                </tr>
                <?php
                // Chỉ lấy 3 bản ghi gần nhất
                $hist = $conn->prepare("SELECT * FROM solution_history WHERE solution_id = ? ORDER BY changed_at DESC LIMIT 3");
                $hist->bind_param("i", $id);
                $hist->execute();
                $resHist = $hist->get_result();

                if ($resHist->num_rows == 0) {
                    echo "<tr><td colspan='4'>Chưa có thay đổi nào</td></tr>";
                } else {
                    while ($h = $resHist->fetch_assoc()) {
                        $stt = $h['status'] == 'draft' ? 'Bản nháp' : ($h['status'] == 'testing' ? 'Đang kiểm tra' : 'Hoàn thành');

                        // Rút gọn nội dung để bảng không quá dài
                        $short_content = mb_strlen($h['content'], 'UTF-8') > 150
                            ? mb_substr(htmlspecialchars($h['content']), 0, 150, 'UTF-8') . '...'
                            : htmlspecialchars($h['content']);

                        echo "<tr>\n                        <td>" . htmlspecialchars($h['version']) . "</td>\n                        <td>$stt</td>\n                        <td>$short_content</td>\n                        <td>" . htmlspecialchars($h['changed_at']) . "</td>\n                    </tr>";
                    }
                }
                $hist->close();
                ?>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Quay Lại Danh Sách</a>
        </div>
    </div>

    <!-- Modal Cập Nhật Giải Pháp -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Cập Nhật Giải Pháp</h2>
            <form method="POST">
                <label>Tên giải pháp:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>

                <label>Nội dung:</label>
                <textarea name="content" rows="10" required><?php echo htmlspecialchars($row['content']); ?></textarea>

                <label>Phiên bản:</label>
                <input type="text" name="version" value="<?php echo htmlspecialchars($row['version']); ?>">

                <label>Trạng thái:</label>
                <select name="status">
                    <option value="draft" <?php if ($row['status'] == 'draft')
                        echo 'selected'; ?>>Bản nháp</option>
                    <option value="testing" <?php if ($row['status'] == 'testing')
                        echo 'selected'; ?>>Đang kiểm tra
                    </option>
                    <option value="done" <?php if ($row['status'] == 'done')
                        echo 'selected'; ?>>Hoàn thành</option>
                </select>

                <div style="margin-top: 20px;">
                    <button type="submit" name="update_solution">Lưu Thay Đổi</button>
                    <button type="button" id="cancelUpdate" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("updateModal");
        const openBtn = document.getElementById("openUpdateModal");
        const closeBtn = modal ? modal.querySelector('.close') : null;
        const cancelBtn = document.getElementById("cancelUpdate");

        openBtn && openBtn.addEventListener('click', () => modal.style.display = 'block');
        closeBtn && closeBtn.addEventListener('click', () => modal.style.display = 'none');
        cancelBtn && cancelBtn.addEventListener('click', () => modal.style.display = 'none');

        window.addEventListener('click', (event) => {
            if (event.target === modal) modal.style.display = 'none';
        });
    </script>
</body>

</html>