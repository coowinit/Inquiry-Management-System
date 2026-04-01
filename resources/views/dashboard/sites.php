<div class="card mb-20">
    <div class="card-header split-header">
        <h2>Receive API</h2>
        <div class="muted">Use these credentials from your site backend</div>
    </div>
    <div class="card-body">
        <p class="muted mb-12">POST endpoint</p>
        <pre class="code-box"><?= e($apiEndpoint) ?></pre>
        <p class="muted mt-16 mb-12">Signed request format</p>
        <pre class="code-box">signature = HMAC_SHA256(X-Timestamp + "\n" + raw_body, signature_secret)</pre>
        <p class="muted mt-16 mb-12">Field mapping example</p>
        <pre class="code-box"><?= e($mappingExample) ?></pre>
    </div>
</div>

<div class="card mb-20">
    <div class="card-header">
        <h2>Create Site</h2>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e(base_url('sites/create')) ?>" class="filter-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

            <label class="form-label">
                <span>Site Name</span>
                <input type="text" name="site_name" class="form-input" required>
            </label>

            <label class="form-label">
                <span>Domain</span>
                <input type="text" name="site_domain" class="form-input" placeholder="a.com" required>
            </label>

            <label class="form-label">
                <span>Site Key</span>
                <input type="text" name="site_key" class="form-input" placeholder="a_main" required>
            </label>

            <label class="form-label">
                <span>API Token</span>
                <input type="text" name="api_token" class="form-input" value="<?= e($generatedToken) ?>" required>
            </label>

            <label class="form-label full-width">
                <span>Signature Secret</span>
                <input type="text" name="signature_secret" class="form-input" value="<?= e($generatedSignatureSecret) ?>" required>
            </label>

            <label class="form-label">
                <span>Status</span>
                <select name="status" class="form-input">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </label>

            <label class="form-label checkbox-label">
                <span>Security</span>
                <label class="checkbox-row"><input type="checkbox" name="require_signature" value="1"> Require HMAC signature</label>
            </label>

            <label class="form-label full-width">
                <span>Field Mapping JSON</span>
                <textarea name="field_mapping_json" class="form-input" rows="8" placeholder="<?= e($mappingExample) ?>"></textarea>
            </label>

            <div class="full-width soft-panel">
                <div class="section-mini-title mb-12">Site Notification Override</div>
                <div class="filter-grid">
                    <label class="form-label"><span>Mode</span><select name="notification_mode" class="form-input"><option value="inherit">Inherit global settings</option><option value="disable">Disable for this site</option><option value="custom">Use site-specific settings</option></select></label>
                    <label class="form-label"><span>Transport</span><select name="notification_transport" class="form-input"><option value="log_only">log_only</option><option value="mail">mail</option></select></label>
                    <label class="form-label"><span>Subject Prefix</span><input type="text" name="notification_subject_prefix" class="form-input" placeholder="[IMS-A]"></label>
                    <label class="form-label full-width"><span>Recipients</span><textarea name="notification_recipients" class="form-input" rows="4" placeholder="sales@example.com\nteam@example.com"></textarea></label>
                    <label class="form-label checkbox-label full-width"><span>Notify Statuses</span><div class="checkbox-grid"><?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?><label class="checkbox-row"><input type="checkbox" name="notification_statuses[]" value="<?= e($status) ?>" <?= in_array($status, $notificationDefaults['notify_statuses'], true) ? 'checked' : '' ?>><span><?= e(ucfirst($status)) ?></span></label><?php endforeach; ?></div></label>
                    <label class="form-label checkbox-label full-width"><span>Advanced</span><div class="checkbox-grid"><label class="checkbox-row"><input type="checkbox" name="notification_include_spam" value="1"> Include spam notifications</label><label class="checkbox-row"><input type="checkbox" name="notification_include_admin_link" value="1" checked> Include backend detail link</label></div></label>
                </div>
            </div>

            <label class="form-label full-width">
                <span>Notes</span>
                <textarea name="notes" class="form-input" rows="4" placeholder="Optional notes for this site"></textarea>
            </label>

            <div class="full-width">
                <button type="submit" class="btn btn-primary">Create Site</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header split-header">
        <h2>Site List</h2>
        <div class="muted">Configured source websites</div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Name</th>
                    <th>Domain</th>
                    <th>Site Key</th>
                    <th>Mode</th>
                    <th>Notifications</th>
                    <th>Stats</th>
                    <th>Last Inquiry</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sites)): ?>
                    <tr>
                        <td colspan="9" class="empty-cell">No sites found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sites as $site): ?>
                        <?php $siteNotif = (new \App\Models\Site())->notificationSettings($site); ?>
                        <tr>
                            <td>#<?= e((string) $site['id']) ?></td>
                            <td>
                                <div class="table-title"><?= e($site['site_name']) ?></div>
                                <div class="table-sub"><?= e($site['status']) ?></div>
                            </td>
                            <td><?= e($site['site_domain']) ?></td>
                            <td><code><?= e($site['site_key']) ?></code></td>
                            <td><?= (int) ($site['require_signature'] ?? 0) === 1 ? 'Signed + Token' : 'Token Only' ?></td>
                            <td><?= e(ucfirst((string) ($siteNotif['mode'] ?? 'inherit'))) ?></td>
                            <td>
                                <div class="table-sub"><?= e((string) ($site['inquiry_total'] ?? 0)) ?> total</div>
                                <div class="table-sub"><?= e((string) ($site['unread_total'] ?? 0)) ?> unread</div>
                            </td>
                            <td><?= e((string) ($site['last_inquiry_at'] ?: '-')) ?></td>
                            <td><a href="<?= e(base_url('sites/edit?id=' . (int) $site['id'])) ?>" class="btn btn-sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
