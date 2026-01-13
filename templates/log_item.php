<?php
// templates/log_item.php
$status_text = $row['status'] == 'open' ? 'Mở' : ($row['status'] == 'in_progress' ? 'Đang xử lý' : 'Đã đóng');
$solution_text = $row['sid'] ? "<a href='solution_detail.php?id={$row['sid']}'>Xem Solution</a>" : "<a href='add_solution.php?log_id={$row['id']}'>Tạo Solution</a>";
$short_content = mb_strlen($row['content'], 'UTF-8') > 100 ? mb_substr($row['content'], 0, 100, 'UTF-8') . '...' : $row['content'];
?>
<div class="card" style="margin-bottom: 20px;">
    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
    <small style="color: var(--text-secondary);">(bởi <?php echo htmlspecialchars($creator ?? 'Không rõ'); ?>)</small>

    <?php $has_solution = !empty($row['sid']); ?>

    <?php
    $short = mb_strlen($row['content'] ?? '', 'UTF-8') > 200 ? mb_substr($row['content'] ?? '', 0, 200, 'UTF-8') . '...' : $row['content'] ?? '';
    ?>

    <p class="content-preview" data-log-id="<?php echo (int)$row['id']; ?>"
        style="cursor:pointer; color: var(--text-secondary);">
        <?php echo nl2br(htmlspecialchars($short)); ?>
    </p>

    <small style="color: var(--text-secondary);">Trạng thái: <span
            style="color: var(--text-primary);"><?php echo $status_text; ?></span></small><br><br>

    <?php if ($has_solution): ?>
        <a href="solution_detail.php?id=<?php echo $row['sid']; ?>" class="btn">Xem Solution</a>
    <?php else: ?>
        <a href="create_solution.php?log_id=<?php echo $row['id']; ?>" class="btn">Tạo Solution</a>
    <?php endif; ?>

    <?php if (!empty($row['sid'])): ?>
        <br><br><small style="color: var(--text-secondary);">Giải pháp bởi:
            <?php echo htmlspecialchars($row['solution_creator'] ?? 'Không rõ'); ?></small>
    <?php endif; ?>
</div>