<div class="card">
    <div class="card-header split-header">
        <h2>System Logs</h2>
        <div class="muted">Total: <?= e((string) $pagination['total']) ?></div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Action</th>
                    <th>Admin</th>
                    <th>Inquiry</th>
                    <th>Note</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagination['data'])): ?>
                    <tr>
                        <td colspan="6" class="empty-cell">No logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['data'] as $log): ?>
                        <tr>
                            <td>#<?= e((string) $log['id']) ?></td>
                            <td><code><?= e($log['action']) ?></code></td>
                            <td><?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?></td>
                            <td>
                                <?php if (!empty($log['inquiry_id'])): ?>
                                    <a href="<?= e(base_url('inquiry?id=' . (int) $log['inquiry_id'])) ?>">#<?= e((string) $log['inquiry_id']) ?></a>
                                    <div class="table-sub"><?= e($log['inquiry_email'] ?: $log['inquiry_title'] ?: '') ?></div>
                                <?php else: ?>
                                    <span class="muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($log['action_note'] ?: '-') ?></td>
                            <td><?= e((string) $log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?>
                <a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('logs', ['page' => $i])) ?>">
                    <?= e((string) $i) ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
