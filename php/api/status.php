<?php
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');
$pdo = db(); $now = time() * 1000; $out = [];
foreach (SERVICES as [$id, $name, $url]) {
  $q = $pdo->prepare('SELECT t,up,ms,code FROM checks WHERE sid=? AND t>? ORDER BY t');
  $q->execute([$id, $now - 864e5]);
  $day = $q->fetchAll(PDO::FETCH_ASSOC);
  $last = $day ? end($day) : null;
  $ups = array_values(array_filter($day, fn($x) => $x['up'] == 1));
  $out[] = [
    'id' => $id, 'name' => $name, 'url' => $url,
    'up' => $last ? (bool)$last['up'] : null,
    'ms' => $last ? (int)$last['ms'] : null,
    'code' => $last ? (int)$last['code'] : null,
    'uptime' => $day ? round(count($ups) / count($day) * 100, 2) : null,
    'avg' => $ups ? (int)round(array_sum(array_column($ups, 'ms')) / count($ups)) : null,
    'history' => array_map(fn($x) => ['t' => (int)$x['t'], 'up' => (bool)$x['up'], 'ms' => (int)$x['ms']], array_slice($day, -48)),
  ];
}
$inc = $pdo->query('SELECT sid AS id, name, f AS "from", t AS "to" FROM incidents ORDER BY f DESC LIMIT 30')->fetchAll(PDO::FETCH_ASSOC);
foreach ($inc as &$i) { $i['from'] = (int)$i['from']; $i['to'] = $i['to'] === null ? null : (int)$i['to']; }
echo json_encode(['updated' => $now, 'services' => $out, 'incidents' => $inc], JSON_UNESCAPED_UNICODE);
