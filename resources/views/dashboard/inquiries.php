<div class="card">
    <div class="card-header split-header">
        <h2>Inquiry List</h2>
        <div class="muted">Total: <?= e((string) $pagination['total']) ?></div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site</th>
                    <th>Title</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagination['data'])): ?>
                    <tr>
                        <td colspan="8" class="empty-cell">No inquiry records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['data'] as $item): ?>
                        <tr>
                            <td>#<?= e((string) $item['id']) ?></td>
                            <td><?= e($item['site_name'] ?: '-') ?></td>
                            <td><?= e($item['title'] ?: 'No title') ?></td>
                            <td><?= e($item['name'] ?: '-') ?></td>
                            <td><?= e($item['email'] ?: '-') ?></td>
                            <td><span class="status-pill status-<?= e($item['status']) ?>"><?= e(ucfirst((string) $item['status'])) ?></span></td>
                            <td><?= e((string) $item['created_at']) ?></td>
                            <td><a class="btn btn-sm" href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="<?= e(base_url('inquiries?page=' . $i)) ?>" class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>"><?= e((string) $i) ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
