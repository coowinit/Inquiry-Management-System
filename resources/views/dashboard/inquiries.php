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
$visibleCount = (int) ($pagination['total'] ?? 0);
$page = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
?>

<div class="ims-page-grid">
    <div class="ims-stat-strip">
        <div class="ims-stat-card">
            <div class="ims-stat-label">Active filters</div>
            <div class="ims-stat-value"><?= $activeFilterCount ?></div>
        </div>
        <div class="ims-stat-card">
            <div class="ims-stat-label">Visible inquiries</div>
            <div class="ims-stat-value"><?= $visibleCount ?></div>
        </div>
        <div class="ims-stat-card">
            <div class="ims-stat-label">Selected export columns</div>
            <div class="ims-stat-value"><?= count($selectedExportFields) ?></div>
        </div>
        <div class="ims-stat-card">
            <div class="ims-stat-label">Current page</div>
            <div class="ims-stat-value"><?= $page ?>/<?= max(1, $totalPages) ?></div>
        </div>
    </div>

    <section class="card ims-card">
        <div class="card-header split-header align-items-start">
            <div>
                <h2>Filters</h2>
                <div class="muted">Use the compact filter bar first, then export or bulk update the visible set.</div>
            </div>
            <div class="ims-header-actions">
                <a href="#imsTemplates" class="btn btn-soft btn-sm">Templates</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="<?= e(base_url('inquiries')) ?>" class="ims-filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label mb-0">
                            <span>Status</span>
                            <select name="status" class="form-input">
                                <option value="">All statuses</option>
                                <?php foreach ($statusMap as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label mb-0">
                            <span>Site</span>
                            <select name="site_id" class="form-input">
                                <option value="">All sites</option>
                                <?php foreach ($sites as $site): ?>
                                    <option value="<?= (int) $site['id'] ?>" <?= (int) ($filters['site_id'] ?? 0) === (int) $site['id'] ? 'selected' : '' ?>><?= e($site['site_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label mb-0">
                            <span>Owner</span>
                            <select name="assigned_admin_id" class="form-input">
                                <option value="">All owners</option>
                                <option value="unassigned" <?= ($filters['assigned_admin_id'] ?? '') === 'unassigned' ? 'selected' : '' ?>>Unassigned</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?= (int) $admin['id'] ?>" <?= (string) ($filters['assigned_admin_id'] ?? '') === (string) $admin['id'] ? 'selected' : '' ?>><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label mb-0">
                            <span>Has note</span>
                            <select name="has_note" class="form-input">
                                <option value="">All</option>
                                <option value="yes" <?= ($filters['has_note'] ?? '') === 'yes' ? 'selected' : '' ?>>With note</option>
                                <option value="no" <?= ($filters['has_note'] ?? '') === 'no' ? 'selected' : '' ?>>Without note</option>
                            </select>
                        </label>
                    </div>

                    <div class="col-xl-2 col-md-6">
                        <label class="form-label mb-0">
                            <span>Date from</span>
                            <input type="date" name="date_from" class="form-input" value="<?= e((string) ($filters['date_from'] ?? '')) ?>">
                        </label>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <label class="form-label mb-0">
                            <span>Date to</span>
                            <input type="date" name="date_to" class="form-input" value="<?= e((string) ($filters['date_to'] ?? '')) ?>">
                        </label>
                    </div>
                    <div class="col-xl-5 col-md-12">
                        <label class="form-label mb-0">
                            <span>Keyword</span>
                            <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search title, content, name, email, company or admin note...">
                        </label>
                    </div>
                    <div class="col-xl-3 col-md-12">
                        <div class="ims-inline-actions">
                            <button type="submit" class="btn btn-primary flex-fill">Apply filters</button>
                            <a href="<?= e(base_url('inquiries')) ?>" class="btn flex-fill">Reset</a>
                        </div>
                    </div>
                </div>

                <div class="ims-export-panel mt-4">
                    <div class="split-header align-items-start mb-3">
                        <div>
                            <h3 class="section-mini-title mb-1">Export setup</h3>
                            <div class="muted">Pick only the CSV columns you need for this view.</div>
                        </div>
                        <button type="submit" formaction="<?= e(base_url('inquiries/export')) ?>" formmethod="get" class="btn btn-primary">Export CSV</button>
                    </div>
                    <div class="ims-checkbox-grid">
                        <?php foreach ($allowedExportFields as $fieldKey => $expression): ?>
                            <label class="checkbox-row ims-field-check">
                                <input type="checkbox" name="export_fields[]" value="<?= e($fieldKey) ?>" <?= in_array($fieldKey, $selectedExportFields, true) ? 'checked' : '' ?>>
                                <span><?= e($fieldKey) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section id="imsTemplates" class="card ims-card">
        <div class="card-header split-header align-items-start">
            <div>
                <h2>Export templates</h2>
                <div class="muted">Save your current filter and column setup as a reusable preset.</div>
            </div>
            <span class="badge-neutral"><?= (int) count($exportTemplates) ?> saved</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="ims-soft-block h-100">
                        <div class="page-section-overline">Create template</div>
                        <form method="post" action="<?= e(base_url('inquiries/export-template/create')) ?>" class="row g-3 align-items-end">
                            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                            <?php foreach ($filters as $key => $value): ?>
                                <input type="hidden" name="<?= e($key) ?>" value="<?= e((string) $value) ?>">
                            <?php endforeach; ?>
                            <?php foreach ($selectedExportFields as $field): ?>
                                <input type="hidden" name="export_fields[]" value="<?= e($field) ?>">
                            <?php endforeach; ?>

                            <div class="col-12">
                                <label class="form-label mb-0">
                                    <span>Template name</span>
                                    <input type="text" name="template_name" class="form-input" placeholder="Unread AU sample leads">
                                </label>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label mb-0">
                                    <span>Scope</span>
                                    <select name="template_scope" class="form-input">
                                        <option value="personal">Personal</option>
                                        <option value="shared">Shared</option>
                                    </select>
                                </label>
                            </div>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-primary w-100">Save template</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-7">
                    <?php if (empty($exportTemplates)): ?>
                        <div class="ims-soft-block h-100 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="page-section-overline">Saved templates</div>
                                <p class="muted mb-0">No export templates saved yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="ims-template-list">
                            <?php foreach ($exportTemplates as $template): ?>
                                <div class="ims-template-item <?= (int) $activeTemplateId === (int) $template['id'] ? 'is-active' : '' ?>">
                                    <div class="split-header align-items-start gap-3">
                                        <div>
                                            <div class="table-title mb-1"><?= e($template['template_name']) ?></div>
                                            <div class="simple-list-meta"><?= e(($template['admin_nickname'] ?: $template['admin_username']) ?: 'System') ?> · <?= e((string) $template['created_at']) ?></div>
                                        </div>
                                        <span class="<?= (int) $activeTemplateId === (int) $template['id'] ? 'badge-success' : 'badge-neutral' ?>">
                                            <?= (int) $activeTemplateId === (int) $template['id'] ? 'Applied' : e(ucfirst((string) $template['template_scope'])) ?>
                                        </span>
                                    </div>
                                    <div class="template-actions mt-3">
                                        <a class="btn btn-sm" href="<?= e(base_url('inquiries?template_id=' . (int) $template['id'])) ?>">Use</a>
                                        <a class="btn btn-sm btn-soft" href="<?= e(base_url('inquiries/export?template_id=' . (int) $template['id'])) ?>">Export</a>
                                        <form method="post" action="<?= e(base_url('inquiries/export-template/delete')) ?>">
                                            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $template['id'] ?>">
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
    </section>

    <section class="card ims-card inquiry-table-card">
        <div class="card-header split-header align-items-start">
            <div>
                <h2>Inquiry list</h2>
                <div class="muted">Use the toolbar for bulk actions, then review each inquiry row in place.</div>
            </div>
            <div class="ims-table-count">Total <?= $visibleCount ?> · Page <?= $page ?>/<?= max(1, $totalPages) ?></div>
        </div>
        <div class="card-body">
            <div class="ims-toolbar">
                <form id="bulkInquiryForm" method="post" action="<?= e(base_url('inquiries/bulk')) ?>" class="ims-bulk-form">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label class="form-label mb-0">
                                <span>Bulk action</span>
                                <select name="bulk_action" class="form-input">
                                    <option value="">Choose an action</option>
                                    <option value="mark_unread">Mark unread</option>
                                    <option value="mark_read">Mark read</option>
                                    <option value="mark_spam">Mark spam</option>
                                    <option value="move_trash">Move to trash</option>
                                    <option value="assign_owner">Assign owner</option>
                                    <option value="clear_owner">Clear owner</option>
                                </select>
                            </label>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <label class="form-label mb-0">
                                <span>Owner</span>
                                <select name="assigned_admin_id" class="form-input">
                                    <option value="">Choose owner</option>
                                    <?php foreach ($admins as $admin): ?>
                                        <option value="<?= (int) $admin['id'] ?>"><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="col-xl-4 col-md-12">
                            <button type="submit" class="btn btn-primary w-100">Run bulk action</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-wrap ims-table-wrap mt-4">
                <table class="data-table ims-data-table">
                    <thead>
                        <tr>
                            <th class="checkbox-cell"><input id="selectAllRows" type="checkbox"></th>
                            <th>ID</th>
                            <th>Inquiry</th>
                            <th>Contact</th>
                            <th>Site</th>
                            <th>Owner</th>
                            <th>Follow-up</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($pagination['data'])): ?>
                        <tr><td colspan="10" class="empty-cell">No inquiries found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pagination['data'] as $item): ?>
                            <?php $rowFormId = 'rowStatusForm' . (int) $item['id']; ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input class="row-check" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>" form="bulkInquiryForm">
                                </td>
                                <td>
                                    <a class="inquiry-id-link" href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>">#<?= (int) $item['id'] ?></a>
                                </td>
                                <td>
                                    <div class="ims-main-line"><?= e($item['title'] ?: 'No title') ?></div>
                                    <div class="ims-sub-line"><?= e(mb_strimwidth((string) $item['content'], 0, 120, '...')) ?></div>
                                    <div class="ims-inline-badges mt-2">
                                        <?= !empty($item['admin_note']) ? '<span class="badge-success">Has note</span>' : '<span class="badge-neutral">No note</span>' ?>
                                        <?php if (!empty($item['form_key'])): ?><span class="badge-neutral"><?= e($item['form_key']) ?></span><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="ims-main-line"><?= e($item['name']) ?></div>
                                    <div class="ims-sub-line"><?= e($item['email']) ?></div>
                                    <?php if (!empty($item['phone'])): ?><div class="ims-sub-line"><?= e($item['phone']) ?></div><?php endif; ?>
                                </td>
                                <td>
                                    <div class="ims-main-line"><?= e($item['site_name'] ?: 'Unknown site') ?></div>
                                    <div class="ims-sub-line"><?= e($item['source_url'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <div class="ims-main-line"><?= e(($item['assigned_nickname'] ?: $item['assigned_username']) ?: 'Unassigned') ?></div>
                                </td>
                                <td>
                                    <div class="ims-main-line"><?= (int) ($item['followup_count'] ?? 0) ?> record<?= (int) ($item['followup_count'] ?? 0) === 1 ? '' : 's' ?></div>
                                    <div class="ims-sub-line"><?= e((string) ($item['last_followup_at'] ?: '-')) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($item['status']) ?>"><?= e(ucfirst((string) $item['status'])) ?></span>
                                </td>
                                <td>
                                    <div class="ims-sub-line"><?= e((string) $item['created_at']) ?></div>
                                </td>
                                <td>
                                    <div class="ims-action-stack">
                                        <a href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>" class="btn btn-sm btn-soft">View</a>
                                        <form id="<?= e($rowFormId) ?>" method="post" action="<?= e(base_url('inquiry/status')) ?>" class="ims-row-status-form">
                                            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                            <input type="hidden" name="back" value="<?= e('inquiries?' . current_query()) ?>">
                                            <select name="status" class="form-input form-input-sm">
                                                <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                                                    <option value="<?= e($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm">Save</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination mt-4">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="page-link <?= $i === $page ? 'is-active' : '' ?>" href="<?= e(url_with_query('inquiries', ['page' => $i])) ?>"><?= e((string) $i) ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
