<div class="grid">
    <?php if (in_array(current_user()['role'], ['administrador'])): ?>
        <a class="card link" href="index.php?page=services">Servicios</a>
        <a class="card link" href="index.php?page=reports">Reportes</a>
    <?php endif; ?>
    <?php if (in_array(current_user()['role'], ['administrador','asesor'])): ?>
        <a class="card link" href="index.php?page=advisor">Panel Asesor</a>
    <?php endif; ?>
    <a class="card link" href="index.php?page=kiosk">Kiosco</a>
    <a class="card link" href="index.php?page=tv">Pantalla TV</a>
</div>
