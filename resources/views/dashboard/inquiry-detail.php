<?php
$logPreview = array_slice($logs ?? [], 0, 3);
$remainingLogs = max(0, count($logs ?? []) - count($logPreview));
?>

<div class="ims-back-row">
    <a href="<?= e(base_url('inquiries')) ?>" class="btn btn-soft">← Back to list</a>
</div>

<section class="card ims-detail-hero ims-detail-hero-polished">
    <div class="card-body p-0">
        <div class="ims-detail-hero-grid ims-detail-hero-grid-polished">
            <div class="ims-detail-main">
                <div class="page-section-overline">Inquiry summary</div>
                <h2 class="ims-detail-title"><?= e($inquiry['title'] ?: 'Untitled inquiry') ?></h2>
                <p class="ims-detail-lead"><?= e($inquiry['content'] ? mb_strimwidth((string) $inquiry['content'], 0, 220, '...') : 'No content.') ?></p>

                <div class="ims-hero-contact-grid ims-hero-contact-grid-polished">
                    <div class="ims-hero-contact-card">
                        <div class="ims-hero-label">Contact</div>
                        <div class="ims-hero-value"><?= e($inquiry['name']) ?></div>
                        <div class="ims-hero-meta"><?= e($inquiry['email']) ?><?= !empty($inquiry['phone']) ? ' · ' . e($inquiry['phone']) : '' ?></div>
                    </div>
                    <div class="ims-hero-contact-card">
                        <div class="ims-hero-label">Site &amp; form</div>
                        <div class="ims-hero-value"><?= e($inquiry['site_name'] ?: '-') ?></div>
                        <div class="ims-hero-meta"><?= e($inquiry['form_key'] ?: '-') ?></div>
                    </div>
                    <div class="ims-hero-contact-card">
                        <div class="ims-hero-label">Submitted</div>
                        <div class="ims-hero-value"><?= e((string) ($inquiry['submitted_at'] ?: $inquiry['created_at'])) ?></div>
                        <div class="ims-hero-meta">Updated <?= e((string) $inquiry['updated_at']) ?></div>
                    </div>
                </div>
            </div>

            <div class="ims-detail-side">
                <div class="ims-side-summary-card ims-side-summary-card-polished">
                    <div class="ims-side-summary-head">
                        <span class="status-badge status-<?= e($inquiry['status']) ?>"><?= e(ucfirst((string) $inquiry['status'])) ?></span>
                        <div class="ims-side-summary-id">Inquiry #<?= (int) $inquiry['id'] ?></div>
                    </div>
                    <div class="ims-side-summary-list">
                        <div>
                            <span>Assigned owner</span>
                            <strong><?= e(($inquiry['assigned_nickname'] ?: $inquiry['assigned_username']) ?: 'Unassigned') ?></strong>
                        </div>
                        <div>
                            <span>Company</span>
                            <strong><?= e($inquiry['from_company'] ?: '-') ?></strong>
                        </div>
                        <div>
                            <span>Country</span>
                            <strong><?= e($inquiry['country'] ?: '-') ?></strong>
                        </div>
                    </div>
                    <div class="ims-side-summary-actions">
                        <button type="button" class="btn btn-primary" data-copy-text="<?= e($copyBlocks['contact_summary']) ?>">Copy contact</button>
                        <button type="button" class="btn" data-copy-text="<?= e($copyBlocks['reply_draft']) ?>">Copy reply draft</button>
                        <button type="button" class="btn btn-soft" data-copy-text="<?= e($copyBlocks['json_snapshot']) ?>">Copy JSON</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="ims-detail-layout ims-detail-layout-polished">
    <div class="ims-detail-primary">
        <section class="card ims-card ims-card-compact">
            <div class="card-header">
                <div class="page-section-overline">Customer message</div>
                <h2>Inquiry content</h2>
            </div>
            <div class="card-body">
                <div class="ims-content-box ims-content-box-polished"><?= nl2br(e($inquiry['content'] ?: '-')) ?></div>
            </div>
        </section>

        <?php if ($canUpdate): ?>
            <section class="card ims-card ims-card-compact">
                <div class="card-header">
                    <div class="page-section-overline">Internal note</div>
                    <h2>Admin note</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= e(base_url('inquiry/note')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <div class="col-12">
                            <label class="form-label mb-0">
                                <span>Admin note</span>
                                <textarea name="admin_note" class="form-input ims-note-input" rows="5" placeholder="Add internal summary, priority or customer context..."><?= e((string) ($inquiry['admin_note'] ?? '')) ?></textarea>
                            </label>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save note</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section class="card ims-card ims-card-compact">
            <div class="card-header split-header align-items-start">
                <div>
                    <div class="page-section-overline">Follow-up history</div>
                    <h2>Follow-up timeline</h2>
                </div>
                <span class="badge-neutral"><?= (int) count($followups) ?> record<?= count($followups) === 1 ? '' : 's' ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($followups)): ?>
                    <p class="muted mb-0">No follow-up records yet.</p>
                <?php else: ?>
                    <div class="ims-timeline ims-timeline-polished">
                        <?php foreach ($followups as $followup): ?>
                            <article class="ims-timeline-item">
                                <div class="ims-timeline-dot <?= !empty($followup['is_completed']) ? 'is-done' : '' ?>"></div>
                                <div class="ims-timeline-card ims-timeline-card-polished">
                                    <div class="split-header align-items-start gap-3">
                                        <div>
                                            <div class="table-title mb-1"><?= e(ucfirst((string) $followup['followup_type'])) ?></div>
                                            <div class="simple-list-meta"><?= e($followup['admin_nickname'] ?: $followup['admin_username'] ?: 'System') ?> · <?= e((string) $followup['created_at']) ?></div>
                                        </div>
                                        <span class="<?= !empty($followup['is_completed']) ? 'badge-success' : 'badge-neutral' ?>">
                                            <?= !empty($followup['is_completed']) ? 'Completed' : 'Open' ?>
                                        </span>
                                    </div>

                                    <div class="ims-timeline-meta mt-2">
                                        <?php if (!empty($followup['next_contact_at'])): ?><span>Next: <?= e((string) $followup['next_contact_at']) ?></span><?php endif; ?>
                                        <?php if (!empty($followup['completed_at'])): ?><span>Completed: <?= e((string) $followup['completed_at']) ?></span><?php endif; ?>
                                    </div>
                                    <div class="ims-timeline-content mt-3"><?= nl2br(e((string) $followup['content'])) ?></div>

                                    <?php if ($canUpdate): ?>
                                        <details class="ims-edit-panel mt-3">
                                            <summary>Edit follow-up</summary>
                                            <form method="post" action="<?= e(base_url('inquiry/followup/update')) ?>" class="row g-3 mt-1">
                                                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                                                <input type="hidden" name="followup_id" value="<?= (int) $followup['id'] ?>">
                                                <div class="col-md-6">
                                                    <label class="form-label mb-0">
                                                        <span>Type</span>
                                                        <select name="followup_type" class="form-input">
                                                            <?php foreach (['note', 'email', 'call', 'meeting', 'todo', 'status'] as $type): ?>
                                                                <option value="<?= e($type) ?>" <?= $followup['followup_type'] === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label mb-0">
                                                        <span>Next contact at</span>
                                                        <input type="datetime-local" name="next_contact_at" class="form-input" value="<?= !empty($followup['next_contact_at']) ? e(date('Y-m-d\TH:i', strtotime((string) $followup['next_contact_at']))) : '' ?>">
                                                    </label>
                                                </div>
                                                <div class="col-12">
                                                    <label class="checkbox-row">
                                                        <input type="checkbox" name="is_completed" value="1" <?= !empty($followup['is_completed']) ? 'checked' : '' ?>>
                                                        <span>Completed</span>
                                                    </label>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label mb-0">
                                                        <span>Content</span>
                                                        <textarea name="content" class="form-input" rows="4"><?= e((string) $followup['content']) ?></textarea>
                                                    </label>
                                                </div>
                                                <div class="col-12 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                                    <button type="submit" class="btn btn-primary btn-sm">Save follow-up</button>
                                                </div>
                                            </form>
                                            <form method="post" action="<?= e(base_url('inquiry/followup/toggle')) ?>" class="mt-2">
                                                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                                                <input type="hidden" name="followup_id" value="<?= (int) $followup['id'] ?>">
                                                <input type="hidden" name="complete" value="<?= !empty($followup['is_completed']) ? '0' : '1' ?>">
                                                <button type="submit" class="btn btn-sm"><?= !empty($followup['is_completed']) ? 'Reopen' : 'Mark completed' ?></button>
                                            </form>
                                        </details>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($canUpdate): ?>
            <section class="card ims-card ims-card-compact">
                <div class="card-header">
                    <div class="page-section-overline">Next action</div>
                    <h2>Add follow-up</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= e(base_url('inquiry/followup')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <div class="col-md-4">
                            <label class="form-label mb-0">
                                <span>Type</span>
                                <select name="followup_type" class="form-input">
                                    <?php foreach (['note', 'email', 'call', 'meeting', 'todo', 'status'] as $type): ?>
                                        <option value="<?= e($type) ?>"><?= e(ucfirst($type)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-0">
                                <span>Next contact at</span>
                                <input type="datetime-local" name="next_contact_at" class="form-input">
                            </label>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="checkbox-row w-100 justify-content-center">
                                <input type="checkbox" name="is_completed" value="1">
                                <span>Mark completed</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-0">
                                <span>Follow-up content</span>
                                <textarea name="content" class="form-input" rows="4" placeholder="Write what happened, what to send next, or the next action item..."></textarea>
                            </label>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Add follow-up</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <aside class="ims-detail-secondary ims-sticky-column">
        <?php if ($canUpdate): ?>
            <section class="card ims-card ims-card-compact">
                <div class="card-header">
                    <div class="page-section-overline">Quick actions</div>
                    <h2>Owner &amp; status</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= e(base_url('inquiry/assign')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <div class="col-12">
                            <label class="form-label mb-0">
                                <span>Assigned owner</span>
                                <select name="assigned_admin_id" class="form-input">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($admins as $admin): ?>
                                        <option value="<?= (int) $admin['id'] ?>" <?= (int) ($inquiry['assigned_admin_id'] ?? 0) === (int) $admin['id'] ? 'selected' : '' ?>><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary w-100">Save owner</button>
                        </div>
                    </form>

                    <hr class="ims-divider">

                    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="row g-3">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                        <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
                        <div class="col-12">
                            <label class="form-label mb-0">
                                <span>Status</span>
                                <select name="status" class="form-input">
                                    <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $inquiry['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn w-100">Update status</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section class="card ims-card ims-card-compact">
            <div class="card-header">
                <div class="page-section-overline">Source &amp; tracking</div>
                <h2>Inquiry metadata</h2>
            </div>
            <div class="card-body">
                <dl class="ims-meta-list ims-meta-list-compact">
                    <div><dt>Name</dt><dd><?= e($inquiry['name']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($inquiry['email']) ?></dd></div>
                    <div><dt>Phone</dt><dd><?= e($inquiry['phone'] ?: '-') ?></dd></div>
                    <div><dt>Company</dt><dd><?= e($inquiry['from_company'] ?: '-') ?></dd></div>
                    <div><dt>Country</dt><dd><?= e($inquiry['country'] ?: '-') ?></dd></div>
                    <div><dt>Address</dt><dd><?= e($inquiry['address'] ?: '-') ?></dd></div>
                    <div><dt>Source URL</dt><dd class="break-all"><?= e($inquiry['source_url'] ?: '-') ?></dd></div>
                    <div><dt>Referer URL</dt><dd class="break-all"><?= e($inquiry['referer_url'] ?: '-') ?></dd></div>
                    <div><dt>IP</dt><dd><?= e($inquiry['ip'] ?: '-') ?></dd></div>
                    <div><dt>Browser</dt><dd><?= e($inquiry['browser'] ?: '-') ?></dd></div>
                    <div><dt>Device type</dt><dd><?= e($inquiry['device_type'] ?: '-') ?></dd></div>
                    <div><dt>Language</dt><dd><?= e($inquiry['language'] ?: '-') ?></dd></div>
                    <div><dt>Created at</dt><dd><?= e((string) $inquiry['created_at']) ?></dd></div>
                    <div><dt>Updated at</dt><dd><?= e((string) $inquiry['updated_at']) ?></dd></div>
                </dl>
            </div>
        </section>

        <section class="card ims-card ims-card-compact">
            <div class="card-header">
                <div class="page-section-overline">Structured fields</div>
                <h2>Extra data</h2>
            </div>
            <div class="card-body">
                <?php if (empty($extraData)): ?>
                    <p class="muted mb-0">No extra fields.</p>
                <?php else: ?>
                    <dl class="ims-meta-list ims-meta-list-compact">
                        <?php foreach ($extraData as $key => $value): ?>
                            <div>
                                <dt><?= e((string) $key) ?></dt>
                                <dd><?= e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $value) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </section>

        <section class="card ims-card ims-card-compact">
            <div class="card-header split-header align-items-start">
                <div>
                    <div class="page-section-overline">Recent activity</div>
                    <h2>Recent logs</h2>
                </div>
                <span class="badge-neutral"><?= (int) count($logs) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="muted mb-0">No related logs yet.</p>
                <?php else: ?>
                    <div class="ims-log-list ims-log-list-compact">
                        <?php foreach ($logPreview as $log): ?>
                            <div class="ims-log-item ims-log-item-compact">
                                <div class="table-title mb-1"><?= e($log['action']) ?></div>
                                <div class="simple-list-meta"><?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?> · <?= e((string) $log['created_at']) ?></div>
                                <?php if (!empty($log['action_note'])): ?><div class="ims-sub-line mt-2"><?= e($log['action_note']) ?></div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($remainingLogs > 0): ?>
                        <details class="ims-compact-details mt-3">
                            <summary>Show <?= (int) $remainingLogs ?> more log entr<?= $remainingLogs === 1 ? 'y' : 'ies' ?></summary>
                            <div class="ims-log-list ims-log-list-compact mt-3">
                                <?php foreach (array_slice($logs, 3) as $log): ?>
                                    <div class="ims-log-item ims-log-item-compact">
                                        <div class="table-title mb-1"><?= e($log['action']) ?></div>
                                        <div class="simple-list-meta"><?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?> · <?= e((string) $log['created_at']) ?></div>
                                        <?php if (!empty($log['action_note'])): ?><div class="ims-sub-line mt-2"><?= e($log['action_note']) ?></div><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card ims-card ims-card-compact">
            <div class="card-header">
                <div class="page-section-overline">Raw payload</div>
                <h2>JSON snapshot</h2>
            </div>
            <div class="card-body">
                <?php if (empty($rawPayload)): ?>
                    <p class="muted mb-0">No raw payload.</p>
                <?php else: ?>
                    <details class="ims-compact-details">
                        <summary>Show raw payload</summary>
                        <pre class="code-box mt-3"><?= e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>
