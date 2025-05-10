<?php
require_once 'config.php';
require_once 'auth.php';

$page_title = $page_title ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($custom_css)): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $custom_css ?>">
    <?php endif; ?>
</head>

<body>

    <header class="bg-dark text-white">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
                    <i class="fas fa-plane"></i> <?= SITE_NAME ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/flights/search.php">Flights</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/status.php">Flight Status</a></li>
                        <?php if (is_logged_in()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/dashboard.php">My Bookings</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/user/profile.php">Profile</a></li>
                            <?php if (is_admin()): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">Admin</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/auth/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <main class="container py-4">