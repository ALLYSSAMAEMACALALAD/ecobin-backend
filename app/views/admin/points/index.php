<?php include APP_DIR . 'views/partials/header.php'; ?>
<div class="row between">
    <h1>Collection points</h1>
    <a class="btn" href="<?= eco_e(eco_url('admin/points/create')) ?>">+ Add point</a>
</div>
<?php if (empty($points)): ?>
    <p class="muted">No collection points yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Name</th><th>Type</th><th>Address</th><th>Coordinates</th><th>In use</th><th class="actions-col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($points as $p): ?>
            <tr>
                <td><?= eco_e($p['name']) ?></td>
                <td><?= eco_e($p['point_type']) ?></td>
                <td><?= eco_e($p['address']) ?></td>
                <td><?= $p['latitude'] !== null ? eco_e($p['latitude'] . ', ' . $p['longitude']) : '<span class="muted">not set</span>' ?></td>
                <td><?= (int) $p['in_use'] ?></td>
                <td class="actions-col">
                    <a href="<?= eco_e(eco_url('admin/points/' . (int) $p['point_id'] . '/edit')) ?>">Edit</a>
                    <?php if ((int) $p['in_use'] === 0): ?>
                        &middot;
                        <form method="post" action="<?= eco_e(eco_url('admin/points/' . (int) $p['point_id'] . '/delete')) ?>" class="inline"
                              onsubmit="return confirm('Delete this collection point?');">
                            <button type="submit" class="link-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted small">A point that has schedules or drop-off records cannot be deleted.</p>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
