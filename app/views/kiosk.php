<div class="card small center">
<h2>Kiosco de Turnos</h2>
<?php if(!empty($generated)): ?><p class="ticket">Tu turno: <?= htmlspecialchars($generated) ?></p><?php endif; ?>
<form method="post">
<select name="service_id" required>
<?php foreach($services as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
</select>
<button>Generar turno</button>
</form></div>
