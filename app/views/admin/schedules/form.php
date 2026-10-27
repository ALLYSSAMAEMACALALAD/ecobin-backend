<?php
include APP_DIR . 'views/partials/header.php';
$action = $mode === 'create'
    ? eco_url('admin/schedules')
    : eco_url('admin/schedules/' . (int) $schedule['schedule_id']);
?>
<section class="narrow">
    <h1><?= eco_e($title) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= eco_e($action) ?>" class="form">
        <label>Collection point
            <select name="point_id" required>
                <option value="">-- choose --</option>
                <?php foreach ($points as $p): ?>
                    <option value="<?= (int) $p['point_id'] ?>" <?= ((string) ($values['point_id'] ?? '') === (string) $p['point_id']) ? 'selected' : '' ?>>
                        <?= eco_e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Waste type
            <select name="waste_type" required>
                <option value="">-- choose --</option>
                <?php foreach ($waste_types as $type): ?>
                    <option value="<?= eco_e($type) ?>" <?= (($values['waste_type'] ?? '') === $type) ? 'selected' : '' ?>><?= eco_e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Collection date
            <input type="date" name="collection_date" value="<?= eco_e($values['collection_date'] ?? '') ?>" required>
        </label>
        <label>Time window
            <input type="text" name="time_window" maxlength="50" placeholder="e.g. 7-9 AM" value="<?= eco_e($values['time_window'] ?? '') ?>" required>
        </label>
        <div class="row">
            <button type="submit" class="btn"><?= $mode === 'create' ? 'Save schedule' : 'Save changes' ?></button>
            <a href="<?= eco_e(eco_url('admin/schedules')) ?>">Cancel</a>
        </div>
    </form>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
