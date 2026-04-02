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

$exportGroups = [
    'Common fields' => ['id', 'site_name', 'status', 'name', 'email', 'title', 'content', 'created_at'],
    'Contact fields' => ['country', 'phone', 'address', 'from_company'],
    'Source fields'  => ['form_key', 'source_url', 'referer_url', 'ip', 'browser', 'device_type', 'language', 'submitted_at'],
    'Internal fields'=> ['assigned_to', 'admin_note', 'updated_at', 'extra_data'],
];
?>

<div class="ims-page-grid ims-page-grid-tight">
    <div class="ims-stat-strip ims-stat-strip-compact">
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
                <div class="muted">Keep this area compact. Filter first, then export or bulk update the current view.</div>
            </div>
            <div class="ims-header-actions">
                <a href="#imsTemplates" class="btn btn-soft btn-sm">Templates</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="<?= e(base_url('inquiries')) ?>" class="ims-filter-form">
                <div class="ims-filter-grid-tight">
                    <label class="form-label mb-0">
                        <span>Status</span>
                        <select name="status" class="form-input">
                            <option value="">All statuses</option>
                            <?php foreach ($statusMap as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="form-label mb-0">
                        <span>Site</span>
                        <select name="site_id" class="form-input">
                            <option value="">All sites</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?= (int) $site['id'] ?>" <?= (int) ($filters['site_id'] ?? 0) === (int) $site['id'] ? 'selected' : '' ?>><?= e($site['site_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

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

                    <label class="form-label mb-0">
                        <span>Has note</span>
                        <select name="has_note" class="form-input">
                            <option value="">All</option>
                            <option value="yes" <?= ($filters['has_note'] ?? '') === 'yes' ? 'selected' : '' ?>>With note</option>
                            <option value="no" <?= ($filters['has_note'] ?? '') === 'no' ? 'selected' : '' ?>>Without note</option>
                        </select>
                    </label>

                    <label class="form-label mb-0">
                        <span>Date from</span>
                        <input type="date" name="date_from" class="form-input" value="<?= e((string) ($filters['date_from'] ?? '')) ?>">
                    </label>

                    <label class="form-label mb-0">
                        <span>Date to</span>
                        <input type="date" name="date_to" class="form-input" value="<?= e((string) ($filters['date_to'] ?? '')) ?>">
                    </label>

                    <label class="form-label mb-0 ims-filter-keyword">
                        <span>Keyword</span>
                        <input type="text" name="keyword" class="form-input" value="<?= e((string) ($filters['keyword'] ?? '')) ?>" placeholder="Search title, content, name, email, company or admin note...">
                    </label>

                    <div class="ims-filter-buttons">
                        <button type="submit" class="btn btn-primary">Apply filters</button>
                        <a href="<?= e(base_url('inquiries')) ?>" class="btn">Reset</a>
                    </div>
                </div>

                <div class="ims-export-panel mt-3">
                    <div class="split-header align-items-start mb-2">
                        <div>
                            <h3 class="section-mini-title mb-1">Export setup</h3>
                            <div class="muted">Common fields stay visible. Open the groups only when you need more columns.</div>
                        </div>
                        <button type="submit" formaction="<?= e(base_url('inquiries/export')) ?>" formmethod="get" class="btn btn-primary btn-sm">Export CSV</button>
                    </div>

                    <div class="ims-field-group-inline">
                        <?php foreach (($exportGroups['Common fields'] ?? []) as $fieldKey): ?>
                            <?php if (!array_key_exists($fieldKey, $allowedExportFields)) { continue; } ?>
                            <label class="checkbox-row ims-field-check">
                                <input type="checkbox" name="export_fields[]" value="<?= e($fieldKey) ?>" <?= in_array($fieldKey, $selectedExportFields, true) ? 'checked' : '' ?>>
                                <span><?= e($fieldKey) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <details class="ims-compact-details mt-3">
                        <summary>More fields</summary>
                        <div class="ims-field-group-stack mt-3">
                            <?php foreach ($exportGroups as $groupTitle => $groupFields): ?>
                                <?php if ($groupTitle === 'Common fields') { continue; } ?>
                                <div class="ims-field-group-card">
                                    <div class="ims-field-group-title"><?= e($groupTitle) ?></div>
                                    <div class="ims-checkbox-grid ims-checkbox-grid-tight">
                                        <?php foreach ($groupFields as $fieldKey): ?>
                                            <?php if (!array_key_exists($fieldKey, $allowedExportFields)) { continue; } ?>
                                            <label class="checkbox-row ims-field-check">
                                                <input type="checkbox" name="export_fields[]" value="<?= e($fieldKey) ?>" <?= in_array($fieldKey, $selectedExportFields, true) ? 'checked' : '' ?>>
                                                <span><?= e($fieldKey) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>
            </form>
        </div>
    </section>

    <section id="imsTemplates" class="card ims-card ims-card-compact">
        <div class="card-header split-header align-items-start">
            <div>
                <h2>Export templates</h2>
                <div class="muted">Save a reusable view without letting this block overpower the list.</div>
            </div>
            <span class="badge-neutral"><?= (int) count($exportTemplates) ?> saved</span>
        </div>
        <div class="card-body">
            <div class="ims-template-layout-compact">
                <div class="ims-soft-block">
                    <div class="page-section-overline">Create template</div>
                    <form method="post" action="<?= e(base_url('inquiries/export-template/create')) ?>" class="ims-template-form-compact">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <?php foreach ($filters as $key => $value): ?>
                            <input type="hidden" name="<?= e($key) ?>" value="<?= e((string) $value) ?>">
                        <?php endforeach; ?>
                        <?php foreach ($selectedExportFields as $field): ?>
                            <input type="hidden" name="export_fields[]" value="<?= e($field) ?>">
                        <?php endforeach; ?>

                        <label class="form-label mb-0">
                            <span>Template name</span>
                            <input type="text" name="template_name" class="form-input" placeholder="Unread AU sample leads">
                        </label>

                        <div class="ims-template-form-row">
                            <label class="form-label mb-0">
                                <span>Scope</span>
                                <select name="template_scope" class="form-input">
                                    <option value="personal">Personal</option>
                                    <option value="shared">Shared</option>
                                </select>
                            </label>
                            <button type="submit" class="btn btn-primary">Save template</button>
                        </div>
                    </form>
                </div>

                <div class="ims-soft-block">
                    <div class="split-header align-items-start mb-2">
                        <div class="page-section-overline mb-0">Saved templates</div>
                        <?php if (!empty($exportTemplates)): ?>
                            <div class="muted"><?= count($exportTemplates) ?> item<?= count($exportTemplates) === 1 ? '' : 's' ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($exportTemplates)): ?>
                        <p class="muted mb-0">No export templates saved yet.</p>
                    <?php else: ?>
                        <div class="ims-template-list ims-template-list-compact">
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
                                    <div class="template-actions mt-2">
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
                <div class="muted">Use the toolbar for bulk changes, then update each row inline only when needed.</div>
            </div>
            <div class="ims-table-count">Total <?= $visibleCount ?> · Page <?= $page ?>/<?= max(1, $totalPages) ?></div>
        </div>
        <div class="card-body">
            <div class="ims-toolbar ims-toolbar-tight">
                <form id="bulkInquiryForm" method="post" action="<?= e(base_url('inquiries/bulk')) ?>" class="ims-bulk-form ims-bulk-form-tight">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

                    <div class="ims-toolbar-field">
                        <label class="form-label mb-0">
                            <span>Bulk action</span>
                            <select id="bulkActionSelect" name="bulk_action" class="form-input form-input-sm">
                                <option value="">Choose an action</option>
                                <option value="mark_unread">Mark unread</option>
                                <option value="mark_read">Mark read</option>
                                <option value="mark_spam">Mark spam</option>
                                <option value="move_trash">Move to trash</option>
                                <option value="assign_selected">Assign owner</option>
                                <option value="clear_assignee">Clear owner</option>
                            </select>
                        </label>
                    </div>

                    <div id="bulkOwnerField" class="ims-toolbar-field d-none">
                        <label class="form-label mb-0">
                            <span>Owner</span>
                            <select id="bulkOwnerSelect" name="bulk_assigned_admin_id" class="form-input form-input-sm" disabled>
                                <option value="">Choose owner</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?= (int) $admin['id'] ?>"><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <div class="ims-toolbar-field ims-toolbar-meta">
                        <div class="form-label mb-0">
                            <span>Selected on this page</span>
                            <div id="bulkSelectionSummary" class="ims-selection-summary">0 rows selected</div>
                        </div>
                    </div>

                    <button id="bulkRunButton" type="submit" class="btn btn-primary ims-bulk-submit" disabled>Run bulk action</button>
                </form>
            </div>

            <div class="table-wrap ims-table-wrap mt-3">
                <table class="data-table ims-data-table ims-data-table-refined">
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
                                    <div class="ims-sub-line ims-sub-line-clamp"><?= e(mb_strimwidth((string) $item['content'], 0, 120, '...')) ?></div>
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
                                    <?php if (!empty($item['source_url'])): ?><div class="ims-sub-line ims-sub-line-break"><?= e($item['source_url']) ?></div><?php endif; ?>
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
                                    <div class="ims-sub-line ims-submitted-stack"><?= e((string) $item['created_at']) ?></div>
                                </td>
                                <td>
                                    <div class="ims-row-actions">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.getElementById('bulkInquiryForm');
    if (!bulkForm) return;

    const selectAll = document.getElementById('selectAllRows');
    const rowChecks = Array.from(document.querySelectorAll('.row-check'));
    const bulkAction = document.getElementById('bulkActionSelect');
    const ownerField = document.getElementById('bulkOwnerField');
    const ownerSelect = document.getElementById('bulkOwnerSelect');
    const runButton = document.getElementById('bulkRunButton');
    const summary = document.getElementById('bulkSelectionSummary');

    const getSelectedCount = () => rowChecks.filter(function (checkbox) {
        return checkbox.checked;
    }).length;

    const updateHeaderCheckbox = () => {
        const selectedCount = getSelectedCount();
        const totalCount = rowChecks.length;

        if (!selectAll) return;

        if (selectedCount === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        } else if (selectedCount === totalCount) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        }
    };

    const updateOwnerVisibility = () => {
        const needsOwner = bulkAction && bulkAction.value === 'assign_selected';
        if (!ownerField || !ownerSelect) return;

        ownerField.classList.toggle('d-none', !needsOwner);
        ownerSelect.disabled = !needsOwner;

        if (!needsOwner) {
            ownerSelect.value = '';
        }
    };

    const updateToolbarState = () => {
        const selectedCount = getSelectedCount();
        const action = bulkAction ? bulkAction.value : '';
        const needsOwner = action === 'assign_selected';
        const ownerReady = !needsOwner || (ownerSelect && ownerSelect.value !== '');

        if (summary) {
            summary.textContent = selectedCount + (selectedCount === 1 ? ' row selected' : ' rows selected');
        }

        if (runButton) {
            runButton.disabled = !(selectedCount > 0 && action && ownerReady);
        }

        updateHeaderCheckbox();
        updateOwnerVisibility();
    };

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateToolbarState();
        });
    }

    rowChecks.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateToolbarState);
    });

    if (bulkAction) {
        bulkAction.addEventListener('change', updateToolbarState);
    }

    if (ownerSelect) {
        ownerSelect.addEventListener('change', updateToolbarState);
    }

    bulkForm.addEventListener('submit', function (event) {
        const selectedCount = getSelectedCount();
        const action = bulkAction ? bulkAction.value : '';
        const needsOwner = action === 'assign_selected';
        const ownerReady = !needsOwner || (ownerSelect && ownerSelect.value !== '');

        if (!(selectedCount > 0 && action && ownerReady)) {
            event.preventDefault();
        }
    });

    updateToolbarState();
});
</script>

</div>
