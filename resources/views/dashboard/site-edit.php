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
