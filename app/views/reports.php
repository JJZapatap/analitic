<div class="card">
<h2>Reportes</h2>
<form method="get" class="inline-form">
<input type="hidden" name="page" value="reports">
<input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
<input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
<select name="service_id"><option value="">Servicio</option><?php foreach($services as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?></select>
<select name="advisor_id"><option value="">Asesor</option><?php foreach($advisors as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option><?php endforeach; ?></select>
<select name="status"><option value="">Estado</option><option>espera</option><option>llamado</option><option>finalizado</option><option>ausente</option></select>
<button>Filtrar</button>
</form>
<canvas id="reportChart" height="100"></canvas>
<table><tr><th>Turno</th><th>Estado</th><th>Espera(s)</th><th>Atención(s)</th></tr><?php foreach($rows as $r): ?><tr><td><?= $r['code'] ?></td><td><?= $r['status'] ?></td><td><?= (int)$r['wait_sec'] ?></td><td><?= (int)$r['att_sec'] ?></td></tr><?php endforeach; ?></table>
</div>
<script>window.reportData = <?= json_encode($rows) ?>;</script>
