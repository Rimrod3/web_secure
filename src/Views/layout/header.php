<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Web Secure</title>
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <?php if (isset($user_header_data) && $user_header_data): ?>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-spacer"></div>
            <ul class="nav-links">
                <li><a href="./">Home</a></li>
                <li><a href="professor">Professor</a></li>
                <li><a href="localization">Localization</a></li>
                <li><a href="about">About Us</a></li>
            </ul>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user_header_data->pseudo); ?></span>
                <span class="user-role">(<?php echo ucfirst($user_header_data->role); ?>)</span> |
                <a href="logout" class="logout-link">Déconnexion</a>
            </div>
        </nav>
    </header>
    <?php endif; ?>
<div class="container">
