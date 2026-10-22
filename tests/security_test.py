"""Run with python3 -m unittest discover -s tests -p '*_test.py' -v.

SQL regression tests use SQLite in memory, never the configured application DB.
Route tests execute the real router/auth helper with stubbed DB/view functions;
they require PHP CLI and are explicitly skipped when it is unavailable.
"""
from pathlib import Path
import re
import shutil
import sqlite3
import subprocess
import unittest

ROOT = Path(__file__).resolve().parents[1]
SOURCE = (ROOT / 'index.php').read_text()


class AdvisorSecurity(unittest.TestCase):
    def test_actions_only_change_owned_called_ticket(self):
        for action in ('repeat', 'finish', 'absent', 'transfer'):
            # Execute the actual SQL and verify its actual parameter binding order.
            pattern = (r"\$action==='" + action +
                       r"'\) db\(\)->prepare\('([^']+)'\)->execute\(\[(.*?)\]\)")
            match = re.search(pattern, SOURCE)
            self.assertIsNotNone(match, action)
            sql, bindings = match.groups()
            expected = ('(int)$_POST[\'new_service_id\'],' if action == 'transfer' else '')
            self.assertEqual(bindings, expected + '$ticketId,$uid')
            cases = [(10, 'llamado'), (20, 'llamado'), (None, 'espera'),
                     (10, 'espera'), (10, 'finalizado'), (10, 'ausente')]
            for owner, status in cases:
                with self.subTest(action=action, owner=owner, status=status):
                    with sqlite3.connect(':memory:') as db:
                        db.create_function('NOW', 0, lambda: '2026-05-19 13:00:00')
                        db.execute('CREATE TABLE tickets (id INT, service_id INT, advisor_id INT, status TEXT, called_at TEXT, finished_at TEXT)')
                        db.execute('INSERT INTO tickets VALUES (1, 1, ?, ?, "old", NULL)', (owner, status))
                        before = db.execute('SELECT * FROM tickets').fetchone()
                        params = ([2] if action == 'transfer' else []) + [1, 10]
                        db.execute(sql, params)
                        after = db.execute('SELECT * FROM tickets').fetchone()
                        if owner != 10 or status != 'llamado':
                            self.assertEqual(before, after)
                        else:
                            self.assertNotEqual(before, after)
                            if action == 'transfer':
                                self.assertEqual(after[1:4], (2, None, 'espera'))
                            elif action in ('finish', 'absent'):
                                self.assertEqual(after[3], {'finish': 'finalizado', 'absent': 'ausente'}[action])
                                self.assertEqual(after[5], '2026-05-19 13:00:00')
                            else:
                                self.assertEqual(after[4], '2026-05-19 13:00:00')
                        # Missing ticket IDs must also be harmless.
                        params[-2] = 999
                        db.execute(sql, params)
                        self.assertEqual(after, db.execute('SELECT * FROM tickets').fetchone())


class TvSecurity(unittest.TestCase):
    def test_gates_precede_render_and_query(self):
        for route in ('tv', 'tv-data'):
            match = re.search(r"if \(\$page === '" + route + r"'\) \{\s*require_role\(\[(.*?)\]\);", SOURCE)
            self.assertIsNotNone(match, route)
            self.assertEqual(re.findall(r"'([^']+)'", match[1]), ['administrador', 'pantalla'])

    @unittest.skipUnless(shutil.which('php'), 'PHP CLI not installed')
    def test_route_access_matrix(self):
        router = SOURCE.removeprefix('<?php').replace('session_start();', '')
        router = re.sub(r"require __DIR__ .*?;", '', router)
        auth = (ROOT / 'app/helpers/auth.php').read_text().removeprefix('<?php')
        for route in ('tv', 'tv-data'):
            for role in (None, 'asesor', 'pantalla', 'administrador', 'invalid'):
                with self.subTest(route=route, role=role):
                    setup = "$_SESSION = [];"
                    if role:
                        setup += "$_SESSION['user'] = ['id'=>10, 'role'=>'" + role + "'];"
                    script = '''<?php
                    register_shutdown_function(function() {
                        echo "\\nSTATUS=" . (http_response_code() ?: 200);
                    });
                    function render($view, $data=[]) { echo 'VIEW:' . $view; }
                    function db() { return new class {
                        function query($sql) { return new class {
                            function fetchAll() { return [['code'=>'A001']]; }
                        }; }
                    }; }
                    ''' + setup + "$_GET['page']='" + route + "';" + auth + router
                    result = subprocess.run(['php'], input=script, text=True, capture_output=True)
                    self.assertEqual(result.returncode, 0, result.stderr)
                    self.assertEqual(result.stderr, '')
                    allowed = role in ('pantalla', 'administrador')
                    self.assertIn('STATUS=' + str(200 if allowed else 302 if role is None else 403), result.stdout)
                    if allowed:
                        self.assertIn('VIEW:tv' if route == 'tv' else 'A001', result.stdout)
                    else:
                        self.assertNotIn('VIEW:', result.stdout)
                        self.assertNotIn('A001', result.stdout)


if __name__ == '__main__':
    unittest.main()
