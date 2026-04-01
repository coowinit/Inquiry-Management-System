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
            <h2>v0.5.0 Highlights</h2>
        </div>
        <div class="card-body">
            <ul class="plain-list">
                <li>Email notification center added with log-only and PHP mail modes</li>
                <li>Blocked emails and blocked domains can now be managed from the backend</li>
                <li>CSV export now supports field selection from the inquiry list page</li>
                <li>Dashboard now shows a 7-day intake trend and top forms summary</li>
                <li>All new inquiry notifications write delivery results into system logs</li>
                <li>API receive flow now checks both blocked IPs and blocked email rules</li>
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
            <h2>Notification Status</h2>
        </div>
        <div class="card-body">
            <div class="simple-list compact-list">
                <div class="simple-list-item compact-item">
                    <div class="simple-list-title">Enabled</div>
                    <div class="simple-list-meta"><?= !empty($notificationSettings['enabled']) ? 'Yes' : 'No' ?></div>
                </div>
                <div class="simple-list-item compact-item">
                    <div class="simple-list-title">Transport</div>
                    <div class="simple-list-meta"><?= e((string) ($notificationSettings['transport'] ?? 'log_only')) ?></div>
                </div>
                <div class="simple-list-item compact-item">
                    <div class="simple-list-title">Recipients</div>
                    <div class="simple-list-meta"><?= e(implode(', ', $notificationSettings['recipients'] ?? [])) ?: '-' ?></div>
                </div>
                <div class="simple-list-item compact-item">
                    <div class="simple-list-title">Notify Statuses</div>
                    <div class="simple-list-meta"><?= e(implode(', ', $notificationSettings['notify_statuses'] ?? [])) ?></div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="dashboard-grid mt-20">
    <section class="card">
        <div class="card-header split-header">
            <h2>7-Day Inquiry Trend</h2>
            <span class="muted">Total / unread / spam</span>
        </div>
        <div class="card-body">
            <?php $maxTrend = 1; foreach ($trendRows as $trendItem) { $maxTrend = max($maxTrend, (int) $trendItem['total_count']); } ?>
            <div class="trend-list">
                <?php foreach ($trendRows as $trend): ?>
                    <div class="trend-row">
                        <div class="trend-meta">
                            <strong><?= e($trend['label']) ?></strong>
                            <span class="muted">T <?= e((string) $trend['total_count']) ?> · U <?= e((string) $trend['unread_count']) ?> · S <?= e((string) $trend['spam_count']) ?></span>
                        </div>
                        <div class="trend-bar-bg">
                            <div class="trend-bar" style="width: <?= e((string) max(8, (int) round(($trend['total_count'] / $maxTrend) * 100))) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header split-header">
            <h2>Top Forms</h2>
            <a class="btn btn-sm" href="<?= e(base_url('inquiries')) ?>">Open List</a>
        </div>
        <div class="card-body">
            <?php if (empty($topForms)): ?>
                <p class="muted">No form data yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($topForms as $form): ?>
                        <div class="simple-list-item compact-item">
                            <div class="simple-list-title"><?= e($form['form_key']) ?></div>
                            <div class="simple-list-meta"><?= e($form['site_name']) ?> · <?= e((string) $form['total_count']) ?> total · <?= e((string) $form['unread_count']) ?> unread</div>
                            <div class="simple-list-meta mt-8">Last inquiry: <?= e((string) ($form['last_inquiry_at'] ?: '-')) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="dashboard-grid dashboard-grid-3 mt-20">
    <section class="card">
        <div class="card-header split-header">
            <h2>Configured Sites</h2>
            <a class="btn btn-sm" href="<?= e(base_url('sites')) ?>">Manage Sites</a>
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

    <section class="card">
        <div class="card-header">
            <h2>Top Countries</h2>
        </div>
        <div class="card-body">
            <?php if (empty($countrySummary)): ?>
                <p class="muted">No country data yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($countrySummary as $row): ?>
                        <div class="simple-list-item compact-item split-header">
                            <div class="simple-list-title"><?= e($row['country_name']) ?></div>
                            <div class="badge-neutral"><?= e((string) $row['total_count']) ?></div>
                        </div>
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
            <h2>Notification Notes</h2>
            <a class="btn btn-sm" href="<?= e(base_url('tools/email-notifications')) ?>">Configure</a>
        </div>
        <div class="card-body">
            <ul class="plain-list">
                <li><strong>log_only</strong> keeps delivery attempts inside system logs for safe testing.</li>
                <li><strong>mail</strong> uses PHP <code>mail()</code>, so the server must already support outbound email.</li>
                <li>Spam notifications stay off unless you explicitly enable them.</li>
                <li>Recipients can be entered one per line or comma separated.</li>
            </ul>
        </div>
    </section>
</div>
