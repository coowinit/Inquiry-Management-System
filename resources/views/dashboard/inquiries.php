<div class="card mb-20">
    <div class="card-header">
        <h2>Filters</h2>
    </div>
    <div class="card-body">
        <form method="get" action="<?= e(base_url('inquiries')) ?>" class="filter-grid">
            <label class="form-label">
                <span>Status</span>
                <select name="status" class="form-input">
                    <option value="">All statuses</option>
                    <?php foreach (['unread' => 'Unread', 'read' => 'Read', 'spam' => 'Spam', 'trash' => 'Trash'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-label">
                <span>Site</span>
                <select name="site_id" class="form-input">
                    <option value="">All sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= (int) $site['id'] ?>" <?= (int) ($filters['site_id'] ?? 0) === (int) $site['id'] ? 'selected' : '' ?>>
                            <?= e($site['site_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-label">
                <span>Date From</span>
                <input type="date" name="date_from" class="form-input" value="<?= e((string) ($filters['date_from'] ?? '')) ?>">
            </label>

            <label class="form-label">
                <span>Date To</span>
                <input type="date" name="date_to" class="form-input" value="<?= e((string) ($filters['date_to'] ?? '')) ?>">
            </label>

            <label class="form-label full-width">
                <span>Keyword</span>
                <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search title, content, name, email, company...">
            </label>

            <div class="filter-actions full-width">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="<?= e(base_url('inquiries')) ?>" class="btn">Reset</a>
            </div>
        </form>
    </div>
</div>

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
                    <th>Title / Content</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagination['data'])): ?>
                    <tr>
                        <td colspan="7" class="empty-cell">No inquiry records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagination['data'] as $item): ?>
                        <tr>
                            <td>#<?= e((string) $item['id']) ?></td>
                            <td>
                                <div class="table-title"><?= e($item['site_name'] ?: '-') ?></div>
                                <div class="table-sub"><?= e($item['form_key'] ?: 'general_form') ?></div>
                            </td>
                            <td>
                                <div class="table-title"><?= e($item['title'] ?: 'No title') ?></div>
                                <div class="table-sub table-sub-clamp"><?= e(mb_strimwidth((string) ($item['content'] ?? ''), 0, 90, '...')) ?></div>
                            </td>
                            <td>
                                <div class="table-title"><?= e($item['name'] ?: '-') ?></div>
                                <div class="table-sub"><?= e($item['email'] ?: '-') ?></div>
                            </td>
                            <td><span class="status-pill status-<?= e($item['status']) ?>"><?= e(ucfirst((string) $item['status'])) ?></span></td>
                            <td>
                                <div class="table-title"><?= e((string) $item['created_at']) ?></div>
                                <div class="table-sub"><?= e($item['ip'] ?: '-') ?></div>
                            </td>
                            <td>
                                <div class="action-stack">
                                    <a class="btn btn-sm" href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>">View</a>
                                    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $item['status'] === 'unread' ? 'read' : 'unread' ?>">
                                        <input type="hidden" name="back" value="<?= e('inquiries?' . current_query()) ?>">
                                        <button type="submit" class="btn btn-sm"><?= $item['status'] === 'unread' ? 'Mark Read' : 'Mark Unread' ?></button>
                                    </form>
                                    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="status" value="spam">
                                        <input type="hidden" name="back" value="<?= e('inquiries?' . current_query()) ?>">
                                        <button type="submit" class="btn btn-sm">Spam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="<?= e(url_with_query('inquiries', ['page' => $i])) ?>" class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>"><?= e((string) $i) ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
