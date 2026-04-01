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
                        <option value="<?= (int) $site['id'] ?>" <?= (int) ($filters['site_id'] ?? 0) === (int) $site['id'] ? 'selected' : '' ?>><?= e($site['site_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-label">
                <span>Owner</span>
                <select name="assigned_admin_id" class="form-input">
                    <option value="">All owners</option>
                    <option value="unassigned" <?= ($filters['assigned_admin_id'] ?? '') === 'unassigned' ? 'selected' : '' ?>>Unassigned</option>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?= (int) $admin['id'] ?>" <?= (string) ($filters['assigned_admin_id'] ?? '') === (string) $admin['id'] ? 'selected' : '' ?>><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="form-label">
                <span>Has Note</span>
                <select name="has_note" class="form-input">
                    <option value="">All</option>
                    <option value="yes" <?= ($filters['has_note'] ?? '') === 'yes' ? 'selected' : '' ?>>With note</option>
                    <option value="no" <?= ($filters['has_note'] ?? '') === 'no' ? 'selected' : '' ?>>Without note</option>
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
                <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search title, content, name, email, company or admin note...">
            </label>

            <div class="filter-actions full-width">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="<?= e(base_url('inquiries')) ?>" class="btn">Reset</a>
            </div>

            <div class="full-width export-box">
                <div class="split-header mb-12">
                    <h3 class="section-mini-title">CSV Export Fields</h3>
                    <a href="<?= e(url_with_query('inquiries/export', ['export_fields' => $selectedExportFields])) ?>" class="btn">Export CSV</a>
                </div>
                <div class="checkbox-grid">
                    <?php foreach ($allowedExportFields as $fieldKey => $expression): ?>
                        <label class="checkbox-row">
                            <input type="checkbox" name="export_fields[]" value="<?= e($fieldKey) ?>" <?= in_array($fieldKey, $selectedExportFields, true) ? 'checked' : '' ?>>
                            <span><?= e($fieldKey) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-20">
    <div class="card-header split-header">
        <h2>Export Templates</h2>
        <div class="muted">Save reusable filter + column presets</div>
    </div>
    <div class="card-body two-col-list gap-20">
        <div>
            <form method="post" action="<?= e(base_url('inquiries/export-template/create')) ?>" class="form-grid">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <?php foreach ($filters as $key => $value): ?>
                    <input type="hidden" name="<?= e($key) ?>" value="<?= e((string) $value) ?>">
                <?php endforeach; ?>
                <?php foreach ($selectedExportFields as $field): ?>
                    <input type="hidden" name="export_fields[]" value="<?= e($field) ?>">
                <?php endforeach; ?>
                <label class="form-label"><span>Template Name</span><input type="text" name="template_name" class="form-input" placeholder="Unread AU sample leads"></label>
                <label class="form-label"><span>Scope</span><select name="template_scope" class="form-input"><option value="personal">Personal</option><option value="shared">Shared</option></select></label>
                <div class="filter-actions"><button type="submit" class="btn btn-primary">Save Current View as Template</button></div>
            </form>
        </div>
        <div>
            <?php if (empty($exportTemplates)): ?>
                <p class="muted">No export templates saved yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($exportTemplates as $template): ?>
                        <div class="simple-list-item compact-item">
                            <div class="split-header"><div class="simple-list-title"><?= e($template['template_name']) ?></div><span class="<?= (int) $activeTemplateId === (int) $template['id'] ? 'badge-success' : 'badge-neutral' ?>"><?= (int) $activeTemplateId === (int) $template['id'] ? 'Applied' : e(ucfirst((string) $template['template_scope'])) ?></span></div>
                            <div class="simple-list-meta"><?= e(($template['admin_nickname'] ?: $template['admin_username']) ?: 'System') ?> · <?= e((string) $template['created_at']) ?></div>
                            <div class="inline-form mt-8">
                                <a class="btn btn-sm" href="<?= e(base_url('inquiries?template_id=' . (int) $template['id'])) ?>">Use Template</a>
                                <a class="btn btn-sm" href="<?= e(base_url('inquiries/export?template_id=' . (int) $template['id'])) ?>">Export</a>
                                <form method="post" action="<?= e(base_url('inquiries/export-template/delete')) ?>" class="inline-form">
                                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="template_id" value="<?= (int) $template['id'] ?>">
                                    <button type="submit" class="btn btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<form method="post" action="<?= e(base_url('inquiries/bulk')) ?>" class="card">
    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
    <div class="card-header split-header">
        <h2>Inquiry List</h2>
        <div class="muted">Total: <?= e((string) $pagination['total']) ?></div>
    </div>

    <div class="card-body filter-grid bulk-toolbar">
        <label class="form-label">
            <span>Bulk Action</span>
            <select id="bulkActionSelect" name="bulk_action" class="form-input">
                <option value="">Choose an action</option>
                <option value="mark_unread">Mark unread</option>
                <option value="mark_read">Mark read</option>
                <option value="mark_spam">Mark spam</option>
                <option value="move_trash">Move to trash</option>
                <option value="assign_selected">Assign owner</option>
                <option value="clear_assignee">Clear owner</option>
            </select>
        </label>
        <label class="form-label">
            <span>Owner</span>
            <select id="bulkAssigneeSelect" name="bulk_assigned_admin_id" class="form-input">
                <option value="">Choose owner</option>
                <?php foreach ($admins as $admin): ?>
                    <option value="<?= (int) $admin['id'] ?>"><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Run Bulk Action</button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><input id="selectAllRows" type="checkbox"></th>
                    <th>ID</th>
                    <th>Inquiry</th>
                    <th>Contact</th>
                    <th>Site</th>
                    <th>Owner</th>
                    <th>Follow-ups</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagination['data'])): ?>
                    <tr><td colspan="11" class="empty-cell">No inquiries found.</td></tr>
                <?php else: ?>
                    <?php foreach ($pagination['data'] as $item): ?>
                        <tr>
                            <td><input class="row-check" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>"></td>
                            <td>#<?= e((string) $item['id']) ?></td>
                            <td>
                                <div class="table-title"><?= e($item['title'] ?: 'No title') ?></div>
                                <div class="table-sub table-sub-clamp"><?= e(mb_strimwidth((string) $item['content'], 0, 100, '...')) ?></div>
                            </td>
                            <td>
                                <div class="table-title"><?= e($item['name']) ?></div>
                                <div class="table-sub"><?= e($item['email']) ?></div>
                                <?php if (!empty($item['phone'])): ?><div class="table-sub"><?= e($item['phone']) ?></div><?php endif; ?>
                            </td>
                            <td>
                                <div class="table-title"><?= e($item['site_name'] ?: 'Unknown site') ?></div>
                                <div class="table-sub"><?= e($item['form_key'] ?: '-') ?></div>
                            </td>
                            <td>
                                <div class="table-title"><?= e(($item['assigned_nickname'] ?: $item['assigned_username']) ?: 'Unassigned') ?></div>
                            </td>
                            <td>
                                <div class="table-title"><?= e((string) ($item['followup_count'] ?? 0)) ?></div>
                                <div class="table-sub"><?= e((string) ($item['last_followup_at'] ?: '-')) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($item['admin_note'])): ?>
                                    <span class="badge-success">Has note</span>
                                <?php else: ?>
                                    <span class="badge-neutral">No note</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?= e($item['status']) ?>"><?= e(ucfirst((string) $item['status'])) ?></span></td>
                            <td><div class="table-sub"><?= e((string) $item['created_at']) ?></div></td>
                            <td>
                                <div class="inline-form">
                                    <a href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>" class="btn btn-sm">View</a>
                                    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="back" value="<?= e('inquiries?' . current_query()) ?>">
                                        <select name="status" class="form-input form-input-sm">
                                            <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm">Update</button>
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
            <?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?>
                <a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('inquiries', ['page' => $i])) ?>"><?= e((string) $i) ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</form>
