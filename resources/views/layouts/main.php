<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'System') . ' - ' . config('app.name')) ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="app-body">
<?php $authUser = \App\Core\Auth::user(); ?>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-title"><?= e(config('app.name')) ?></div>
            <div class="brand-version"><?= e(app_version()) ?></div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group-title">Main</div>
            <a href="<?= e(base_url('dashboard')) ?>" class="nav-link <?= request_path() === '/dashboard' ? 'is-active' : '' ?>">Dashboard</a>
            <a href="<?= e(base_url('reports/stats')) ?>" class="nav-link <?= request_path() === '/reports/stats' ? 'is-active' : '' ?>">Reports &amp; Analytics</a>
            <a href="<?= e(base_url('inquiries')) ?>" class="nav-link <?= request_path() === '/inquiries' || request_path() === '/inquiry' ? 'is-active' : '' ?>">Inquiry Management</a>
            <a href="<?= e(base_url('followup-reminders')) ?>" class="nav-link <?= request_path() === '/followup-reminders' ? 'is-active' : '' ?>">Follow-up Reminders</a>
            <?php if (\App\Core\Auth::can('sites.view')): ?>
                <a href="<?= e(base_url('sites')) ?>" class="nav-link <?= request_path() === '/sites' || request_path() === '/sites/edit' ? 'is-active' : '' ?>">Sites &amp; API</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('tools.view')): ?>
                <a href="<?= e(base_url('admins')) ?>" class="nav-link <?= request_path() === '/admins' ? 'is-active' : '' ?>">Admin Users</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('logs.view')): ?>
                <a href="<?= e(base_url('logs')) ?>" class="nav-link <?= request_path() === '/logs' ? 'is-active' : '' ?>">System Logs</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('api_logs.view')): ?>
                <a href="<?= e(base_url('api-logs')) ?>" class="nav-link <?= request_path() === '/api-logs' ? 'is-active' : '' ?>">API Request Logs</a>
            <?php endif; ?>

            <?php if (\App\Core\Auth::can('tools.view')): ?>
                <div class="nav-group-title">Tools</div>
                <a href="<?= e(base_url('tools/blacklist-ips')) ?>" class="nav-link <?= request_path() === '/tools/blacklist-ips' ? 'is-active' : '' ?>">Blocked IPs</a>
                <a href="<?= e(base_url('tools/blacklist-emails')) ?>" class="nav-link <?= request_path() === '/tools/blacklist-emails' ? 'is-active' : '' ?>">Blocked Emails</a>
                <a href="<?= e(base_url('tools/spam-rules')) ?>" class="nav-link <?= request_path() === '/tools/spam-rules' ? 'is-active' : '' ?>">Spam Rule Center</a>
                <a href="<?= e(base_url('tools/email-notifications')) ?>" class="nav-link <?= request_path() === '/tools/email-notifications' ? 'is-active' : '' ?>">Email Notifications</a>
            <?php endif; ?>

            <div class="nav-group-title">Settings</div>
            <a href="<?= e(base_url('profile')) ?>" class="nav-link <?= request_path() === '/profile' ? 'is-active' : '' ?>">My Profile</a>
            <a href="<?= e(base_url('logout')) ?>" class="nav-link">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title"><?= e($pageTitle ?? '') ?></h1>
                <?php if (!empty($pageSubtitle ?? '')): ?>
                    <p class="page-subtitle mb-0"><?= e((string) $pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="topbar-user">
                <span><?= e(($authUser['nickname'] ?? $authUser['username'] ?? 'Admin') . ' · ' . strtoupper((string) ($authUser['role'] ?? 'admin'))) ?></span>
            </div>
        </header>

        <?php if ($success = get_flash('success')): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
