<div class="stats-grid stats-grid-five">
    <div class="card stat-card">
        <div class="stat-label">Total Inquiries</div>
        <div class="stat-value"><?= e((string) $stats['total']) ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Unread</div>
        <div class="stat-value"><?= e((string) $stats['unread']) ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Read</div>
        <div class="stat-value"><?= e((string) $stats['read']) ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Spam</div>
        <div class="stat-value"><?= e((string) $stats['spam']) ?></div>
    </div>
    <div class="card stat-card">
        <div class="stat-label">Today</div>
        <div class="stat-value"><?= e((string) $stats['today']) ?></div>
    </div>
</div>

<div class="dashboard-grid dashboard-grid-3">
    <section class="card">
        <div class="card-header">
            <h2>v0.3.0 Highlights</h2>
        </div>
        <div class="card-body">
            <ul class="plain-list">
                <li>Site management now supports create, edit and token rotation</li>
                <li>Optional HMAC signature verification is available per site</li>
                <li>Inquiry CSV export is ready from the list page</li>
                <li>System logs page is now available for audit and review</li>
                <li>Blocked IP entries can be added and removed from the backend</li>
                <li>Health API now returns the application version automatically</li>
            </ul>
        </div>
    </section>

    <section class="card">
        <div class="card-header split-header">
            <h2>API Quick Start</h2>
            <a class="btn btn-sm" href="<?= e(base_url('sites')) ?>">Manage Sites</a>
        </div>
        <div class="card-body">
            <p class="muted mb-12">Receive endpoint</p>
            <pre class="code-box"><?= e($apiEndpoint) ?></pre>
            <p class="muted mt-16 mb-12">Minimum payload</p>
            <pre class="code-box">site_key, api_token, name, email, content</pre>
            <p class="muted mt-16 mb-12">Optional security</p>
            <pre class="code-box">X-Timestamp + X-Signature (per site)</pre>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2>Configured Sites</h2>
        </div>
        <div class="card-body compact-list">
            <?php foreach ($sites as $site): ?>
                <div class="simple-list-item compact-item">
                    <div class="simple-list-title"><?= e($site['site_name']) ?></div>
                    <div class="simple-list-meta">
                        <?= e($site['site_domain']) ?> · <?= e($site['site_key']) ?> ·
                        <?= e((string) ($site['inquiry_total'] ?? 0)) ?> total ·
                        <?= (int) ($site['require_signature'] ?? 0) === 1 ? 'signed' : 'token only' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div class="dashboard-grid mt-20">
    <section class="card">
        <div class="card-header split-header">
            <h2>Latest Inquiries</h2>
            <a class="btn btn-sm" href="<?= e(base_url('inquiries')) ?>">Manage All</a>
        </div>
        <div class="card-body">
            <?php if (empty($latestInquiries)): ?>
                <p class="muted">No inquiry data yet.</p>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($latestInquiries as $item): ?>
                        <a href="<?= e(base_url('inquiry?id=' . (int) $item['id'])) ?>" class="simple-list-item">
                            <div class="simple-list-title"><?= e($item['title'] ?: 'No title') ?></div>
                            <div class="simple-list-meta">
                                <?= e($item['site_name'] ?: 'Unknown site') ?> · <?= e($item['name'] ?: 'Unknown') ?> · <?= e((string) $item['created_at']) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card-header split-header">
            <h2>Recent Logs</h2>
            <a class="btn btn-sm" href="<?= e(base_url('logs')) ?>">View Logs</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentLogs)): ?>
                <p class="muted">No logs available yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="simple-list-item compact-item">
                            <div class="simple-list-title"><?= e($log['action']) ?></div>
                            <div class="simple-list-meta">
                                <?= e($log['admin_nickname'] ?: $log['admin_username'] ?: 'System') ?> · <?= e((string) $log['created_at']) ?>
                            </div>
                            <?php if (!empty($log['action_note'])): ?>
                                <div class="simple-list-meta mt-8"><?= e($log['action_note']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
