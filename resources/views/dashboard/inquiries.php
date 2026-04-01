<?php
$statusMap = [
    'unread' => 'Unread',
    'read'   => 'Read',
    'spam'   => 'Spam',
    'trash'  => 'Trash',
];
$activeFilterCount = 0;
foreach (['status', 'site_id', 'assigned_admin_id', 'has_note', 'date_from', 'date_to', 'keyword'] as $key) {
    if (!empty($filters[$key])) {
        $activeFilterCount++;
    }
}
?>

<div class="inquiry-page-grid">
    <section class="card">
        <div class="card-header split-header">
            <div>
                <h2>Filters &amp; Export Setup</h2>
                <div class="muted">Compact filtering, export field control, and reusable templates in one place.</div>
            </div>
            <div class="filter-summary">
                <span class="filter-chip">Active filters <strong><?= (int) $activeFilterCount ?></strong></span>
                <span class="filter-chip">Selected columns <strong><?= (int) count($selectedExportFields) ?></strong></span>
                <span class="filter-chip">Visible results <strong><?= (int) $pagination['total'] ?></strong></span>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="<?= e(base_url('inquiries')) ?>" class="inquiry-filters-grid">
                <label class="form-label">
                    <span>Status</span>
                    <select name="status" class="form-input">
                        <option value="">All statuses</option>
                        <?php foreach ($statusMap as $value => $label): ?>
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

                <label class="form-label wide-2">
                    <span>Keyword</span>
                    <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search title, content, name, email, company or admin note...">
                </label>

                <div class="filter-actions wide-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="<?= e(base_url('inquiries')) ?>" class="btn">Reset</a>
                    <a href="#exportTemplatesCard" class="btn btn-soft">Jump to Templates</a>
                </div>

                <div class="full-width export-box csv-fields-bar">
                    <div class="split-header">
                        <div>
                            <h3 class="section-mini-title">CSV Export Fields</h3>
                            <div class="muted">Choose only the columns you want in the export file.</div>
                        </div>
                        <a href="<?= e(url_with_query('inquiries/export', ['export_fields' => $selectedExportFields])) ?>" class="btn btn-primary">Export CSV</a>
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
    </section>

    <section id="exportTemplatesCard" class="card">
        <div class="card-header split-header">
            <div>
                <h2>Export Templates</h2>
                <div class="muted">Save your current filter + column view as a reusable preset.</div>
            </div>
            <span class="badge-neutral"><?= (int) count($exportTemplates) ?> template<?= count($exportTemplates) === 1 ? '' : 's' ?></span>
        </div>
        <div class="card-body inquiry-tools-grid">
            <div class="soft-panel">
                <div class="page-section-overline">Create Template</div>
                <form method="post" action="<?= e(base_url('inquiries/export-template/create')) ?>" class="template-form-grid">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <?php foreach ($filters as $key => $value): ?>
                        <input type="hidden" name="<?= e($key) ?>" value="<?= e((string) $value) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($selectedExportFields as $field): ?>
                        <input type="hidden" name="export_fields[]" value="<?= e($field) ?>">
                    <?php endforeach; ?>

                    <label class="form-label">
                        <span>Template Name</span>
                        <input type="text" name="template_name" class="form-input" placeholder="Unread AU sample leads">
                    </label>
                    <label class="form-label">
                        <span>Scope</span>
                        <select name="template_scope" class="form-input">
                            <option value="personal">Personal</option>
                            <option value="shared">Shared</option>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </form>
            </div>

            <div>
                <?php if (empty($exportTemplates)): ?>
                    <div class="soft-panel">
                        <div class="page-section-overline">Saved Templates</div>
                        <p class="muted mb-0">No export templates saved yet.</p>
                    </div>
                <?php else: ?>
                    <div class="simple-list compact-list">
                        <?php foreach ($exportTemplates as $template): ?>
                            <div class="simple-list-item compact-item">
                                <div class="split-header">
                                    <div>
                                        <div class="simple-list-title"><?= e($template['template_name']) ?></div>
                                        <div class="simple-list-meta"><?= e(($template['admin_nickname'] ?: $template['admin_username']) ?: 'System') ?> · <?= e((string) $template['created_at']) ?></div>
                                    </div>
                                    <span class="<?= (int) $activeTemplateId === (int) $template['id'] ? 'badge-success' : 'badge-neutral' ?>">
                                        <?= (int) $activeTemplateId === (int) $template['id'] ? 'Applied' : e(ucfirst((string) $template['template_scope'])) ?>
                                    </span>
                                </div>
                                <div class="template-actions">
                                    <a class="btn btn-sm" href="<?= e(base_url('inquiries?template_id=' . (int) $template['id'])) ?>">Use</a>
                                    <a class="btn btn-sm btn-soft" href="<?= e(base_url('inquiries/export?template_id=' . (int) $template['id'])) ?>">Export</a>
                                    <form method="post" action="<?= e(base_url('inquiries/export-template/delete')) ?>">
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
    </section>

    <section class="card inquiry-table-card">
        <form id="bulkInquiryForm" method="post" action="<?= e(base_url('inquiries/bulk')) ?>" class="hidden-form">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        </form>

        <div class="card-header inquiry-table-toolbar">
            <div>
                <h2>Inquiry List</h2>
                <div class="muted">A denser table layout for faster review, assignment, and status updates.</div>
            </div>
            <div class="filter-summary">
                <span class="filter-chip">Total <strong><?= (int) $pagination['total'] ?></strong></span>
                <span class="filter-chip">Page <strong><?= (int) $pagination['page'] ?>/<?= (int) $pagination['total_pages'] ?></strong></span>
            </div>
        </div>

        <div class="card-body">
            <div class="inquiry-table-toolbar mb-20">
                <div class="inquiry-table-toolbar-left">
                    <label class="form-label toolbar-field">
                        <span>Bulk Action</span>
                        <select id="bulkActionSelect" name="bulk_action" class="form-input" form="bulkInquiryForm">
                            <option value="">Choose an action</option>
                            <option value="mark_unread">Mark unread</option>
                            <option value="mark_read">Mark read</option>
                            <option value="mark_spam">Mark spam</option>
                            <option value="move_trash">Move to trash</option>
                            <option value="assign_selected">Assign owner</option>
                            <option value="clear_assignee">Clear owner</option>
                        </select>
                    </label>

                    <label class="form-label toolbar-field">
                        <span>Owner</span>
                        <select id="bulkAssigneeSelect" name="bulk_assigned_admin_id" class="form-input" form="bulkInquiryForm">
                            <option value="">Choose owner</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?= (int) $admin['id'] ?>"><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" form="bulkInquiryForm">Run Bulk Action</button>
                    </div>
                </div>
                <div class="muted">Tip: use filters first, then bulk-update the visible set.</div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="checkbox-cell"><input id="selectAllRows" type="checkbox"></th>
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
                            <?php $rowFormId = 'rowStatusForm' . (int) $item['id']; ?>
                            <tr>
                                <td class="checkbox-cell"><input class="row-check" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>" form="bulkInquiryForm"></td>
                                <td><a class="inquiry-id-link" href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>">#<?= (int) $item['id'] ?></a></td>
                                <td>
                                    <div class="table-title"><?= e($item['title'] ?: 'No title') ?></div>
                                    <div class="table-sub table-sub-clamp"><?= e(mb_strimwidth((string) $item['content'], 0, 120, '...')) ?></div>
                                </td>
                                <td>
                                    <div class="contact-stack">
                                        <div class="table-title"><?= e($item['name']) ?></div>
                                        <div class="table-sub"><?= e($item['email']) ?></div>
                                        <?php if (!empty($item['phone'])): ?><div class="table-sub"><?= e($item['phone']) ?></div><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="site-stack">
                                        <div class="table-title"><?= e($item['site_name'] ?: 'Unknown site') ?></div>
                                        <div class="table-sub"><?= e($item['form_key'] ?: '-') ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-title"><?= e(($item['assigned_nickname'] ?: $item['assigned_username']) ?: 'Unassigned') ?></div>
                                </td>
                                <td>
                                    <div class="followup-stack">
                                        <div class="table-title"><?= (int) ($item['followup_count'] ?? 0) ?></div>
                                        <div class="table-sub"><?= e((string) ($item['last_followup_at'] ?: '-')) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?= !empty($item['admin_note']) ? '<span class="badge-success">Has note</span>' : '<span class="badge-neutral">No note</span>' ?>
                                </td>
                                <td><span class="status-badge status-<?= e($item['status']) ?>"><?= e(ucfirst((string) $item['status'])) ?></span></td>
                                <td>
                                    <div class="table-sub"><?= e((string) $item['created_at']) ?></div>
                                </td>
                                <td>
                                    <div class="action-cluster">
                                        <div class="table-inline-meta">
                                            <a href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>" class="btn btn-sm btn-soft">View</a>
                                        </div>
                                        <form id="<?= e($rowFormId) ?>" method="post" action="<?= e(base_url('inquiry/status')) ?>" class="row-status-form">
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
        </div>

        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?>
                    <a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('inquiries', ['page' => $i])) ?>"><?= e((string) $i) ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
