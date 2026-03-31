<div class="card mb-20">
    <div class="card-header">
        <h2>Add Blocked IP</h2>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e(base_url('tools/blacklist-ips')) ?>" class="filter-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <label class="form-label">
                <span>IP Address</span>
                <input type="text" name="ip_address" class="form-input" placeholder="e.g. 203.0.113.15" required>
            </label>
            <label class="form-label full-width">
                <span>Reason</span>
                <input type="text" name="reason" class="form-input" placeholder="Optional note for future reference">
            </label>
            <div class="full-width">
                <button type="submit" class="btn btn-primary">Add Blocked IP</button>
            </div>
        </form>
    </div>
</div>

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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="empty-cell">No blocked IP records.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>#<?= e((string) $item['id']) ?></td>
                            <td><code><?= e($item['ip_address']) ?></code></td>
                            <td><?= e($item['reason'] ?: '-') ?></td>
                            <td><?= e((string) $item['created_at']) ?></td>
                            <td>
                                <form method="post" action="<?= e(base_url('tools/blacklist-ips/delete')) ?>" class="inline-form">
                                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
