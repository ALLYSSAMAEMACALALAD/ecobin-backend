<?php include APP_DIR . 'views/partials/header.php'; ?>
<div class="row between">
    <h1>Collection schedules</h1>
    <a class="btn" href="<?= eco_e(eco_url('admin/schedules/create')) ?>">+ Add schedule</a>
</div>
<?php if (empty($schedules)): ?>
    <p class="muted">No schedules yet. Add the first one.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Date</th><th>Time</th><th>Point</th><th>Waste type</th><th class="actions-col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($schedules as $row): ?>
            <tr>
                <td><?= eco_e($row['collection_date']) ?></td>
                <td><?= eco_e($row['time_window']) ?></td>
                <td><?= eco_e($row['point_name']) ?></td>
                <td><?= eco_e($row['waste_type']) ?></td>
                <td class="actions-col">
                    <a href="<?= eco_e(eco_url('admin/schedules/' . (int) $row['schedule_id'])) ?>">View</a> ·
                    <a href="<?= eco_e(eco_url('admin/schedules/' . (int) $row['schedule_id'] . '/edit')) ?>">Edit</a> ·
                    <form method="post" action="<?= eco_e(eco_url('admin/schedules/' . (int) $row['schedule_id'] . '/delete')) ?>" class="inline"
                          onsubmit="return confirm('Delete this schedule? This cannot be undone.');">
                        <button type="submit" class="link-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
