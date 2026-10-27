<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Collection schedule</h1>
<?php if (empty($schedules)): ?>
    <p class="muted">No upcoming collections are scheduled.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Date</th><th>Time</th><th>Point</th><th>Address</th><th>Waste type</th></tr></thead>
        <tbody>
        <?php foreach ($schedules as $row): ?>
            <tr>
                <td><?= eco_e($row['collection_date']) ?></td>
                <td><?= eco_e($row['time_window']) ?></td>
                <td><?= eco_e($row['point_name']) ?></td>
                <td><?= eco_e($row['point_address']) ?></td>
                <td><?= eco_e($row['waste_type']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
