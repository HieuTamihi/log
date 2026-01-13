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

    <div style="display: flex; align-items: center; gap: 12px; margin-top: 16px; flex-wrap: wrap;">
        <?php if ($has_solution): ?>
            <a href="solution_detail.php?id=<?php echo $row['sid']; ?>" class="btn btn-small">View Solution</a>
            <span style="font-size: 12px; color: var(--text-muted);">
                Solved by <?php echo htmlspecialchars($row['solution_creator'] ?? 'Unknown'); ?>
            </span>
        <?php else: ?>
            <a href="create_solution.php?log_id=<?php echo $row['id']; ?>" class="btn btn-small">Create Solution</a>
        <?php endif; ?>

        <?php if (isset($currentUserId) && $currentUserId == $row['user_id']): ?>
            <div style="margin-left: auto; display: flex; gap: 8px;">
                <!-- Edit Button -->
                <?php
                // Prepare safe data for JS
                $jsContent = json_encode($row['content']);
                // Escape for HTML attribute (single quotes of the onclick attribute)
                $safeContent = htmlspecialchars($jsContent, ENT_QUOTES, 'UTF-8');
                ?>
                <button class="btn btn-secondary btn-icon"
                    onclick='openEditModal(<?php echo $row['id']; ?>, <?php echo $safeContent; ?>)' title="Chỉnh sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="color: var(--warning-color);">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>

                <!-- Delete Button (Only if no solution) -->
                <?php if (!$has_solution): ?>
                    <form method="POST" action="index.php"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa vấn đề này? Hành động này không thể hoàn tác.');"
                        style="display:inline;">
                        <input type="hidden" name="delete_log" value="1">
                        <input type="hidden" name="log_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-secondary btn-icon" title="Xóa">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="color: var(--danger-color);">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>