<div class="card">
    <div class="card-header split-header">
        <h2>Blocked IP List</h2>
        <div class="muted">Current blacklist records</div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>IP Address</th>
                    <th>Reason</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="4" class="empty-cell">No blocked IP records.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>#<?= e((string) $item['id']) ?></td>
                            <td><?= e($item['ip_address']) ?></td>
                            <td><?= e($item['reason'] ?: '-') ?></td>
                            <td><?= e((string) $item['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
