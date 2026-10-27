<?php include APP_DIR . 'views/partials/header.php'; ?>
<section class="narrow">
    <h1><?= eco_e($title) ?></h1>
    <p>This page is in place so the navigation and routing work. <?= eco_e($note ?? '') ?></p>
    <p><a href="javascript:history.back()">&larr; Go back</a></p>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
