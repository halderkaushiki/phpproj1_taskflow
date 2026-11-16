<?php
require __DIR__ . '/includes/db.php';
header('Content-Type: application/json');
if (empty($_SESSION['uid'])) { http_response_code(401); exit('{"error":"auth"}'); }
$uid = $_SESSION['uid'];
$in = json_decode(file_get_contents('php://input'), true) ?: [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF'] ?? '')) { http_response_code(403); exit('{"error":"csrf"}'); }
$id = (int)($in['id'] ?? 0);
function clean($i) {
    return [mb_substr(trim($i['title'] ?? ''), 0, 200), mb_substr(trim($i['notes'] ?? ''), 0, 1000),
        in_array($i['priority'] ?? '', ['low', 'medium', 'high']) ? $i['priority'] : 'medium',
        mb_substr(trim($i['category'] ?? ''), 0, 30), preg_match('/^\d{4}-\d\d-\d\d$/', $i['due'] ?? '') ? $i['due'] : null];
}
function subs($i) {
    $a = json_decode($i['subs'] ?? '[]', true); if (!is_array($a)) return '[]'; $o = [];
    foreach (array_slice($a, 0, 30) as $x) if (!empty($x['t'])) $o[] = ['t' => mb_substr((string)$x['t'], 0, 100), 'd' => empty($x['d']) ? 0 : 1];
    return json_encode($o);
}
switch ($_GET['a'] ?? 'list') {
    case 'add':
        [$t, $n, $p, $c, $d] = clean($in);
        if ($t === '') { http_response_code(422); exit('{"error":"title"}'); }
        $db->prepare('INSERT INTO tasks(user_id,title,notes,priority,category,due,subs) VALUES(?,?,?,?,?,?,?)')->execute([$uid, $t, $n, $p, $c, $d, subs($in)]); break;
    case 'update':
        [$t, $n, $p, $c, $d] = clean($in);
        if ($t !== '') $db->prepare('UPDATE tasks SET title=?,notes=?,priority=?,category=?,due=?,subs=? WHERE id=? AND user_id=?')->execute([$t, $n, $p, $c, $d, subs($in), $id, $uid]); break;
    case 'pin': $db->prepare('UPDATE tasks SET pinned=1-pinned WHERE id=? AND user_id=?')->execute([$id, $uid]); break;
    case 'subs': $db->prepare('UPDATE tasks SET subs=? WHERE id=? AND user_id=?')->execute([subs($in), $id, $uid]); break;
    case 'toggle': $db->prepare('UPDATE tasks SET done=1-done WHERE id=? AND user_id=?')->execute([$id, $uid]); break;
    case 'delete': $db->prepare('DELETE FROM tasks WHERE id=? AND user_id=?')->execute([$id, $uid]); break;
    case 'clear': $db->prepare('DELETE FROM tasks WHERE done=1 AND user_id=?')->execute([$uid]); break;
}
$s = $db->prepare('SELECT * FROM tasks WHERE user_id=? ORDER BY id DESC'); $s->execute([$uid]);
echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));
