<div class="detail-actions">
    <div class="actions-wrap">
        <a href="<?= e(base_url('sites')) ?>" class="btn">Back to Sites</a>

        <form method="post" action="<?= e(base_url('sites/rotate-token')) ?>" class="inline-form">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
            <button type="submit" class="btn">Rotate API Token</button>
        </form>

        <form method="post" action="<?= e(base_url('sites/rotate-signature-secret')) ?>" class="inline-form">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
            <button type="submit" class="btn">Rotate Signature Secret</button>
        </form>
    </div>
</div>

<div class="card form-card">
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
            <span>Current API Token</span>
            <input type="text" class="form-input" value="<?= e($site['api_token']) ?>" readonly>
        </label>

        <label class="form-label full-width">
            <span>Current Signature Secret</span>
            <input type="text" class="form-input" value="<?= e($site['signature_secret'] ?: '-') ?>" readonly>
        </label>

        <label class="form-label full-width">
            <span>Notes</span>
            <textarea name="notes" class="form-input" rows="5"><?= e($site['notes'] ?? '') ?></textarea>
        </label>

        <div class="full-width">
            <button type="submit" class="btn btn-primary">Save Site</button>
        </div>
    </form>
</div>
