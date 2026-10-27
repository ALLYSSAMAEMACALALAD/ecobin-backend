<?php include APP_DIR . 'views/partials/header.php'; ?>
<section class="hero">
    <?php $mascot_class = 'mascot mascot-hero'; include APP_DIR . 'views/partials/mascot.php'; ?>
    <div class="hero-text">
        <p class="eyebrow">✦ Smart waste collection ✦</p>
        <h1 class="hero-title">Eco-Bin</h1>
        <p class="hero-sub">An integrated smart waste collection and municipal recycling reward system. Recycle, earn points, and keep the neighborhood clean.</p>
        <div class="actions">
            <a class="btn" href="<?= eco_e(eco_url('register')) ?>">Create a resident account</a>
            <a class="btn btn-outline" href="<?= eco_e(eco_url('login')) ?>">Log in</a>
        </div>
    </div>
</section>
<section class="cards">
    <article class="card"><div class="card-icon">🗓️</div><h3>Collection schedules</h3><p>See upcoming pickups for your area.</p></article>
    <article class="card"><div class="card-icon">🌱</div><h3>Earn points</h3><p>Verified recyclable drop-offs add points you can redeem for rewards.</p></article>
    <article class="card"><div class="card-icon">📸</div><h3>Report dumping</h3><p>Send a report with a photo so the municipal office can act fast.</p></article>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
