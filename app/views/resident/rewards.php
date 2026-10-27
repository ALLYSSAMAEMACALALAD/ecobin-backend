<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Rewards</h1>
<div class="cards">
    <article class="card stat">
        <span class="muted">Your points</span>
        <strong><?= number_format((int) ($me['points_balance'] ?? 0)) ?> pts</strong>
    </article>
</div>
<h2>Available rewards</h2>
<?php if (empty($rewards)): ?>
    <p class="muted">No rewards are available right now.</p>
<?php else: ?>
    <div class="cards">
        <?php foreach ($rewards as $rw): $can = (int) $rw['stock'] > 0 && (int) ($me['points_balance'] ?? 0) >= (int) $rw['points_cost']; ?>
            <article class="card">
                <h3><?= eco_e($rw['name']) ?></h3>
                <p class="muted"><?= (int) $rw['points_cost'] ?> pts &middot; <?= (int) $rw['stock'] ?> left</p>
                <?php if ((int) $rw['stock'] < 1): ?>
                    <p class="muted">Out of stock</p>
                <?php else: ?>
                    <form method="post" action="<?= eco_e(eco_url('resident/rewards/' . (int) $rw['reward_id'] . '/redeem')) ?>">
                        <button type="submit" class="btn btn-small" <?= $can ? '' : 'disabled' ?>>Request reward</button>
                        <?php if (!$can): ?><p class="muted small">Not enough points yet.</p><?php endif; ?>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>My requests</h2>
<?php if (empty($mine)): ?>
    <p class="muted">You have not requested any rewards yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Requested</th><th>Reward</th><th>Points</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($mine as $r): ?>
            <tr>
                <td><?= eco_e($r['requested_at']) ?></td>
                <td><?= eco_e($r['reward_name']) ?></td>
                <td><?= (int) $r['points_used'] ?></td>
                <td><span class="badge badge-<?= eco_e($r['status']) ?>"><?= eco_e($r['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
