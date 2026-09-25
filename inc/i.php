<?php
/**
 * SMS-уведомление админу.
 * PHP 8.2-совместимая версия.
 */

$from = 'noreply@hmr.su';
if (function_exists('mail_utf8') && !empty($mess)) {
    mail_utf8('79231237203@sms.megafonsib.ru', 'Миртания', (string)$mess, $from);
}
