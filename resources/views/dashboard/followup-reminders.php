<div class="stats-grid stats-grid-three mb-20">
    <div class="card stat-card"><div class="stat-label">Overdue</div><div class="stat-value"><?= e((string) ($stats['overdue_count'] ?? 0)) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Due Today</div><div class="stat-value"><?= e((string) ($stats['today_count'] ?? 0)) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Next 7 Days</div><div class="stat-value"><?= e((string) ($stats['next7_count'] ?? 0)) ?></div></div>
</div>

<div class="card mb-20">
    <div class="card-header"><h2>Reminder Filters</h2></div>
    <div class="card-body">
        <form method="get" action="<?= e(base_url('followup-reminders')) ?>" class="filter-grid">
            <?php if (in_array($userRole, ['admin', 'manager'], true)): ?>
            <label class="form-label">
                <span>Scope</span>
                <select name="scope" class="form-input">
                    <option value="all" <?= ($filters['scope'] ?? 'all') === 'all' ? 'selected' : '' ?>>All follow-ups</option>
                    <option value="mine" <?= ($filters['scope'] ?? '') === 'mine' ? 'selected' : '' ?>>Only mine</option>
                </select>
            </label>
            <?php endif; ?>
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
                <span>Timing</span>
                <select name="timing" class="form-input">
                    <?php foreach (['open_only' => 'All open', 'overdue' => 'Overdue', 'today' => 'Due today', 'next7' => 'Next 7 days', 'unscheduled' => 'Unscheduled'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($filters['timing'] ?? 'open_only') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="form-label checkbox-label">
                <span>Options</span>
                <label class="checkbox-row"><input type="checkbox" name="include_completed" value="1" <?= !empty($filters['include_completed']) ? 'checked' : '' ?>> Include completed</label>
            </label>
            <label class="form-label full-width"><span>Keyword</span><input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search contact name, email, title or follow-up content..."></label>
            <div class="filter-actions full-width"><button type="submit" class="btn btn-primary">Apply Filters</button><a href="<?= e(base_url('followup-reminders')) ?>" class="btn">Reset</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header split-header"><h2>Reminder Queue</h2><div class="muted">Total: <?= e((string) $pagination['total']) ?></div></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Inquiry</th><th>Site</th><th>Owner</th><th>Type</th><th>Next Contact</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($pagination['data'])): ?>
                    <tr><td colspan="8" class="empty-cell">No follow-up reminders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($pagination['data'] as $row): ?>
                        <?php $isOverdue = !empty($row['next_contact_at']) && strtotime((string) $row['next_contact_at']) < time() && empty($row['is_completed']); ?>
                        <?php $isToday = !empty($row['next_contact_at']) && date('Y-m-d', strtotime((string) $row['next_contact_at'])) === date('Y-m-d'); ?>
                        <tr>
                            <td>#<?= e((string) $row['id']) ?></td>
                            <td>
                                <div class="table-title"><?= e($row['name'] ?: ('Inquiry #' . $row['inquiry_id'])) ?></div>
                                <div class="table-sub"><?= e($row['email'] ?: '-') ?></div>
                                <div class="table-sub table-sub-clamp"><?= e(mb_strimwidth((string) $row['content'], 0, 120, '...')) ?></div>
                            </td>
                            <td><div class="table-title"><?= e($row['site_name'] ?: 'Unknown site') ?></div><div class="table-sub"><?= e($row['form_key'] ?: '-') ?></div></td>
                            <td><?= e(($row['admin_nickname'] ?: $row['admin_username']) ?: 'Unassigned') ?></td>
                            <td><?= e(ucfirst((string) $row['followup_type'])) ?></td>
                            <td><?= e((string) ($row['next_contact_at'] ?: '-')) ?></td>
                            <td>
                                <?php if (!empty($row['is_completed'])): ?>
                                    <span class="badge-success">Completed</span>
                                <?php elseif ($isOverdue): ?>
                                    <span class="badge-warning">Overdue</span>
                                <?php elseif ($isToday): ?>
                                    <span class="badge-neutral">Today</span>
                                <?php else: ?>
                                    <span class="badge-neutral">Open</span>
                                <?php endif; ?>
                            </td>
                            <td><a class="btn btn-sm" href="<?= e(base_url('inquiry?id=' . (int) $row['inquiry_id'])) ?>">Open Inquiry</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?><div class="pagination"><?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?><a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('followup-reminders', ['page' => $i])) ?>"><?= e((string) $i) ?></a><?php endfor; ?></div><?php endif; ?>
</div>
