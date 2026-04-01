<div class="card mb-20">
    <div class="card-header"><h2>Filters</h2></div>
    <div class="card-body">
        <form method="get" action="<?= e(base_url('api-logs')) ?>" class="filter-grid">
            <label class="form-label">
                <span>Site</span>
                <select name="site_id" class="form-input">
                    <option value="">All sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= (int) $site['id'] ?>" <?= (string) ($filters['site_id'] ?? '') === (string) $site['id'] ? 'selected' : '' ?>><?= e($site['site_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-label">
                <span>Result</span>
                <select name="status_class" class="form-input">
                    <option value="">All</option>
                    <option value="success" <?= ($filters['status_class'] ?? '') === 'success' ? 'selected' : '' ?>>Success (&lt; 400)</option>
                    <option value="error" <?= ($filters['status_class'] ?? '') === 'error' ? 'selected' : '' ?>>Error (400+)</option>
                </select>
            </label>
            <label class="form-label full-width">
                <span>Keyword</span>
                <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search site key, code, message, IP or endpoint...">
            </label>
            <div class="filter-actions full-width">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="<?= e(base_url('api-logs')) ?>" class="btn">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header split-header"><h2>API Request Logs</h2><div class="muted">Total: <?= e((string) $pagination['total']) ?></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Site</th><th>Request</th><th>Result</th><th>Message</th><th>Created At</th><th>Actions</th></tr></thead><tbody>
    <?php if (empty($pagination['data'])): ?><tr><td colspan="7" class="empty-cell">No API request logs yet.</td></tr><?php else: ?>
        <?php foreach ($pagination['data'] as $row): ?>
            <tr>
                <td>#<?= e((string) $row['id']) ?></td>
                <td><div class="table-title"><?= e($row['site_name'] ?: ($row['site_key'] ?: 'Unknown')) ?></div><div class="table-sub"><?= e($row['site_key'] ?: '-') ?></div></td>
                <td><div class="table-title"><?= e($row['request_method']) ?> <?= e($row['endpoint']) ?></div><div class="table-sub"><?= e($row['request_ip'] ?: '-') ?> · <?= e($row['origin_host'] ?: '-') ?></div></td>
                <td><span class="status-badge <?= (int) $row['response_status'] >= 400 ? 'status-spam' : 'status-read' ?>"><?= e((string) $row['response_status']) ?></span><div class="table-sub"><?= e($row['result_code'] ?: '-') ?></div></td>
                <td><?= e($row['result_message'] ?: '-') ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
                <td><a class="btn btn-sm" href="<?= e(base_url('api-log?id=' . (int) $row['id'])) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody></table></div>
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?><div class="pagination"><?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?><a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('api-logs', ['page' => $i])) ?>"><?= e((string) $i) ?></a><?php endfor; ?></div><?php endif; ?>
</div>
