<div class="card mb-20">
    <div class="card-header split-header">
        <h2>Receive API</h2>
        <div class="muted">Use these credentials from your site backend</div>
    </div>
    <div class="card-body">
        <p class="muted mb-12">POST endpoint</p>
        <pre class="code-box"><?= e($apiEndpoint) ?></pre>
        <p class="muted mt-16 mb-12">Required fields</p>
        <pre class="code-box">site_key, api_token, name, email, content</pre>
    </div>
</div>

<div class="card">
    <div class="card-header split-header">
        <h2>Site List</h2>
        <div class="muted">Configured source websites</div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Name</th>
                    <th>Domain</th>
                    <th>Site Key</th>
                    <th>API Token</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sites)): ?>
                    <tr>
                        <td colspan="7" class="empty-cell">No sites found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sites as $site): ?>
                        <tr>
                            <td>#<?= e((string) $site['id']) ?></td>
                            <td><?= e($site['site_name']) ?></td>
                            <td><?= e($site['site_domain']) ?></td>
                            <td><code><?= e($site['site_key']) ?></code></td>
                            <td><code><?= e($site['api_token']) ?></code></td>
                            <td><span class="status-pill <?= $site['status'] === 'active' ? 'status-read' : 'status-trash' ?>"><?= e($site['status']) ?></span></td>
                            <td><?= e((string) $site['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
