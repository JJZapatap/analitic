const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const test = require('node:test');

// Minimal DOM double: any HTML sink fails immediately; text stays literal.
class Element {
  constructor(tag) { this.tag = tag; this.children = []; this.textContent = ''; }
  appendChild(child) { this.children.push(child); }
  replaceChildren(...children) { this.children = children; }
  set innerHTML(value) { throw new Error('Unsafe HTML sink: ' + value); }
}

test('TV polling renders DB values as text, handles nulls and replaces old rows', async () => {
  const table = new Element('table');
  const attack = '<img src=x onerror="alert(1)">';
  const values = [attack, '<script>alert(2)</script>', 'Ana & José <b>', '"quoted"'];
  let rows = [Object.fromEntries(['code', 'service', 'advisor', 'called_at'].map((key, i) => [key, values[i]]))];
  let refresh;
  const context = {
    window: { tvMode: true },
    document: {
      getElementById(id) { assert.equal(id, 'tv-table'); return table; },
      createElement(tag) { return new Element(tag); }
    },
    fetch: async url => { assert.equal(url, 'index.php?page=tv-data'); return { json: async () => rows }; },
    setInterval(callback, delay) { assert.equal(delay, 3000); refresh = callback; }
  };
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/assets/js/app.js'), 'utf8'), context);
  await new Promise(resolve => setImmediate(resolve));
  assert.deepEqual(table.children[0].children[0].children.map(c => c.textContent), ['Turno', 'Servicio', 'Asesor', 'Hora']);
  const cells = table.children[1].children[0].children;
  assert.deepEqual(cells.map(c => c.textContent), values);
  cells.forEach(cell => { assert.equal(cell.tag, 'td'); assert.equal(cell.children.length, 0); });
  rows = [{ code: 'B002', service: 'Consulta', advisor: null, called_at: null }];
  await refresh();
  assert.equal(table.children[1].children.length, 1);
  assert.deepEqual(table.children[1].children[0].children.map(c => c.textContent), ['B002', 'Consulta', '', '']);
  rows = [];
  await refresh();
  assert.equal(table.children[1].children.length, 0);
  assert.equal(table.children[0].children[0].children.length, 4);
});
