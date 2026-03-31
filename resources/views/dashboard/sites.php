<div class="card">
    <div class="card-header split-header">
        <h2>Site List</h2>
        <div class="muted">Configured source websites</div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Name</th>
                    <th>Domain</th>
                    <th>Site Key</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sites)): ?>
                    <tr>
                        <td colspan="6" class="empty-cell">No sites found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sites as $site): ?>
                        <tr>
                            <td>#<?= e((string) $site['id']) ?></td>
                            <td><?= e($site['site_name']) ?></td>
                            <td><?= e($site['site_domain']) ?></td>
                            <td><?= e($site['site_key']) ?></td>
                            <td><?= e($site['status']) ?></td>
                            <td><?= e((string) $site['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
