<div class="card">
<h2>Servicios</h2>
<form method="post" class="inline-form">
<input name="name" placeholder="Nombre" required>
<input name="prefix" placeholder="Prefijo (A,B,C)" required maxlength="3">
<button>Guardar</button>
</form>
<table><tr><th>ID</th><th>Nombre</th><th>Prefijo</th></tr>
<?php foreach($services as $s): ?><tr><td><?= $s['id'] ?></td><td><?= htmlspecialchars($s['name']) ?></td><td><?= htmlspecialchars($s['prefix']) ?></td></tr><?php endforeach; ?>
</table></div>
