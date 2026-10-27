<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Users</h1>
<div class="two-col">
    <section>
        <h2>Add a collector</h2>
        <form method="post" action="<?= eco_e(eco_url('admin/users')) ?>" class="form">
            <label>Full name <input type="text" name="full_name" maxlength="120" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Phone (optional) <input type="text" name="phone"></label>
            <label>Temporary password (min. 8 characters) <input type="password" name="password" minlength="8" required></label>
            <button type="submit" class="btn">Create collector</button>
        </form>
    </section>
    <section>
        <h2>Accounts</h2>
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Points</th><th>Status</th><th class="actions-col">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): $self = ((int) $u['user_id'] === (int) $me); ?>
                <tr>
                    <td><?= eco_e($u['full_name']) ?><?= $self ? ' <span class="muted small">(you)</span>' : '' ?></td>
                    <td><?= eco_e($u['email']) ?></td>
                    <td>
                        <form method="post" action="<?= eco_e(eco_url('admin/users/' . (int) $u['user_id'] . '/role')) ?>" class="inline">
                            <select name="role" onchange="this.form.submit()" <?= $self ? 'disabled' : '' ?>>
                                <?php foreach (['resident', 'collector', 'admin'] as $role): ?>
                                    <option value="<?= $role ?>" <?= $u['role'] === $role ? 'selected' : '' ?>><?= $role ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td><?= number_format((int) $u['points_balance']) ?></td>
                    <td><span class="badge badge-<?= eco_e($u['status']) ?>"><?= eco_e($u['status']) ?></span></td>
                    <td class="actions-col">
                        <?php if (!$self): ?>
                            <form method="post" action="<?= eco_e(eco_url('admin/users/' . (int) $u['user_id'] . '/status')) ?>" class="inline">
                                <button type="submit" class="link"><?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        <?php else: ?>
                            <span class="muted small">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
