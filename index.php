<?php
session_start();
require __DIR__ . '/app/helpers/db.php';
require __DIR__ . '/app/helpers/auth.php';
require __DIR__ . '/app/helpers/view.php';

$page = $_GET['page'] ?? 'login';

if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();
        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: index.php?page=dashboard'); exit;
        }
        render('login', ['error' => 'Credenciales inválidas']); exit;
    }
    render('login'); exit;
}
if ($page === 'logout') { session_destroy(); header('Location: index.php?page=login'); exit; }
if ($page === 'dashboard') { require_login(); render('dashboard'); exit; }

if ($page === 'services') {
    require_role(['administrador']);
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        db()->prepare('INSERT INTO services(name,prefix,active) VALUES (?,?,1)')->execute([$_POST['name'], $_POST['prefix']]);
    }
    $services = db()->query('SELECT * FROM services ORDER BY id DESC')->fetchAll();
    render('services', compact('services')); exit;
}
if ($page === 'kiosk') {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        $sid=(int)$_POST['service_id'];
        $svc=db()->query("SELECT * FROM services WHERE id=$sid")->fetch();
        $seq=(int)db()->query("SELECT IFNULL(MAX(sequence),0)+1 n FROM tickets WHERE service_id=$sid AND DATE(created_at)=CURDATE()")->fetch()['n'];
        $code=$svc['prefix'].str_pad((string)$seq,3,'0',STR_PAD_LEFT);
        db()->prepare('INSERT INTO tickets(service_id,sequence,code,status) VALUES (?,?,?,"espera")')->execute([$sid,$seq,$code]);
        $generated=$code;
    }
    $services = db()->query('SELECT * FROM services WHERE active=1')->fetchAll();
    render('kiosk', compact('services','generated')); exit;
}
if ($page === 'advisor') {
    require_role(['administrador','asesor']);
    $uid=current_user()['id'];
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        $action=$_POST['action']; $ticketId=(int)($_POST['ticket_id']??0);
        if ($action==='call_next') {
            $ticket=db()->query('SELECT t.* FROM tickets t WHERE status="espera" ORDER BY id ASC LIMIT 1')->fetch();
            if ($ticket) db()->prepare('UPDATE tickets SET status="llamado", advisor_id=?, called_at=NOW() WHERE id=?')->execute([$uid,$ticket['id']]);
        } elseif ($action==='repeat') db()->prepare('UPDATE tickets SET called_at=NOW() WHERE id=?')->execute([$ticketId]);
        elseif ($action==='finish') db()->prepare('UPDATE tickets SET status="finalizado", finished_at=NOW() WHERE id=?')->execute([$ticketId]);
        elseif ($action==='absent') db()->prepare('UPDATE tickets SET status="ausente", finished_at=NOW() WHERE id=?')->execute([$ticketId]);
        elseif ($action==='transfer') db()->prepare('UPDATE tickets SET service_id=?, status="espera", advisor_id=NULL WHERE id=?')->execute([(int)$_POST['new_service_id'],$ticketId]);
    }
    $current = db()->query("SELECT t.*, s.name service FROM tickets t JOIN services s ON s.id=t.service_id WHERE t.status='llamado' AND t.advisor_id=$uid ORDER BY t.called_at DESC LIMIT 1")->fetch();
    $waiting = db()->query("SELECT t.*, s.name service FROM tickets t JOIN services s ON s.id=t.service_id WHERE t.status='espera' ORDER BY t.id LIMIT 10")->fetchAll();
    $services=db()->query('SELECT * FROM services WHERE active=1')->fetchAll();
    render('advisor', compact('current','waiting','services')); exit;
}
if ($page === 'tv') { render('tv'); exit; }
if ($page === 'tv-data') {
    header('Content-Type: application/json');
    echo json_encode(db()->query("SELECT t.code,s.name service,u.name advisor,t.called_at FROM tickets t JOIN services s ON s.id=t.service_id LEFT JOIN users u ON u.id=t.advisor_id WHERE t.status='llamado' ORDER BY t.called_at DESC LIMIT 12")->fetchAll()); exit;
}
if ($page === 'reports') {
    require_role(['administrador']);
    $where=['1=1']; $params=[];
    foreach (['service_id','advisor_id'] as $f) if(!empty($_GET[$f])){$where[]="t.$f=?";$params[]=$_GET[$f];}
    if(!empty($_GET['status'])){$where[]='t.status=?';$params[]=$_GET['status'];}
    if(!empty($_GET['from'])){$where[]='DATE(t.created_at)>=?';$params[]=$_GET['from'];}
    if(!empty($_GET['to'])){$where[]='DATE(t.created_at)<=?';$params[]=$_GET['to'];}
    $sql='SELECT t.*,s.name service,u.name advisor, TIMESTAMPDIFF(SECOND,t.created_at,t.called_at) wait_sec, TIMESTAMPDIFF(SECOND,t.called_at,t.finished_at) att_sec FROM tickets t JOIN services s ON s.id=t.service_id LEFT JOIN users u ON u.id=t.advisor_id WHERE '.implode(' AND ',$where).' ORDER BY t.id DESC';
    $st=db()->prepare($sql);$st->execute($params);$rows=$st->fetchAll();
    $services=db()->query('SELECT * FROM services')->fetchAll();$advisors=db()->query("SELECT * FROM users WHERE role='asesor'")->fetchAll();
    render('reports', compact('rows','services','advisors')); exit;
}
