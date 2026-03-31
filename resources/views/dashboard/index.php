<div class="stats-grid">
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
        <div class="stat-label">Trash</div>
        <div class="stat-value"><?= e((string) $stats['trash']) ?></div>
    </div>
</div>

<div class="dashboard-grid">
    <section class="card">
        <div class="card-header">
            <h2>Project Summary</h2>
        </div>
        <div class="card-body">
            <p>This is the initial scaffold version of the pure PHP + MySQL inquiry management system.</p>
            <ul class="plain-list">
                <li>Login and basic backend structure are ready</li>
                <li>Inquiry list and detail pages are connected to the database</li>
                <li>Site list and blocked IP list are ready</li>
                <li>The external receiving API will be added in the next version</li>
            </ul>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2>Latest Inquiries</h2>
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
</div>
