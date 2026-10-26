# Мониторинг доступности сервисов VK

## Вариант 1 — Netlify (весь проект)
1. Залейте проект на GitHub → Netlify → Add new site → Import from Git (настройки берутся из netlify.toml).
2. Scheduled Function `check` запускается каждые 5 минут, данные хранятся в Netlify Blobs (история 7 суток).
3. Первые данные появятся через ≤5 минут.

## Вариант 2 — InfinityFree (папка php/)
1. Загрузите содержимое `php/` в `htdocs/` (index.html, api/, data/).
2. В `api/lib.php` смените `CRON_KEY`.
3. Cron на InfinityFree нет — создайте задание на cron-job.org: `https://ВАШ-САЙТ/api/check.php?key=КЛЮЧ`, каждые 5 минут.
4. Данные хранятся в SQLite (`data/m.sqlite`, папка закрыта .htaccess).
