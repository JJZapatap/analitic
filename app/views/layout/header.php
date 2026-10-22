<?php $u = current_user(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digiturno</title>
    <link rel="stylesheet" href="public/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="nav">
    <h1>Digiturno</h1>
    <div>
        <?php if ($u): ?>
            <span><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role']) ?>)</span>
            <a href="index.php?page=dashboard">Inicio</a>
            <a href="index.php?page=logout">Salir</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
