<div class="stats-grid stats-grid-five">
    <div class="card stat-card"><div class="stat-label">Total Inquiries</div><div class="stat-value"><?= e((string) $stats['total']) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Unread</div><div class="stat-value"><?= e((string) $stats['unread']) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Spam</div><div class="stat-value"><?= e((string) $stats['spam']) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Today</div><div class="stat-value"><?= e((string) $stats['today']) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Export Templates</div><div class="stat-value"><?= e((string) $exportTemplateCount) ?></div></div>
</div>

<div class="stats-grid stats-grid-three mb-20">
    <div class="card stat-card"><div class="stat-label">My Open Follow-ups</div><div class="stat-value"><?= e((string) $openFollowupsCount) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Overdue Reminders</div><div class="stat-value"><?= e((string) ($followupReminderStats['overdue_count'] ?? 0)) ?></div></div>
    <div class="card stat-card"><div class="stat-label">Due Today / Next 7</div><div class="stat-value"><?= e((string) (($followupReminderStats['today_count'] ?? 0) . ' / ' . ($followupReminderStats['next7_count'] ?? 0))) ?></div></div>
</div>

<div class="dashboard-grid dashboard-grid-3">
    <section class="card">
        <div class="card-header"><h2>v0.8.0 Focus</h2></div>
        <div class="card-body">
            <ul class="plain-list">
                <li>API request logs now have a dedicated detail page.</li>
                <li>Sites can inherit, disable or override notification delivery.</li>
                <li>Inquiry export templates can be saved and reused.</li>
                <li>Follow-up reminders now have a dedicated working page.</li>
                <li>Inquiry detail adds one-click copy actions for sales work.</li>
            </ul>
        </div>
    </section>
    <section class="card">
        <div class="card-header split-header"><h2>API Quick Start</h2><a class="btn btn-sm" href="<?= e(base_url('sites')) ?>">Manage Sites</a></div>
        <div class="card-body">
            <p class="muted mb-12">Receive endpoint</p>
            <pre class="code-box"><?= e($apiEndpoint) ?></pre>
            <p class="muted mt-16 mb-12">Signed request format</p>
            <pre class="code-box">signature = HMAC_SHA256(X-Timestamp + "\n" + raw_body, signature_secret)</pre>
        </div>
    </section>
    <section class="card">
        <div class="card-header"><h2>Notification Status</h2></div>
        <div class="card-body">
            <div class="simple-list compact-list">
                <div class="simple-list-item compact-item"><div class="simple-list-title">Enabled</div><div class="simple-list-meta"><?= !empty($notificationSettings['enabled']) ? 'Yes' : 'No' ?></div></div>
                <div class="simple-list-item compact-item"><div class="simple-list-title">Transport</div><div class="simple-list-meta"><?= e((string) ($notificationSettings['transport'] ?? 'log_only')) ?></div></div>
                <div class="simple-list-item compact-item"><div class="simple-list-title">Recipients</div><div class="simple-list-meta"><?= e(implode(', ', $notificationSettings['recipients'] ?? [])) ?: '-' ?></div></div>
            </div>
        </div>
    </section>
</div>

<div class="dashboard-grid mt-20">
    <section class="card">
        <div class="card-header split-header"><h2>Upcoming Follow-up Reminders</h2><a class="btn btn-sm" href="<?= e(base_url('followup-reminders')) ?>">Open Reminders</a></div>
        <div class="card-body">
            <?php if (empty($upcomingFollowups)): ?>
                <p class="muted">No follow-up reminders scheduled.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($upcomingFollowups as $row): ?>
                        <?php $isOverdue = !empty($row['next_contact_at']) && strtotime((string) $row['next_contact_at']) < time(); ?>
                        <a class="simple-list-item compact-item" href="<?= e(base_url('inquiry?id=' . (int) $row['inquiry_id'])) ?>">
                            <div class="split-header"><div class="simple-list-title"><?= e($row['name'] ?: 'Inquiry #' . $row['inquiry_id']) ?></div><span class="<?= $isOverdue ? 'badge-warning' : 'badge-neutral' ?>"><?= $isOverdue ? 'Overdue' : 'Open' ?></span></div>
                            <div class="simple-list-meta"><?= e($row['site_name'] ?: 'Unknown site') ?> · <?= e($row['followup_type']) ?></div>
                            <div class="simple-list-meta mt-8"><?= e((string) ($row['next_contact_at'] ?: 'No next contact time')) ?></div>
                            <div class="simple-list-meta mt-8"><?= e(mb_strimwidth((string) $row['content'], 0, 120, '...')) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card-header split-header"><h2>7-Day Inquiry Trend</h2><a class="btn btn-sm" href="<?= e(base_url('reports/stats')) ?>">Open Reports</a></div>
        <div class="card-body">
            <?php $maxTrend = 1; foreach ($trendRows as $trendItem) { $maxTrend = max($maxTrend, (int) $trendItem['total_count']); } ?>
            <div class="trend-list">
                <?php foreach ($trendRows as $trend): ?>
                    <div class="trend-row">
                        <div class="trend-meta"><strong><?= e($trend['label']) ?></strong><span class="muted">T <?= e((string) $trend['total_count']) ?> · U <?= e((string) $trend['unread_count']) ?> · S <?= e((string) $trend['spam_count']) ?></span></div>
                        <div class="trend-bar"><span style="width: <?= (int) round(((int) $trend['total_count'] / $maxTrend) * 100) ?>%"></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<div class="dashboard-grid mt-20">
    <section class="card">
        <div class="card-header split-header"><h2>Recent API Traffic</h2><a class="btn btn-sm" href="<?= e(base_url('api-logs')) ?>">Open API Logs</a></div>
        <div class="card-body">
            <?php if (empty($recentApiLogs)): ?>
                <p class="muted">No API traffic yet.</p>
            <?php else: ?>
                <div class="simple-list compact-list">
                    <?php foreach ($recentApiLogs as $log): ?>
                        <a class="simple-list-item compact-item" href="<?= e(base_url('api-log?id=' . (int) $log['id'])) ?>">
                            <div class="split-header"><div class="simple-list-title"><?= e($log['site_name'] ?: ($log['site_key'] ?: 'Unknown')) ?></div><span class="<?= (int) $log['response_status'] >= 400 ? 'badge-warning' : 'badge-success' ?>"><?= e((string) $log['response_status']) ?></span></div>
                            <div class="simple-list-meta"><?= e($log['request_method']) ?> <?= e($log['endpoint']) ?></div>
                            <div class="simple-list-meta mt-8"><?= e($log['result_code'] ?: '-') ?> · <?= e((string) $log['created_at']) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card-header"><h2>Top Forms / Countries</h2></div>
        <div class="card-body two-col-list">
            <div>
                <div class="section-mini-title mb-12">Top Forms</div>
                <div class="simple-list compact-list">
                    <?php foreach ($topForms as $form): ?>
                        <div class="simple-list-item compact-item"><div class="simple-list-title"><?= e($form['form_key']) ?></div><div class="simple-list-meta"><?= e((string) $form['total_count']) ?> inquiries</div></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <div class="section-mini-title mb-12">Top Countries</div>
                <div class="simple-list compact-list">
                    <?php foreach ($countrySummary as $country): ?>
                        <div class="simple-list-item compact-item"><div class="simple-list-title"><?= e($country['country']) ?></div><div class="simple-list-meta"><?= e((string) $country['total_count']) ?> inquiries</div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div>
