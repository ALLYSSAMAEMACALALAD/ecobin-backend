<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Redemptions</h1>
<h2>Pending requests</h2>
<?php if (empty($pending)): ?>
    <p class="muted">No pending requests.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Requested</th><th>Resident</th><th>Reward</th><th>Points</th><th class="actions-col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($pending as $r): ?>
            <tr>
                <td><?= eco_e($r['requested_at']) ?></td>
                <td><?= eco_e($r['resident_name']) ?></td>
                <td><?= eco_e($r['reward_name']) ?></td>
                <td><?= (int) $r['points_used'] ?></td>
                <td class="actions-col">
                    <form method="post" action="<?= eco_e(eco_url('admin/redemptions/' . (int) $r['redemption_id'] . '/approve')) ?>" class="inline">
                        <button type="submit" class="btn btn-small">Approve</button>
                    </form>
                    <form method="post" action="<?= eco_e(eco_url('admin/redemptions/' . (int) $r['redemption_id'] . '/reject')) ?>" class="inline"
                          onsubmit="return confirm('Reject this request?');">
                        <button type="submit" class="btn btn-small btn-outline">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>History</h2>
<?php if (empty($history)): ?>
    <p class="muted">No redemptions yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Requested</th><th>Resident</th><th>Reward</th><th>Points</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($history as $r): ?>
            <tr>
                <td><?= eco_e($r['requested_at']) ?></td>
                <td><?= eco_e($r['resident_name']) ?></td>
                <td><?= eco_e($r['reward_name']) ?></td>
                <td><?= (int) $r['points_used'] ?></td>
                <td><span class="badge badge-<?= eco_e($r['status']) ?>"><?= eco_e($r['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
