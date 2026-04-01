<div class="card mb-20">
    <div class="card-header split-header">
        <h2>Spam Rule Center</h2>
        <div class="muted">These rules are applied by the unified receive API</div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e(base_url('tools/spam-rules')) ?>" class="filter-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

            <label class="form-label checkbox-label">
                <span>Honeypot</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_honeypot" value="1" <?= !empty($rules['enable_honeypot']) ? 'checked' : '' ?>> Enable hidden honeypot field check</label>
            </label>
            <label class="form-label">
                <span>Honeypot Field</span>
                <input type="text" name="honeypot_field" class="form-input" value="<?= e((string) $rules['honeypot_field']) ?>">
            </label>

            <label class="form-label checkbox-label">
                <span>Link Check</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_link_check" value="1" <?= !empty($rules['enable_link_check']) ? 'checked' : '' ?>> Mark as spam when links exceed threshold</label>
            </label>
            <label class="form-label">
                <span>Link Threshold</span>
                <input type="number" name="spam_link_threshold" class="form-input" min="1" value="<?= e((string) $rules['spam_link_threshold']) ?>">
            </label>

            <label class="form-label checkbox-label">
                <span>Duplicate Check</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_duplicate_check" value="1" <?= !empty($rules['enable_duplicate_check']) ? 'checked' : '' ?>> Check same email + same content</label>
            </label>
            <label class="form-label">
                <span>Duplicate Window (minutes)</span>
                <input type="number" name="duplicate_window_minutes" class="form-input" min="1" value="<?= e((string) $rules['duplicate_window_minutes']) ?>">
            </label>

            <label class="form-label checkbox-label">
                <span>IP Rate Limit</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_ip_rate_limit" value="1" <?= !empty($rules['enable_ip_rate_limit']) ? 'checked' : '' ?>> Limit frequent submissions by IP</label>
            </label>
            <label class="form-label">
                <span>IP Window (minutes)</span>
                <input type="number" name="ip_rate_limit_window_minutes" class="form-input" min="1" value="<?= e((string) $rules['ip_rate_limit_window_minutes']) ?>">
            </label>
            <label class="form-label">
                <span>IP Max Count</span>
                <input type="number" name="ip_rate_limit_max" class="form-input" min="1" value="<?= e((string) $rules['ip_rate_limit_max']) ?>">
            </label>

            <label class="form-label checkbox-label">
                <span>Email Rate Limit</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_email_rate_limit" value="1" <?= !empty($rules['enable_email_rate_limit']) ? 'checked' : '' ?>> Limit frequent submissions by email</label>
            </label>
            <label class="form-label">
                <span>Email Window (minutes)</span>
                <input type="number" name="email_rate_limit_window_minutes" class="form-input" min="1" value="<?= e((string) $rules['email_rate_limit_window_minutes']) ?>">
            </label>
            <label class="form-label">
                <span>Email Max Count</span>
                <input type="number" name="email_rate_limit_max" class="form-input" min="1" value="<?= e((string) $rules['email_rate_limit_max']) ?>">
            </label>

            <label class="form-label checkbox-label full-width">
                <span>Keyword Check</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_keyword_check" value="1" <?= !empty($rules['enable_keyword_check']) ? 'checked' : '' ?>> Match keyword list in title or content</label>
            </label>
            <label class="form-label full-width">
                <span>Spam Keywords (one per line)</span>
                <textarea name="spam_keywords" class="form-input" rows="8"><?= e(implode("\n", $rules['spam_keywords'] ?? [])) ?></textarea>
            </label>

            <label class="form-label checkbox-label full-width">
                <span>Disposable Email Domains</span>
                <label class="checkbox-row"><input type="checkbox" name="enable_disposable_email_domains" value="1" <?= !empty($rules['enable_disposable_email_domains']) ? 'checked' : '' ?>> Mark temporary email domains as spam</label>
            </label>
            <label class="form-label full-width">
                <span>Disposable Domains (one per line)</span>
                <textarea name="disposable_email_domains" class="form-input" rows="8"><?= e(implode("\n", $rules['disposable_email_domains'] ?? [])) ?></textarea>
            </label>

            <div class="full-width">
                <button type="submit" class="btn btn-primary">Save Spam Rules</button>
            </div>
        </form>
    </div>
</div>
