<?php include APP_DIR . 'views/partials/header.php'; ?>
<section class="narrow">
    <h1>Log in</h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= eco_e(eco_url('login')) ?>" class="form">
        <label>Email
            <input type="email" name="email" value="<?= eco_e($old['email'] ?? '') ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn">Log in</button>
    </form>
    <p class="muted">New resident? <a href="<?= eco_e(eco_url('register')) ?>">Create an account</a>.</p>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
