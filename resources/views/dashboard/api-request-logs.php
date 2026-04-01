<div class="card">
    <div class="card-header split-header"><h2>API Request Logs</h2><div class="muted">Total: <?= e((string) $pagination['total']) ?></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Site</th><th>Request</th><th>Result</th><th>Message</th><th>Created At</th></tr></thead><tbody>
    <?php if (empty($pagination['data'])): ?><tr><td colspan="6" class="empty-cell">No API request logs yet.</td></tr><?php else: ?>
        <?php foreach ($pagination['data'] as $row): ?>
            <tr>
                <td>#<?= e((string) $row['id']) ?></td>
                <td><div class="table-title"><?= e($row['site_name'] ?: ($row['site_key'] ?: 'Unknown')) ?></div><div class="table-sub"><?= e($row['site_key'] ?: '-') ?></div></td>
                <td><div class="table-title"><?= e($row['request_method']) ?> <?= e($row['endpoint']) ?></div><div class="table-sub"><?= e($row['request_ip'] ?: '-') ?> · <?= e($row['origin_host'] ?: '-') ?></div></td>
                <td><span class="status-badge <?= (int) $row['response_status'] >= 400 ? 'status-spam' : 'status-read' ?>"><?= e((string) $row['response_status']) ?></span><div class="table-sub"><?= e($row['result_code'] ?: '-') ?></div></td>
                <td><?= e($row['result_message'] ?: '-') ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody></table></div>
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?><div class="pagination"><?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?><a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('api-logs', ['page' => $i])) ?>"><?= e((string) $i) ?></a><?php endfor; ?></div><?php endif; ?>
</div>
