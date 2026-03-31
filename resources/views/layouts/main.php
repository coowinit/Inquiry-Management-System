<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($pageTitle ?? 'System') . ' - ' . config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-title"><?= e(config('app.name')) ?></div>
            <div class="brand-version">v0.2.0</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group-title">Main</div>
            <a href="<?= e(base_url('dashboard')) ?>" class="nav-link <?= request_path() === '/dashboard' ? 'is-active' : '' ?>">Dashboard</a>
            <a href="<?= e(base_url('inquiries')) ?>" class="nav-link <?= request_path() === '/inquiries' || request_path() === '/inquiry' ? 'is-active' : '' ?>">Inquiry Management</a>
            <a href="<?= e(base_url('sites')) ?>" class="nav-link <?= request_path() === '/sites' ? 'is-active' : '' ?>">Sites & API</a>

            <div class="nav-group-title">Tools</div>
            <a href="<?= e(base_url('tools/blacklist-ips')) ?>" class="nav-link <?= request_path() === '/tools/blacklist-ips' ? 'is-active' : '' ?>">Blocked IPs</a>

            <div class="nav-group-title">Settings</div>
            <a href="<?= e(base_url('profile')) ?>" class="nav-link <?= request_path() === '/profile' ? 'is-active' : '' ?>">My Profile</a>
            <a href="<?= e(base_url('logout')) ?>" class="nav-link">Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1 class="page-title"><?= e($pageTitle ?? '') ?></h1>
            </div>
            <div class="topbar-user">
                <?php $authUser = \App\Core\Auth::user(); ?>
                <span><?= e($authUser['nickname'] ?? $authUser['username'] ?? 'Admin') ?></span>
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
</body>
</html>
