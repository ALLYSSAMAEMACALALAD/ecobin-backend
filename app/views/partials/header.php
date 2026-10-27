<?php
$current_user = eco_user();
$nav = $current_user ? eco_nav($current_user['role']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1020">
    <title><?= eco_e($title ?? 'Eco-Bin') ?> | Eco-Bin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?= eco_e(eco_url('assets/css/app.css')) ?>">
</head>
<body>
<div class="sky" aria-hidden="true"></div>
<header class="topbar">
    <a class="brand" href="<?= eco_e(eco_url('')) ?>">
        <?php $mascot_class = 'mascot mascot-nav'; include APP_DIR . 'views/partials/mascot.php'; ?>
        <span class="brand-text">Eco-Bin</span>
    </a>
    <nav class="menu">
        <?php if ($current_user): ?>
            <?php foreach ($nav as $label => $path): ?>
                <a href="<?= eco_e(eco_url($path)) ?>"><?= eco_e($label) ?></a>
            <?php endforeach; ?>
            <a class="logout" href="<?= eco_e(eco_url('logout')) ?>">Log out</a>
        <?php else: ?>
            <a href="<?= eco_e(eco_url('faq')) ?>">FAQ</a>
            <a href="<?= eco_e(eco_url('login')) ?>">Log in</a>
            <a class="btn-small" href="<?= eco_e(eco_url('register')) ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php foreach (['success', 'error', 'info'] as $type):
        $message = eco_flash_get($type);
        if ($message): ?>
            <div class="alert alert-<?= $type ?>"><?= eco_e($message) ?></div>
        <?php endif;
    endforeach; ?>
