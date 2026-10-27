<?php include APP_DIR . 'views/partials/header.php'; ?>
<section class="narrow">
    <h1>Create a resident account</h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= eco_e(eco_url('register')) ?>" class="form">
        <label>Full name <input type="text" name="full_name" maxlength="120" value="<?= eco_e($old['full_name'] ?? '') ?>" required></label>
        <label>Email <input type="email" name="email" value="<?= eco_e($old['email'] ?? '') ?>" required></label>
        <label>Phone (optional) <input type="text" name="phone" value="<?= eco_e($old['phone'] ?? '') ?>"></label>
        <label>Password (min. 8 characters) <input type="password" name="password" required></label>
        <label>Confirm password <input type="password" name="password_confirm" required></label>
        <button type="submit" class="btn">Create account</button>
    </form>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
