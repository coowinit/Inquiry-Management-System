<div class="detail-actions">
    <a href="<?= e(base_url('inquiries')) ?>" class="btn btn-soft">← Back to list</a>
</div>

<section class="card inquiry-hero mb-20">
    <div>
        <div class="page-section-overline">Inquiry Overview</div>
        <h2><?= e($inquiry['title'] ?: 'Untitled inquiry') ?></h2>
        <p><?= e($inquiry['content'] ? mb_strimwidth((string) $inquiry['content'], 0, 180, '...') : 'No content.') ?></p>

        <div class="inquiry-meta-grid">
            <div class="meta-stat">
                <div class="meta-stat-label">Inquiry ID</div>
                <div class="meta-stat-value">#<?= (int) $inquiry['id'] ?></div>
            </div>
            <div class="meta-stat">
                <div class="meta-stat-label">Status</div>
                <div class="meta-stat-value"><span class="status-pill status-<?= e($inquiry['status']) ?>"><?= e(ucfirst((string) $inquiry['status'])) ?></span></div>
            </div>
            <div class="meta-stat">
                <div class="meta-stat-label">Assigned Owner</div>
                <div class="meta-stat-value"><?= e(($inquiry['assigned_nickname'] ?: $inquiry['assigned_username']) ?: 'Unassigned') ?></div>
            </div>
            <div class="meta-stat">
                <div class="meta-stat-label">Submitted At</div>
                <div class="meta-stat-value"><?= e((string) ($inquiry['submitted_at'] ?: $inquiry['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <div class="soft-panel">
        <div class="page-section-overline">Quick Actions</div>
        <div class="quick-actions-row">
            <button type="button" class="btn" data-copy-text="<?= e($copyBlocks['contact_summary']) ?>">Copy Contact</button>
            <button type="button" class="btn" data-copy-text="<?= e($copyBlocks['reply_draft']) ?>">Copy Reply Draft</button>
            <button type="button" class="btn" data-copy-text="<?= e($copyBlocks['json_snapshot']) ?>">Copy JSON</button>
        </div>
        <div class="details-key-grid mt-20">
            <div>
                <div class="meta-stat-label">Contact</div>
                <div class="meta-stat-value"><?= e($inquiry['name']) ?></div>
                <div class="muted mt-8"><?= e($inquiry['email']) ?><?= !empty($inquiry['phone']) ? ' · ' . e($inquiry['phone']) : '' ?></div>
            </div>
            <div>
                <div class="meta-stat-label">Site &amp; Form</div>
                <div class="meta-stat-value"><?= e($inquiry['site_name'] ?: '-') ?></div>
                <div class="muted mt-8"><?= e($inquiry['form_key'] ?: '-') ?></div>
            </div>
            <?php if (!empty($inquiry['source_url'])): ?>
                <div>
                    <div class="meta-stat-label">Source URL</div>
                    <div class="muted break-all"><?= e($inquiry['source_url']) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="inquiry-detail-layout">
    <div class="detail-main-stack">
        <section class="content-card">
            <div class="page-section-overline">Customer Message</div>
            <h3>Inquiry Content</h3>
            <div class="content-box"><?= nl2br(e($inquiry['content'] ?: '-')) ?></div>
        </section>

        <?php if ($canUpdate): ?>
            <section class="content-card">
                <div class="page-section-overline">Internal Notes</div>
                <h3>Admin Note</h3>
                <form method="post" action="<?= e(base_url('inquiry/note')) ?>" class="form-grid">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                    <label class="form-label full-width">
                        <span>Admin Note</span>
                        <textarea name="admin_note" class="form-input" rows="6" placeholder="Add internal summary, priority or customer context..."><?= e((string) ($inquiry['admin_note'] ?? '')) ?></textarea>
                    </label>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </section>

            <section class="content-card">
                <div class="page-section-overline">Next Action</div>
                <h3>Add Follow-up Record</h3>
                <form method="post" action="<?= e(base_url('inquiry/followup')) ?>" class="filter-grid">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                    <label class="form-label">
                        <span>Type</span>
                        <select name="followup_type" class="form-input">
                            <?php foreach (['note', 'email', 'call', 'meeting', 'todo', 'status'] as $type): ?>
                                <option value="<?= e($type) ?>"><?= e(ucfirst($type)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="form-label">
                        <span>Next Contact At</span>
                        <input type="datetime-local" name="next_contact_at" class="form-input">
                    </label>
                    <label class="form-label full-width">
                        <span>Progress</span>
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_completed" value="1">
                            <span>Mark this follow-up as completed</span>
                        </label>
                    </label>
                    <label class="form-label full-width">
                        <span>Follow-up Content</span>
                        <textarea name="content" class="form-input" rows="5" placeholder="Write what happened, what to send next, or the next action item..."></textarea>
                    </label>
                    <div class="filter-actions full-width">
                        <button type="submit" class="btn btn-primary">Add Follow-up</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="content-card history-card">
            <div class="split-header mb-12">
                <div>
                    <div class="page-section-overline">History</div>
                    <h3 class="mb-0">Follow-up Timeline</h3>
                </div>
                <span class="badge-neutral"><?= (int) count($followups) ?> record<?= count($followups) === 1 ? '' : 's' ?></span>
            </div>

            <?php if (empty($followups)): ?>
                <p class="muted mb-0">No follow-up records yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($followups as $followup): ?>
                        <div class="simple-list-item compact-item">
                            <div class="split-header">
                                <div>
                                    <div class="simple-list-title"><?= e(ucfirst((string) $followup['followup_type'])) ?></div>
                                    <div class="simple-list-meta"><?= e($followup['admin_nickname'] ?: $followup['admin_username'] ?: 'System') ?> · <?= e((string) $followup['created_at']) ?></div>
                                </div>
                                <span class="<?= !empty($followup['is_completed']) ? 'badge-success' : 'badge-neutral' ?>">
                                    <?= !empty($followup['is_completed']) ? 'Completed' : 'Open' ?>
                                </span>
                            </div>
                            <?php if (!empty($followup['next_contact_at'])): ?><div class="simple-list-meta mt-8">Next contact: <?= e((string) $followup['next_contact_at']) ?></div><?php endif; ?>
                            <?php if (!empty($followup['completed_at'])): ?><div class="simple-list-meta mt-8">Completed at: <?= e((string) $followup['completed_at']) ?></div><?php endif; ?>
                            <div class="simple-list-meta mt-8"><?= nl2br(e((string) $followup['content'])) ?></div>

                            <?php if ($canUpdate): ?>
                                <details class="mt-12">
                                    <summary class="btn btn-sm btn-linklike">Edit follow-up</summary>
                                    <form method="post" action="<?= e(base_url('inquiry/followup/update')) ?>" class="filter-grid mt-12">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                                        <input type="hidden" name="followup_id" value="<?= (int) $followup['id'] ?>">
                                        <label class="form-label">
                                            <span>Type</span>
                                            <select name="followup_type" class="form-input">
                                                <?php foreach (['note', 'email', 'call', 'meeting', 'todo', 'status'] as $type): ?>
                                                    <option value="<?= e($type) ?>" <?= $followup['followup_type'] === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label class="form-label">
                                            <span>Next Contact At</span>
                                            <input type="datetime-local" name="next_contact_at" class="form-input" value="<?= !empty($followup['next_contact_at']) ? e(date('Y-m-d\TH:i', strtotime((string) $followup['next_contact_at']))) : '' ?>">
                                        </label>
                                        <label class="form-label full-width">
                                            <span>Progress</span>
                                            <label class="checkbox-row">
                                                <input type="checkbox" name="is_completed" value="1" <?= !empty($followup['is_completed']) ? 'checked' : '' ?>>
                                                <span>Mark this follow-up as completed</span>
                                            </label>
                                        </label>
                                        <label class="form-label full-width">
                                            <span>Follow-up Content</span>
                                            <textarea name="content" class="form-input" rows="5"><?= e((string) $followup['content']) ?></textarea>
                                        </label>
                                        <div class="filter-actions full-width">
                                            <button type="submit" class="btn btn-primary btn-sm">Save Follow-up</button>
                                        </div>
                                    </form>
                                    <form method="post" action="<?= e(base_url('inquiry/followup/toggle')) ?>" class="filter-actions mt-8">
                                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                                        <input type="hidden" name="followup_id" value="<?= (int) $followup['id'] ?>">
                                        <input type="hidden" name="complete" value="<?= !empty($followup['is_completed']) ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm"><?= !empty($followup['is_completed']) ? 'Reopen' : 'Mark Completed' ?></button>
                                    </form>
                                </details>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="detail-side-stack">
        <?php if ($canUpdate): ?>
            <section class="content-card">
                <div class="page-section-overline">Owner &amp; Status</div>
                <h3>Quick Management</h3>
                <form method="post" action="<?= e(base_url('inquiry/assign')) ?>" class="form-grid">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                    <label class="form-label full-width">
                        <span>Assigned Owner</span>
                        <select name="assigned_admin_id" class="form-input">
                            <option value="">Unassigned</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?= (int) $admin['id'] ?>" <?= (int) ($inquiry['assigned_admin_id'] ?? 0) === (int) $admin['id'] ? 'selected' : '' ?>><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="filter-actions full-width">
                        <button type="submit" class="btn btn-primary">Save Owner</button>
                    </div>
                </form>

                <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="form-grid mt-16">
                    <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                    <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
                    <label class="form-label full-width">
                        <span>Status</span>
                        <select name="status" class="form-input">
                            <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= $inquiry['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="filter-actions full-width">
                        <button type="submit" class="btn">Update Status</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="content-card">
            <div class="page-section-overline">Tracking</div>
            <h3>Source &amp; Tracking</h3>
            <dl class="detail-list">
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
                <div><dt>Device Type</dt><dd><?= e($inquiry['device_type'] ?: '-') ?></dd></div>
                <div><dt>Language</dt><dd><?= e($inquiry['language'] ?: '-') ?></dd></div>
                <div><dt>Created At</dt><dd><?= e((string) $inquiry['created_at']) ?></dd></div>
                <div><dt>Updated At</dt><dd><?= e((string) $inquiry['updated_at']) ?></dd></div>
            </dl>
        </section>

        <section class="content-card">
            <div class="page-section-overline">Structured Fields</div>
            <h3>Extra Data</h3>
            <?php if (empty($extraData)): ?>
                <p class="muted mb-0">No extra fields.</p>
            <?php else: ?>
                <dl class="detail-list">
                    <?php foreach ($extraData as $key => $value): ?>
                        <div>
                            <dt><?= e((string) $key) ?></dt>
                            <dd><?= e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $value) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
        </section>

        <section class="content-card">
            <div class="page-section-overline">Recent Activity</div>
            <h3>Recent Logs</h3>
            <?php if (empty($logs)): ?>
                <p class="muted mb-0">No related logs yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($logs as $log): ?>
                        <div class="simple-list-item compact-item">
                            <div class="simple-list-title"><?= e($log['action']) ?></div>
                            <div class="simple-list-meta"><?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?> · <?= e((string) $log['created_at']) ?></div>
                            <?php if (!empty($log['action_note'])): ?><div class="simple-list-meta mt-8"><?= e($log['action_note']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="content-card">
            <div class="page-section-overline">Raw Payload</div>
            <h3>JSON Snapshot</h3>
            <?php if (empty($rawPayload)): ?>
                <p class="muted mb-0">No raw payload.</p>
            <?php else: ?>
                <details class="payload-toggle" open>
                    <summary>Show / hide raw payload</summary>
                    <pre class="code-box code-box-tall"><?= e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                </details>
            <?php endif; ?>
        </section>
    </aside>
</div>
