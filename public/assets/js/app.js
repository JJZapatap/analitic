if (window.tvMode) {
  const refresh = async () => {
    const res = await fetch('index.php?page=tv-data');
    const rows = await res.json();
    const table = document.getElementById('tv-table');
    table.innerHTML = '<tr><th>Turno</th><th>Servicio</th><th>Asesor</th><th>Hora</th></tr>';
    rows.forEach(r => {
      table.innerHTML += `<tr><td>${r.code}</td><td>${r.service}</td><td>${r.advisor || ''}</td><td>${r.called_at || ''}</td></tr>`;
    });
  };
  refresh();
  setInterval(refresh, 3000);
}

if (window.reportData) {
  const c = document.getElementById('reportChart');
  const states = {};
  window.reportData.forEach(r => states[r.status] = (states[r.status] || 0) + 1);
  new Chart(c, {
    type: 'bar',
    data: { labels: Object.keys(states), datasets: [{ label: 'Turnos por estado', data: Object.values(states), backgroundColor: '#2f80ed' }] }
  });
}
