<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
        <div>
            <span class="card-title"><?php echo htmlspecialchars($row['name']); ?></span>
            <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">
                by <?php echo htmlspecialchars($creator ?? 'Unknown'); ?>
            </span>
        </div>
        <?php
        $status_color = $row['status'] == 'open' ? 'var(--danger-color)' : ($row['status'] == 'in_progress' ? 'var(--warning-color)' : 'var(--success-color)');
        $status_text = $row['status'] == 'open' ? 'Open' : ($row['status'] == 'in_progress' ? 'In Progress' : 'Closed');
        ?>
        <span
            style="font-size: 11px; padding: 4px 10px; background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>; border-radius: 12px; font-weight: 500;">
            <?php echo $status_text; ?>
        </span>
    </div>

    <?php $has_solution = !empty($row['sid']); ?>

    <?php
    $full = $row['content'] ?? '';
    $short = mb_strlen($full, 'UTF-8') > 150 ? mb_substr($full, 0, 150, 'UTF-8') . '...' : $full;
    ?>

    <p class="content-preview" data-full="<?php echo htmlspecialchars($full, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
        <?php echo nl2br(htmlspecialchars($short)); ?>
    </p>

    <div style="display: flex; align-items: center; gap: 12px; margin-top: 16px;">
        <?php if ($has_solution): ?>
            <a href="solution_detail.php?id=<?php echo $row['sid']; ?>" class="btn btn-small">View Solution</a>
            <span style="font-size: 12px; color: var(--text-muted);">
                Solved by <?php echo htmlspecialchars($row['solution_creator'] ?? 'Unknown'); ?>
            </span>
        <?php else: ?>
            <a href="create_solution.php?log_id=<?php echo $row['id']; ?>" class="btn btn-small">Create Solution</a>
        <?php endif; ?>
    </div>
</div>