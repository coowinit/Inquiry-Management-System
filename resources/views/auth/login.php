<div class="login-wrap">
    <div class="login-card">
        <div class="login-head">
            <h1>Login</h1>
            <p>Inquiry Management System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(base_url('login')) ?>" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

            <label class="form-label">
                <span>Username</span>
                <input type="text" name="username" value="<?= e((string) old('username')) ?>" class="form-input" required>
            </label>

            <label class="form-label">
                <span>Password</span>
                <input type="password" name="password" class="form-input" required>
            </label>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="login-note">
            <strong>Default:</strong> admin / Admin@123456
        </div>
    </div>
</div>
