<div class="detail-actions">
    <div class="actions-wrap">
        <a href="<?= e(base_url('inquiries')) ?>" class="btn">Back to List</a>

        <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
            <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
            <select name="status" class="form-input form-input-sm">
                <?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= $inquiry['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>
</div>

<div class="card mb-20">
    <div class="card-header"><h2>Admin Note</h2></div>
    <div class="card-body">
        <form method="post" action="<?= e(base_url('inquiry/note')) ?>">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
            <label class="form-label full-width">
                <span>Internal note for follow-up</span>
                <textarea name="admin_note" class="form-input" rows="5" placeholder="Add your follow-up note, summary, or risk judgment here..."><?= e((string) ($inquiry['admin_note'] ?? '')) ?></textarea>
            </label>
            <button type="submit" class="btn btn-primary">Save Note</button>
        </form>
    </div>
</div>

<div class="card detail-card">
    <div class="detail-grid">
        <div class="detail-section">
            <h2>Basic Information</h2>
            <dl class="detail-list">
                <div><dt>ID</dt><dd>#<?= e((string) $inquiry['id']) ?></dd></div>
                <div><dt>Status</dt><dd><span class="status-pill status-<?= e($inquiry['status']) ?>"><?= e(ucfirst((string) $inquiry['status'])) ?></span></dd></div>
                <div><dt>Name</dt><dd><?= e($inquiry['name'] ?: '-') ?></dd></div>
                <div><dt>Email</dt><dd><?= e($inquiry['email'] ?: '-') ?></dd></div>
                <div><dt>Phone</dt><dd><?= e($inquiry['phone'] ?: '-') ?></dd></div>
                <div><dt>Company</dt><dd><?= e($inquiry['from_company'] ?: '-') ?></dd></div>
                <div><dt>Country</dt><dd><?= e($inquiry['country'] ?: '-') ?></dd></div>
                <div><dt>Address</dt><dd><?= e($inquiry['address'] ?: '-') ?></dd></div>
                <div><dt>Title</dt><dd><?= e($inquiry['title'] ?: '-') ?></dd></div>
            </dl>
        </div>

        <div class="detail-section">
            <h2>Source Information</h2>
            <dl class="detail-list">
                <div><dt>Site</dt><dd><?= e($inquiry['site_name'] ?: '-') ?></dd></div>
                <div><dt>Site Domain</dt><dd><?= e($inquiry['site_domain'] ?: '-') ?></dd></div>
                <div><dt>Form Key</dt><dd><?= e($inquiry['form_key'] ?: '-') ?></dd></div>
                <div><dt>Source URL</dt><dd class="break-all"><?= e($inquiry['source_url'] ?: '-') ?></dd></div>
                <div><dt>Referer URL</dt><dd class="break-all"><?= e($inquiry['referer_url'] ?: '-') ?></dd></div>
                <div><dt>IP</dt><dd><?= e($inquiry['ip'] ?: '-') ?></dd></div>
                <div><dt>Browser</dt><dd><?= e($inquiry['browser'] ?: '-') ?></dd></div>
                <div><dt>Device Type</dt><dd><?= e($inquiry['device_type'] ?: '-') ?></dd></div>
                <div><dt>Language</dt><dd><?= e($inquiry['language'] ?: '-') ?></dd></div>
                <div><dt>Submitted At</dt><dd><?= e((string) ($inquiry['submitted_at'] ?: '-')) ?></dd></div>
                <div><dt>Created At</dt><dd><?= e((string) $inquiry['created_at']) ?></dd></div>
            </dl>
        </div>
    </div>

    <div class="detail-section">
        <h2>Content</h2>
        <div class="content-box"><?= nl2br(e($inquiry['content'] ?: '-')) ?></div>
    </div>

    <div class="detail-grid mt-20">
        <div class="detail-section">
            <h2>Extra Data</h2>
            <?php if (empty($extraData)): ?>
                <p class="muted">No extra fields.</p>
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
        </div>

        <div class="detail-section">
            <h2>Recent Logs</h2>
            <?php if (empty($logs)): ?>
                <p class="muted">No related logs yet.</p>
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
        </div>
    </div>

    <div class="detail-section mt-20">
        <h2>Raw Payload</h2>
        <?php if (empty($rawPayload)): ?>
            <p class="muted">No raw payload.</p>
        <?php else: ?>
            <pre class="code-box"><?= e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        <?php endif; ?>
    </div>
</div>
