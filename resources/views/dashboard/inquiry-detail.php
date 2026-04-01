<div class="detail-actions">
    <a href="<?= e(base_url('inquiries')) ?>" class="btn">← Back to list</a>
</div>

<div class="card detail-card">
    <div class="detail-grid">
        <div class="detail-section">
            <h2>Inquiry Overview</h2>
            <dl class="detail-list">
                <div><dt>ID</dt><dd>#<?= e((string) $inquiry['id']) ?></dd></div>
                <div><dt>Status</dt><dd><span class="status-pill status-<?= e($inquiry['status']) ?>"><?= e(ucfirst((string) $inquiry['status'])) ?></span></dd></div>
                <div><dt>Assigned To</dt><dd><?= e(($inquiry['assigned_nickname'] ?: $inquiry['assigned_username']) ?: 'Unassigned') ?></dd></div>
                <div><dt>Name</dt><dd><?= e($inquiry['name']) ?></dd></div>
                <div><dt>Email</dt><dd><?= e($inquiry['email']) ?></dd></div>
                <div><dt>Phone</dt><dd><?= e($inquiry['phone'] ?: '-') ?></dd></div>
                <div><dt>Company</dt><dd><?= e($inquiry['from_company'] ?: '-') ?></dd></div>
                <div><dt>Country</dt><dd><?= e($inquiry['country'] ?: '-') ?></dd></div>
                <div><dt>Address</dt><dd><?= e($inquiry['address'] ?: '-') ?></dd></div>
                <div><dt>Title</dt><dd><?= e($inquiry['title'] ?: '-') ?></dd></div>
                <div><dt>Site</dt><dd><?= e($inquiry['site_name'] ?: '-') ?></dd></div>
                <div><dt>Form Key</dt><dd><?= e($inquiry['form_key'] ?: '-') ?></dd></div>
            </dl>
        </div>

        <div class="detail-section">
            <h2>Source & Tracking</h2>
            <dl class="detail-list">
                <div><dt>Source URL</dt><dd class="break-all"><?= e($inquiry['source_url'] ?: '-') ?></dd></div>
                <div><dt>Referer URL</dt><dd class="break-all"><?= e($inquiry['referer_url'] ?: '-') ?></dd></div>
                <div><dt>IP</dt><dd><?= e($inquiry['ip'] ?: '-') ?></dd></div>
                <div><dt>Browser</dt><dd><?= e($inquiry['browser'] ?: '-') ?></dd></div>
                <div><dt>Device Type</dt><dd><?= e($inquiry['device_type'] ?: '-') ?></dd></div>
                <div><dt>Language</dt><dd><?= e($inquiry['language'] ?: '-') ?></dd></div>
                <div><dt>Submitted At</dt><dd><?= e((string) ($inquiry['submitted_at'] ?: '-')) ?></dd></div>
                <div><dt>Created At</dt><dd><?= e((string) $inquiry['created_at']) ?></dd></div>
                <div><dt>Updated At</dt><dd><?= e((string) $inquiry['updated_at']) ?></dd></div>
            </dl>
        </div>
    </div>

    <div class="detail-section">
        <h2>Content</h2>
        <div class="content-box"><?= nl2br(e($inquiry['content'] ?: '-')) ?></div>
    </div>

    <div class="detail-grid mt-20">
        <div class="detail-section">
            <h2>Owner & Status Actions</h2>
            <form method="post" action="<?= e(base_url('inquiry/assign')) ?>" class="form-grid">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <label class="form-label">
                    <span>Assigned Owner</span>
                    <select name="assigned_admin_id" class="form-input">
                        <option value="">Unassigned</option>
                        <?php foreach ($admins as $admin): ?>
                            <option value="<?= (int) $admin['id'] ?>" <?= (int) ($inquiry['assigned_admin_id'] ?? 0) === (int) $admin['id'] ? 'selected' : '' ?>><?= e($admin['nickname'] ?: $admin['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="btn btn-primary">Save Owner</button>
            </form>

            <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="form-grid mt-20">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
                <label class="form-label">
                    <span>Status</span>
                    <select name="status" class="form-input">
                        <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $inquiry['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="btn">Update Status</button>
            </form>
        </div>

        <div class="detail-section">
            <h2>Admin Note</h2>
            <form method="post" action="<?= e(base_url('inquiry/note')) ?>" class="form-grid">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <label class="form-label full-width">
                    <span>Admin Note</span>
                    <textarea name="admin_note" class="form-input" rows="6" placeholder="Add internal summary, priority or customer context..."><?= e((string) ($inquiry['admin_note'] ?? '')) ?></textarea>
                </label>
                <button type="submit" class="btn btn-primary">Save Note</button>
            </form>
        </div>
    </div>

    <div class="detail-grid mt-20">
        <div class="detail-section">
            <h2>Add Follow-up Record</h2>
            <form method="post" action="<?= e(base_url('inquiry/followup')) ?>" class="form-grid">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <label class="form-label"><span>Type</span><select name="followup_type" class="form-input"><?php foreach (['note', 'email', 'call', 'meeting', 'todo', 'status'] as $type): ?><option value="<?= e($type) ?>"><?= e(ucfirst($type)) ?></option><?php endforeach; ?></select></label>
                <label class="form-label"><span>Next Contact At</span><input type="datetime-local" name="next_contact_at" class="form-input"></label>
                <label class="form-label checkbox-label full-width"><span>Progress</span><label class="checkbox-row"><input type="checkbox" name="is_completed" value="1"> Mark this follow-up as completed</label></label>
                <label class="form-label full-width"><span>Follow-up Content</span><textarea name="content" class="form-input" rows="6" placeholder="Write what happened, what to send next, or the next action item..."></textarea></label>
                <button type="submit" class="btn btn-primary">Add Follow-up</button>
            </form>
        </div>

        <div class="detail-section">
            <h2>Follow-up History</h2>
            <?php if (empty($followups)): ?>
                <p class="muted">No follow-up records yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($followups as $followup): ?>
                        <div class="simple-list-item compact-item">
                            <div class="split-header"><div class="simple-list-title"><?= e(ucfirst((string) $followup['followup_type'])) ?></div><span class="<?= !empty($followup['is_completed']) ? 'badge-success' : 'badge-neutral' ?>"><?= !empty($followup['is_completed']) ? 'Completed' : 'Open' ?></span></div>
                            <div class="simple-list-meta"><?= e($followup['admin_nickname'] ?: $followup['admin_username'] ?: 'System') ?> · <?= e((string) $followup['created_at']) ?></div>
                            <?php if (!empty($followup['next_contact_at'])): ?><div class="simple-list-meta mt-8">Next contact: <?= e((string) $followup['next_contact_at']) ?></div><?php endif; ?>
                            <div class="simple-list-meta mt-8"><?= nl2br(e((string) $followup['content'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-grid mt-20">
        <div class="detail-section">
            <h2>Extra Data</h2>
            <?php if (empty($extraData)): ?><p class="muted">No extra fields.</p><?php else: ?><dl class="detail-list"><?php foreach ($extraData as $key => $value): ?><div><dt><?= e((string) $key) ?></dt><dd><?= e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $value) ?></dd></div><?php endforeach; ?></dl><?php endif; ?>
        </div>
        <div class="detail-section">
            <h2>Recent Logs</h2>
            <?php if (empty($logs)): ?><p class="muted">No related logs yet.</p><?php else: ?><div class="simple-list compact-list"><?php foreach ($logs as $log): ?><div class="simple-list-item compact-item"><div class="simple-list-title"><?= e($log['action']) ?></div><div class="simple-list-meta"><?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?> · <?= e((string) $log['created_at']) ?></div><?php if (!empty($log['action_note'])): ?><div class="simple-list-meta mt-8"><?= e($log['action_note']) ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        </div>
    </div>

    <div class="detail-section mt-20">
        <h2>Raw Payload</h2>
        <?php if (empty($rawPayload)): ?><p class="muted">No raw payload.</p><?php else: ?><pre class="code-box"><?= e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre><?php endif; ?>
    </div>
</div>
