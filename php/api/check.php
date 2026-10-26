<?php
// Вызывать по расписанию: /api/check.php?key=КЛЮЧ (cron-job.org, каждые 5 минут)
require __DIR__ . '/lib.php';
if (!hash_equals(CRON_KEY, $_GET['key'] ?? '')) { http_response_code(403); exit('forbidden'); }
set_time_limit(120);
$pdo = db(); $now = time() * 1000;

$mh = curl_multi_init(); $hs = [];
foreach (SERVICES as [$id, $name, $url]) {
  $c = curl_init($url);
  curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_TIMEOUT => 12,
    CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; VKStatusMonitor/1.0; +https://example.com) AppleWebKit/537.36 Chrome/124.0 Safari/537.36', CURLOPT_HTTPHEADER => ['Accept: text/html,application/json;q=0.9,*/*;q=0.8','Accept-Language: ru,en;q=0.8']]);
  curl_multi_add_handle($mh, $c); $hs[$id] = $c;
}
do { curl_multi_exec($mh, $run); curl_multi_select($mh, 1); } while ($run > 0);

foreach (SERVICES as [$id, $name, $url]) {
  $c = $hs[$id];
  $code = (int)curl_getinfo($c, CURLINFO_HTTP_CODE);
  $ms = (int)round(curl_getinfo($c, CURLINFO_TOTAL_TIME) * 1000);
  $up = ($code > 0 && $code < 500) ? 1 : 0;
  $q = $pdo->prepare('SELECT up FROM checks WHERE sid=? ORDER BY t DESC LIMIT 1');
  $q->execute([$id]); $prev = $q->fetchColumn();
  $pdo->prepare('INSERT INTO checks(sid,t,up,ms,code) VALUES(?,?,?,?,?)')->execute([$id, $now, $up, $ms, $code]);
  if (($prev === false || (int)$prev === 1) && !$up)
    $pdo->prepare('INSERT INTO incidents(sid,name,f,t) VALUES(?,?,?,NULL)')->execute([$id, $name, $now]);
  if ($prev !== false && (int)$prev === 0 && $up)
    $pdo->prepare('UPDATE incidents SET t=? WHERE sid=? AND t IS NULL')->execute([$now, $id]);
  curl_multi_remove_handle($mh, $c); curl_close($c);
}
curl_multi_close($mh);
$pdo->prepare('DELETE FROM checks WHERE t<?')->execute([$now - 7 * 864e5]);
echo 'ok';
