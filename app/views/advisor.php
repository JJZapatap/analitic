<div class="grid two">
<div class="card">
<h2>Turno actual</h2>
<?php if($current): ?><h3><?= $current['code'] ?> - <?= htmlspecialchars($current['service']) ?></h3>
<form method="post" class="inline-form">
<input type="hidden" name="ticket_id" value="<?= $current['id'] ?>">
<button name="action" value="repeat">Repetir</button>
<button name="action" value="finish">Finalizar</button>
<button name="action" value="absent">Ausente</button>
<select name="new_service_id"><?php foreach($services as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?></select>
<button name="action" value="transfer">Transferir</button>
</form><?php else: ?><p>Sin turno en atención</p><?php endif; ?>
<form method="post"><button name="action" value="call_next">Llamar siguiente</button></form>
</div>
<div class="card"><h2>En espera</h2><ul><?php foreach($waiting as $w): ?><li><?= $w['code'] ?> - <?= htmlspecialchars($w['service']) ?></li><?php endforeach; ?></ul></div>
</div>
