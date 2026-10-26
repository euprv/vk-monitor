<?php
// Секретный ключ для запуска проверок внешним cron-сервисом. ОБЯЗАТЕЛЬНО СМЕНИТЕ.
const CRON_KEY = 'change-me-please';
const SERVICES = [
  ['vk','VK (веб)','https://vk.com'],
  ['vk-m','VK мобильная версия','https://m.vk.com'],
  ['vk-api','VK API','https://api.vk.com/method/utils.getServerTime'],
  ['vk-id','VK ID','https://id.vk.com'],
  ['vk-video','VK Видео','https://vkvideo.ru'],
  ['vk-music','VK Музыка','https://music.vk.com'],
  ['ok','Одноклассники','https://ok.ru'],
  ['mail','Mail.ru','https://mail.ru'],
  ['e-mail','Почта Mail.ru','https://e.mail.ru'],
  ['cloud','Облако Mail.ru','https://cloud.mail.ru'],
  ['dzen','Дзен','https://dzen.ru'],
  ['vkplay','VK Play','https://vkplay.ru'],
  ['rustore','RuStore','https://rustore.ru'],
  ['vk-company','VK Company','https://vk.company'],
];
function db(): PDO {
  $p = new PDO('sqlite:' . __DIR__ . '/../data/m.sqlite');
  $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $p->exec('CREATE TABLE IF NOT EXISTS checks(id INTEGER PRIMARY KEY, sid TEXT, t INTEGER, up INTEGER, ms INTEGER, code INTEGER)');
  $p->exec('CREATE INDEX IF NOT EXISTS i1 ON checks(sid,t)');
  $p->exec('CREATE TABLE IF NOT EXISTS incidents(id INTEGER PRIMARY KEY, sid TEXT, name TEXT, f INTEGER, t INTEGER)');
  return $p;
}
