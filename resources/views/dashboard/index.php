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
            <h2>v0.2.0 Highlights</h2>
        </div>
        <div class="card-body">
            <ul class="plain-list">
                <li>Unified receive API is now available</li>
                <li>site_key + api_token validation is enabled</li>
                <li>Required fields are validated before insert</li>
                <li>extra_data and raw_payload are both stored</li>
                <li>Blocked IP and basic spam checks are active</li>
                <li>Inquiry list now supports filters and quick status actions</li>
            </ul>
        </div>
    </section>

    <section class="card">
        <div class="card-header split-header">
            <h2>API Quick Start</h2>
            <a class="btn btn-sm" href="<?= e(base_url('sites')) ?>">View Sites</a>
        </div>
        <div class="card-body">
            <p class="muted mb-12">Receive endpoint</p>
            <pre class="code-box"><?= e($apiEndpoint) ?></pre>
            <p class="muted mt-16 mb-12">Required payload</p>
            <pre class="code-box">site_key, api_token, name, email, content</pre>
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
                    <div class="simple-list-meta"><?= e($site['site_domain']) ?> · <?= e($site['site_key']) ?> · <?= e($site['status']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<section class="card mt-20">
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
