<div class="card small">
    <h2>Ingreso al sistema</h2>
    <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="index.php?page=login">
        <label>Usuario</label><input type="text" name="username" required>
        <label>Contraseña</label><input type="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
</div>
