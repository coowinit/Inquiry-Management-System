<div class="card mb-20">
    <div class="card-header"><h2>Create Admin User</h2></div>
    <div class="card-body">
        <form method="post" action="<?= e(base_url('admins/create')) ?>" class="filter-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
            <label class="form-label"><span>Username</span><input type="text" name="username" class="form-input" required></label>
            <label class="form-label"><span>Nickname</span><input type="text" name="nickname" class="form-input"></label>
            <label class="form-label"><span>Email</span><input type="email" name="email" class="form-input" required></label>
            <label class="form-label"><span>Password</span><input type="password" name="password" class="form-input" required></label>
            <label class="form-label"><span>Role</span><select name="role" class="form-input"><?php foreach (['admin','manager','agent','viewer'] as $role): ?><option value="<?= e($role) ?>"><?= e(ucfirst($role)) ?></option><?php endforeach; ?></select></label>
            <label class="form-label"><span>Status</span><select name="status" class="form-input"><?php foreach (['active','disabled'] as $status): ?><option value="<?= e($status) ?>"><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
            <div class="filter-actions full-width"><button type="submit" class="btn btn-primary">Create User</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header split-header"><h2>Admin Users</h2><div class="muted">Total: <?= e((string) $pagination['total']) ?></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>User</th><th>Role</th><th>Status</th><th>Assigned Inquiries</th><th>Last Login</th><th>Update</th></tr></thead><tbody>
    <?php if (empty($pagination['data'])): ?><tr><td colspan="7" class="empty-cell">No users found.</td></tr><?php else: ?>
        <?php foreach ($pagination['data'] as $row): ?>
            <tr>
                <td>#<?= e((string) $row['id']) ?></td>
                <td><div class="table-title"><?= e($row['nickname'] ?: $row['username']) ?></div><div class="table-sub"><?= e($row['username']) ?> · <?= e($row['email']) ?></div></td>
                <td>
                    <form method="post" action="<?= e(base_url('admins/update-meta')) ?>" class="inline-form">
                        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <select name="role" class="form-input form-input-sm"><?php foreach (['admin','manager','agent','viewer'] as $role): ?><option value="<?= e($role) ?>" <?= ($row['role'] ?? 'admin') === $role ? 'selected' : '' ?>><?= e(ucfirst($role)) ?></option><?php endforeach; ?></select>
                </td>
                <td><select name="status" class="form-input form-input-sm"><?php foreach (['active','disabled'] as $status): ?><option value="<?= e($status) ?>" <?= ($row['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></td>
                <td><?= e((string) ($row['assigned_inquiry_count'] ?? 0)) ?></td>
                <td><?= e((string) ($row['last_login_at'] ?: '-')) ?></td>
                <td><button type="submit" class="btn btn-sm">Save</button></form></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody></table></div>
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?><div class="pagination"><?php for ($i = 1; $i <= (int) $pagination['total_pages']; $i++): ?><a class="page-link <?= $i === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= e(url_with_query('admins', ['page' => $i])) ?>"><?= e((string) $i) ?></a><?php endfor; ?></div><?php endif; ?>
</div>
