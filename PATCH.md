# PATCH — что изменилось

Дата: сентябрь 2026.

Исходный проект: maslanin/mirtania (2014, PHP 5.3).

Цель: совместимость с PHP 8.2 и запуск на современном стеке (PHP 8.2 + MySQL 8.0).

---

## Кратко

- 60+ файлов адаптированы под PHP 8.2.
- Создан новый game.sql.new — с фиксами и уникальными названиями вещей.
- Найдено и исправлено 80+ багов.
- Игра запускается, бой работает, персонаж регистрируется.

---

## Технические изменения

### 1. Совместимость с PHP 8.2

Что было (PHP 5.3):

- @ перед new mysqli — не глушит исключения в 8.x.
- mysqli_connect_error() — глобальная функция, а не свойство.
- unset($_SESSION) — не убивает сессию.
- strlen(null) — deprecated.
- htmlspecialchars(null) — deprecated.
- ${var} в строках — deprecated.
- Динамические свойства класса — deprecated.
- each(), create_function(), mysql_* — удалены.
- date('d.m.Y', 0) без (int) — warning.
- mt_rand() и rand() — смешаны.

Что стало:

- mysqli_report(MYSQLI_REPORT_OFF) + проверка connect_error.
- session_unset() + session_destroy().
- (int) для всех числовых.
- htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8').
- Явные свойства класса (для smsru).
- Единообразно mt_rand().

### 2. SQL

Найдено:

- only_full_group_by — 4 запроса в inv.php, shop.php, bank.php.
  SELECT id, COUNT(*) ... GROUP BY ido  →  SELECT MIN(id) AS id, COUNT(*) ...
  ORDER BY time  →  ORDER BY MAX(time).
- INSERT INTO combat VALUES (...) — 20 значений, а в схеме 21 поле (добавили magic_hod).
  Добавили 0 в конец в inc/boi.php и inc/bot.php.
- Таблицы users и др. — TEXT NOT NULL без DEFAULT.
  Заменили на VARCHAR(...) NOT NULL DEFAULT ''.
- battle.php — users.kvest_now = ''  →  0 (INT).

### 3. Найденные баги (ключевые)

- arena.php — delete ftom arena (опечатка, заявка не удалялась).
- arena.php — toBoi брал всех с arena_id, включая тех, кто не в заявке.
- battle.php — if(empty($bz['login'])) вместо $boi['login'].
- battle.php — if(mt_rand...) без скобок в case $s_kakoy_udar == 3.
- infa.php — приоритет and/or в проверке требований вещи.
- inv.php — SQL-синтаксис flag_rinok=0 flag_sklad=0 and and flag_equip=0.
- klan.php — where klan= вместо where name=.
- zamok.php — $klan['altar'] вместо $klan['oruzh'] в логе.
- inc/doping.php — uvurot вместо uvorot.
- inc/magic.php — обновлял $me вместо $hz в case 3, 4.
- inc/drop.php — Тролль и Дракон давали предмет $me, а не $winner.
- inc/sumka.php — case 191 дважды, кодировка битая, Молния судьбы не работала для игроков.
- inc/donate.php — диапазоны лотереи пересекались (85).
- inc/exp.php — после 20 lvl нельзя прокачаться.
- inc/check.php — передача главы клана каждому зашедшему.
- loc.php — первый шаг игнорировался из-за $_SESSION['hint'].
- rabota.php — не было fin() после неверной капчи.
- forum.php — $b['name'] при $b = false.
- inc/top.php — $settings использовалось до загрузки.
- inc/func.php — fin() менял $_SERVER['REQUEST_TIME'] (суперглобал).

Всего — 80+ багов.

### 4. game.sql.new — новая БД

Что изменилось:

- Все таблицы — utf8mb4 (вместо utf8).
- Все int(12) → int, int(1) → tinyint.
- TEXT NOT NULL → VARCHAR(...) NOT NULL DEFAULT '' или TEXT NOT NULL DEFAULT ('').
- Добавлены индексы на частые поля (login, lastdate, boi_id, ido, flag_rinok).
- Все INSERT с именами полей (не VALUES(...) без имён).
- 706 вещей с уникальными названиями.

### 5. Прогрессия названий вещей

Раньше: «Правка крита 6 лвл», «Амулет 15 (с)» — шаблонно.

Теперь: тематические сеты по уровням.

| Уровень | Тема | Пример |
|---------|------|--------|
| 1-5     | Бродяга → Ветеран | Кинжал Бродяги, Меч Путника |
| 6       | Шахтёр | Клинок Кирки, Щит Забоя |
| 7       | Охотник | Клинок Силка, Щит Чащи |
| 8       | Болотник | Клинок Пиявки, Щит Тумана |
| 9       | Кочевник | Клинок Сабли, Щит Пыли |
| 10      | Стражник | Клинок Рогатины, Щит Заставы |
| 11      | Наёмник | Клинок Клеймора, Щит Золота |
| 12      | Рыцарь | Клинок Молота, Щит Знамени |
| 13      | Маг Огня | Клинок Костров, Щит Углей |
| 14      | Маг Воды | Клинок Потока, Щит Шторма |
| 15      | Паладин | Клинок Креста, Щит Молитвы |
| 16      | Инквизитор | Клинок Карателя, Щит Ереси |
| 17      | Архимаг | Клинок Молнии, Щит Астрала |
| 18      | Магистр | Клинок Печати, Щит Звёзд |
| 19      | Каменный дракон | Клинок Клыка, Щит Гранита |
| 20      | Болотный дракон | Клинок Грязи, Щит Гнили |
| 21      | Ледяной дракон | Клинок Мороза, Щит Вьюги |
| 22      | Огненный дракон | Клинок Пламени, Щит Крыла |
| 23      | Дракон-нежить | Клинок Костей, Щит Мрака |
| 24      | Спящий | Клинок Забвения, Щит Тени Спящего |
| 25      | Мироздание | Клинок Мироздания, Щит Богов |

Все имена уникальны. Проверка:

SELECT name, COUNT(*) FROM item GROUP BY name HAVING COUNT(*) > 1;

Результат — пусто.

### 6. UI-правки

- inc/head.php — текстовые ссылки вместо иконок, часы по центру (flex), скрытие ссылок по контексту.
- loc.php — фразы про портал («Выберите, куда открыть портал» → «Вы переместились в X. Это стоило вам 1 маны»).
- battle.php — П/У в процентах (средний шанс без кубика), ссылки на профиль в «Кто в бою».
- inv.php, shop.php, bank.php — only_full_group_by фикс.

### 7. Отложено

- П/У в бою не показываются при первом заходе — считаются до разбивки пар. Появляются после обновления.
- DEBUG — не сделано. Планируется: define('DEBUG', true) в class/DBC.php + if (DEBUG) в inc/top.php.
- Индексы для старого game.sql — уже есть в новом.
- PATCH.md — этот файл.
- Перенос на Python — проект B, отдельно.

---

## Как запустить

### Требования

- PHP 8.2+
- MySQL 8.0+

### Установка

Клонировать репозиторий:

    git clone <URL>
    cd mirtania

Создать БД:

    mysql -u root

Затем в консоли MySQL:

    CREATE DATABASE game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    USE game;
    SOURCE game.sql.new;

### Настройка

В class/DBC.php:

    define('DB_SERVER', '127.0.0.1');
    define('DB_USER',   'root');
    define('DB_PASS',   '');
    define('DB_BASE',   'game');
    define('DB_CHARSET','utf8mb4');

### Запуск

    php -S localhost:8000

Открыть http://localhost:8000/index.php.

### Первый админ

Создать персонажа, потом в MySQL:

    UPDATE users SET admin = 3 WHERE login = 'Твой_Логин';

Значения admin: 1 = модератор, 2 = супермодератор, 3 = админ, 4 = суперадмин.

---

## Что дальше

- DEBUG — флаг для отладки.
- Тестирование остальных модулей (chat, pm, forum, klan, kvest).
- Перенос на Python (проект B).

---

## Благодарности

- maslanin — за исходный проект, за адаптацию под PHP 8.2.
