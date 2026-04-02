<div class="card mb-20">
    <div class="card-header split-header">
        <div>
            <h2>Edit Admin User</h2>
            <div class="muted mt-1">Manage profile details and disable access safely.</div>
        </div>
        <a href="<?= e(base_url('admins')) ?>" class="btn btn-soft btn-sm">← Back to Admin Users</a>
    </div>
    <div class="card-body">
        <div class="admin-edit-meta mb-20">
            <div class="simple-list-item compact-item">
                <div class="simple-list-meta">Username</div>
                <div class="table-title"><?= e((string) $adminUser['username']) ?></div>
            </div>
            <div class="simple-list-item compact-item">
                <div class="simple-list-meta">Last Login</div>
                <div class="table-title"><?= e((string) ($adminUser['last_login_at'] ?: '-')) ?></div>
            </div>
            <div class="simple-list-item compact-item">
                <div class="simple-list-meta">Current Status</div>
                <div class="table-title"><?= e(ucfirst((string) ($adminUser['status'] ?? 'active'))) ?></div>
            </div>
        </div>

        <form method="post" action="<?= e(base_url('admins/update')) ?>" class="filter-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $adminUser['id'] ?>">

            <label class="form-label">
                <span>Username</span>
                <input type="text" class="form-input" value="<?= e((string) $adminUser['username']) ?>" disabled>
            </label>
            <label class="form-label">
                <span>Nickname</span>
                <input type="text" name="nickname" class="form-input" value="<?= e((string) ($adminUser['nickname'] ?? '')) ?>" required>
            </label>
            <label class="form-label">
                <span>Email</span>
                <input type="email" name="email" class="form-input" value="<?= e((string) ($adminUser['email'] ?? '')) ?>" required>
            </label>
            <label class="form-label">
                <span>Role</span>
                <select name="role" class="form-input"><?php foreach (['admin','manager','agent','viewer'] as $role): ?><option value="<?= e($role) ?>" <?= ($adminUser['role'] ?? 'admin') === $role ? 'selected' : '' ?>><?= e(ucfirst($role)) ?></option><?php endforeach; ?></select>
            </label>
            <label class="form-label">
                <span>Status</span>
                <select name="status" class="form-input"><?php foreach (['active','disabled'] as $status): ?><option value="<?= e($status) ?>" <?= ($adminUser['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select>
            </label>
            <div></div>

            <label class="form-label">
                <span>New Password</span>
                <input type="password" name="password" class="form-input" placeholder="Leave blank to keep current password">
            </label>
            <label class="form-label">
                <span>Confirm New Password</span>
                <input type="password" name="password_confirm" class="form-input" placeholder="Repeat the new password">
            </label>
            <div class="full-width filter-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>

        <div class="admin-danger-zone mt-20">
            <div>
                <h3 class="mb-1">Quick Disable</h3>
                <p class="muted mb-0">Disable access without deleting the account. Historical logs and assignments stay intact.</p>
            </div>
            <form method="post" action="<?= e(base_url('admins/toggle-status')) ?>" class="inline-form" onsubmit="return confirm('<?= e(($adminUser['status'] ?? 'active') === 'disabled' ? 'Enable this account?' : 'Disable this account?') ?>');">
                <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= (int) $adminUser['id'] ?>">
                <input type="hidden" name="target_status" value="<?= e(($adminUser['status'] ?? 'active') === 'disabled' ? 'active' : 'disabled') ?>">
                <button type="submit" class="btn <?= ($adminUser['status'] ?? 'active') === 'disabled' ? 'btn-soft' : 'btn-primary' ?>">
                    <?= e(($adminUser['status'] ?? 'active') === 'disabled' ? 'Enable Account' : 'Disable Account') ?>
                </button>
            </form>
        </div>
    </div>
</div>
