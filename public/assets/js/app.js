if (window.tvMode) {
  const refresh = async () => {
    const res = await fetch('index.php?page=tv-data');
    const rows = await res.json();
    const table = document.getElementById('tv-table');
    const header = document.createElement('tr');
    ['Turno', 'Servicio', 'Asesor', 'Hora'].forEach(label => {
      const cell = document.createElement('th');
      cell.textContent = label;
      header.appendChild(cell);
    });
    const body = document.createElement('tbody');
    rows.forEach(r => {
      const row = document.createElement('tr');
      [r.code, r.service, r.advisor, r.called_at].forEach(value => {
        const cell = document.createElement('td');
        cell.textContent = value ?? '';
        row.appendChild(cell);
      });
      body.appendChild(row);
    });
    const head = document.createElement('thead');
    head.appendChild(header);
    table.replaceChildren(head, body);
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
