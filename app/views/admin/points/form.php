<?php
include APP_DIR . 'views/partials/header.php';
$action = $mode === 'create' ? eco_url('admin/points') : eco_url('admin/points/' . (int) $point['point_id']);
?>
<section class="narrow wide">
    <h1><?= eco_e($title) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= eco_e($action) ?>" class="form">
        <label>Name <input type="text" name="name" maxlength="120" required value="<?= eco_e($values['name'] ?? '') ?>"></label>
        <label>Address <input type="text" name="address" maxlength="255" required value="<?= eco_e($values['address'] ?? '') ?>"></label>
        <label>Type
            <select name="point_type" required>
                <?php foreach (['hub' => 'Drop-off hub', 'bin' => 'Collection bin'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= (($values['point_type'] ?? 'bin') === $val) ? 'selected' : '' ?>><?= eco_e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="row">
            <label class="grow">Latitude (optional) <input type="text" name="latitude" value="<?= eco_e($values['latitude'] ?? '') ?>" placeholder="14.5995"></label>
            <label class="grow">Longitude (optional) <input type="text" name="longitude" value="<?= eco_e($values['longitude'] ?? '') ?>" placeholder="120.9842"></label>
        </div>
        <div class="row">
            <button type="submit" class="btn"><?= $mode === 'create' ? 'Save point' : 'Save changes' ?></button>
            <a href="<?= eco_e(eco_url('admin/points')) ?>">Cancel</a>
        </div>
    </form>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
