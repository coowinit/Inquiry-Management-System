<div class="detail-actions">
    <div class="actions-wrap">
        <a href="<?= e(base_url('sites')) ?>" class="btn">Back to Sites</a>
    </div>
</div>

<div class="card form-card mb-20">
    <div class="card-header">
        <h2>Edit Site</h2>
    </div>

    <form method="post" action="<?= e(base_url('sites/update')) ?>" class="form-grid profile-form">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">

        <label class="form-label">
            <span>Site Name</span>
            <input type="text" name="site_name" class="form-input" value="<?= e($site['site_name']) ?>" required>
        </label>

        <label class="form-label">
            <span>Domain</span>
            <input type="text" name="site_domain" class="form-input" value="<?= e($site['site_domain']) ?>" required>
        </label>

        <label class="form-label">
            <span>Site Key</span>
            <input type="text" name="site_key" class="form-input" value="<?= e($site['site_key']) ?>" required>
        </label>

        <label class="form-label">
            <span>Status</span>
            <select name="status" class="form-input">
                <option value="active" <?= $site['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $site['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </label>

        <label class="form-label checkbox-label full-width">
            <span>Security</span>
            <label class="checkbox-row"><input type="checkbox" name="require_signature" value="1" <?= (int) ($site['require_signature'] ?? 0) === 1 ? 'checked' : '' ?>> Require HMAC signature</label>
        </label>

        <label class="form-label full-width">
            <span>Field Mapping JSON</span>
            <textarea name="field_mapping_json" class="form-input" rows="10" placeholder="<?= e($mappingExample) ?>"><?= e((string) ($site['field_mapping_json'] ?? '')) ?></textarea>
        </label>

        <div class="full-width soft-panel">
            <div class="section-mini-title mb-12">Site Notification Override</div>
            <div class="filter-grid">
                <label class="form-label"><span>Mode</span><select name="notification_mode" class="form-input"><option value="inherit" <?= ($siteNotificationSettings['mode'] ?? 'inherit') === 'inherit' ? 'selected' : '' ?>>Inherit global settings</option><option value="disable" <?= ($siteNotificationSettings['mode'] ?? '') === 'disable' ? 'selected' : '' ?>>Disable for this site</option><option value="custom" <?= ($siteNotificationSettings['mode'] ?? '') === 'custom' ? 'selected' : '' ?>>Use site-specific settings</option></select></label>
                <label class="form-label"><span>Transport</span><select name="notification_transport" class="form-input"><option value="log_only" <?= ($siteNotificationSettings['transport'] ?? 'log_only') === 'log_only' ? 'selected' : '' ?>>log_only</option><option value="mail" <?= ($siteNotificationSettings['transport'] ?? '') === 'mail' ? 'selected' : '' ?>>mail</option></select></label>
                <label class="form-label"><span>Subject Prefix</span><input type="text" name="notification_subject_prefix" class="form-input" value="<?= e((string) ($siteNotificationSettings['subject_prefix'] ?? '')) ?>" placeholder="[IMS-A]"></label>
                <label class="form-label full-width"><span>Recipients</span><textarea name="notification_recipients" class="form-input" rows="4" placeholder="sales@example.com\nteam@example.com"><?= e(implode("\n", $siteNotificationSettings['recipients'] ?? [])) ?></textarea></label>
                <label class="form-label checkbox-label full-width"><span>Notify Statuses</span><div class="checkbox-grid"><?php foreach (['unread', 'read', 'spam', 'trash'] as $status): ?><label class="checkbox-row"><input type="checkbox" name="notification_statuses[]" value="<?= e($status) ?>" <?= in_array($status, $siteNotificationSettings['notify_statuses'] ?? ['unread'], true) ? 'checked' : '' ?>><span><?= e(ucfirst($status)) ?></span></label><?php endforeach; ?></div></label>
                <label class="form-label checkbox-label full-width"><span>Advanced</span><div class="checkbox-grid"><label class="checkbox-row"><input type="checkbox" name="notification_include_spam" value="1" <?= !empty($siteNotificationSettings['include_spam']) ? 'checked' : '' ?>> Include spam notifications</label><label class="checkbox-row"><input type="checkbox" name="notification_include_admin_link" value="1" <?= !empty($siteNotificationSettings['include_admin_link']) ? 'checked' : '' ?>> Include backend detail link</label></div></label>
            </div>
        </div>

        <label class="form-label full-width">
            <span>Notes</span>
            <textarea name="notes" class="form-input" rows="5"><?= e($site['notes'] ?? '') ?></textarea>
        </label>

        <div class="full-width">
            <button type="submit" class="btn btn-primary">Save Site</button>
        </div>
    </form>
</div>

<div class="card mb-20">
    <div class="card-header"><h2>Current Secrets</h2></div>
    <div class="card-body filter-grid">
        <label class="form-label full-width">
            <span>Current API Token</span>
            <input type="text" class="form-input" value="<?= e($site['api_token']) ?>" readonly>
        </label>
        <form method="post" action="<?= e(base_url('sites/rotate-token')) ?>" class="full-width">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
            <label class="form-label full-width">
                <span>Rotate API Token</span>
                <input type="text" name="api_token" class="form-input" value="<?= e(random_token(32)) ?>" required>
            </label>
            <button type="submit" class="btn">Update API Token</button>
        </form>

        <label class="form-label full-width">
            <span>Current Signature Secret</span>
            <input type="text" class="form-input" value="<?= e($site['signature_secret'] ?: '-') ?>" readonly>
        </label>
        <form method="post" action="<?= e(base_url('sites/rotate-signature-secret')) ?>" class="full-width">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
            <label class="form-label full-width">
                <span>Rotate Signature Secret</span>
                <input type="text" name="signature_secret" class="form-input" value="<?= e(random_token(48)) ?>" required>
            </label>
            <button type="submit" class="btn">Update Signature Secret</button>
        </form>
    </div>
</div>
