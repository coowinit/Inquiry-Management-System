<div class="detail-actions">
    <div class="actions-wrap">
        <a href="<?= e(base_url('api-logs')) ?>" class="btn">Back to API Logs</a>
    </div>
</div>

<div class="detail-grid mt-20">
    <div class="detail-section">
        <h2>Request Overview</h2>
        <dl class="detail-list">
            <div><dt>Log ID</dt><dd>#<?= e((string) $log['id']) ?></dd></div>
            <div><dt>Site</dt><dd><?= e($log['site_name'] ?: ($log['site_key'] ?: 'Unknown')) ?></dd></div>
            <div><dt>Site Key</dt><dd><?= e($log['site_key'] ?: '-') ?></dd></div>
            <div><dt>Endpoint</dt><dd><?= e($log['endpoint']) ?></dd></div>
            <div><dt>Method</dt><dd><?= e($log['request_method']) ?></dd></div>
            <div><dt>Request IP</dt><dd><?= e($log['request_ip'] ?: '-') ?></dd></div>
            <div><dt>Origin Host</dt><dd><?= e($log['origin_host'] ?: '-') ?></dd></div>
            <div><dt>Referer Host</dt><dd><?= e($log['referer_host'] ?: '-') ?></dd></div>
            <div><dt>Response Status</dt><dd><?= e((string) $log['response_status']) ?></dd></div>
            <div><dt>Result Code</dt><dd><?= e($log['result_code'] ?: '-') ?></dd></div>
            <div><dt>Result Message</dt><dd><?= e($log['result_message'] ?: '-') ?></dd></div>
            <div><dt>Created At</dt><dd><?= e((string) $log['created_at']) ?></dd></div>
        </dl>
    </div>
    <div class="detail-section">
        <h2>Quick Actions</h2>
        <div class="copy-actions-grid">
            <button type="button" class="btn" data-copy-text="<?= e($headersJson) ?>">Copy Headers JSON</button>
            <button type="button" class="btn" data-copy-text="<?= e($payloadJson) ?>">Copy Payload JSON</button>
            <button type="button" class="btn" data-copy-text="<?= e($responseJson) ?>">Copy Response JSON</button>
        </div>
        <p class="muted mt-12">These buttons copy the exact pretty-printed JSON shown below.</p>
    </div>
</div>

<div class="detail-section mt-20">
    <h2>Request Headers</h2>
    <pre class="code-box code-box-tall"><?= e($headersJson) ?></pre>
</div>

<div class="detail-section mt-20">
    <h2>Payload JSON</h2>
    <pre class="code-box code-box-tall"><?= e($payloadJson) ?></pre>
</div>

<div class="detail-section mt-20">
    <h2>Response JSON</h2>
    <pre class="code-box code-box-tall"><?= e($responseJson) ?></pre>
</div>
