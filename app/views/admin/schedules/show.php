<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Schedule #<?= (int) $schedule['schedule_id'] ?></h1>
<dl class="details">
    <dt>Date</dt><dd><?= eco_e($schedule['collection_date']) ?></dd>
    <dt>Time window</dt><dd><?= eco_e($schedule['time_window']) ?></dd>
    <dt>Collection point</dt><dd><?= eco_e($schedule['point_name']) ?></dd>
    <dt>Address</dt><dd><?= eco_e($schedule['point_address']) ?></dd>
    <dt>Waste type</dt><dd><?= eco_e($schedule['waste_type']) ?></dd>
</dl>
<div class="row">
    <a class="btn" href="<?= eco_e(eco_url('admin/schedules/' . (int) $schedule['schedule_id'] . '/edit')) ?>">Edit</a>
    <a href="<?= eco_e(eco_url('admin/schedules')) ?>">Back to list</a>
</div>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
