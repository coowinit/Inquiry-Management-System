<div class="detail-actions actions-wrap">
    <a href="<?= e(base_url('inquiries')) ?>" class="btn">Back</a>

    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
        <input type="hidden" name="status" value="unread">
        <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
        <button type="submit" class="btn">Mark Unread</button>
    </form>

    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
        <input type="hidden" name="status" value="spam">
        <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
        <button type="submit" class="btn">Mark Spam</button>
    </form>

    <form method="post" action="<?= e(base_url('inquiry/status')) ?>" class="inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
        <input type="hidden" name="status" value="trash">
        <input type="hidden" name="back" value="<?= e('inquiry?id=' . (int) $inquiry['id']) ?>">
        <button type="submit" class="btn">Move to Trash</button>
    </form>

    <?php if (!empty($inquiry['ip'])): ?>
        <form method="post" action="<?= e(base_url('tools/blacklist-ips')) ?>" class="inline-form">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="ip_address" value="<?= e($inquiry['ip']) ?>">
            <input type="hidden" name="reason" value="Added from inquiry #<?= (int) $inquiry['id'] ?>">
            <button type="submit" class="btn">Block This IP</button>
        </form>
    <?php endif; ?>
</div>

<div class="card detail-card">
    <div class="detail-grid">
        <div class="detail-section">
            <h2>Basic Information</h2>
            <dl class="detail-list">
                <div><dt>ID</dt><dd>#<?= e((string) $inquiry['id']) ?></dd></div>
                <div><dt>Title</dt><dd><?= e($inquiry['title'] ?: '-') ?></dd></div>
                <div><dt>Name</dt><dd><?= e($inquiry['name'] ?: '-') ?></dd></div>
                <div><dt>Email</dt><dd><?= e($inquiry['email'] ?: '-') ?></dd></div>
                <div><dt>Phone</dt><dd><?= e($inquiry['phone'] ?: '-') ?></dd></div>
                <div><dt>Company</dt><dd><?= e($inquiry['from_company'] ?: '-') ?></dd></div>
                <div><dt>Country</dt><dd><?= e($inquiry['country'] ?: '-') ?></dd></div>
                <div><dt>Address</dt><dd><?= e($inquiry['address'] ?: '-') ?></dd></div>
                <div><dt>Status</dt><dd><span class="status-pill status-<?= e($inquiry['status']) ?>"><?= e((string) $inquiry['status']) ?></span></dd></div>
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

    <div class="detail-grid">
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
            <h2>Raw Payload</h2>
            <?php if (empty($rawPayload)): ?>
                <p class="muted">No raw payload.</p>
            <?php else: ?>
                <pre class="code-box"><?= e(json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>
