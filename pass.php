<?php
/**
 * Восстановление пароля.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/head.php';

$timer = $t - 86400;
$db->query("DELETE FROM `recpass` WHERE `daterec` < '{$timer}';");

$go        = isset($_REQUEST['go'])        ? (int)$_REQUEST['go'] : 0;
$ok        = isset($_REQUEST['ok'])        ? $_REQUEST['ok'] : 0;
$email     = isset($_REQUEST['email'])     ? $_REQUEST['email'] : '';
$keystring = isset($_REQUEST['keystring']) ? $_REQUEST['keystring'] : '';
$email1    = isset($_REQUEST['email1'])    ? $_REQUEST['email1'] : '';
$code1     = isset($_REQUEST['code1'])     ? $_REQUEST['code1'] : '';

switch ($go) {
    case 1:
        if (empty($ok)) {
            echo '<div class="board" style="text-align:left;">';
            echo '<form method="POST" action="pass.php?go=1&ok=1">
            <b>Введите ваш адрес e-mail:</b><br/><input type="text" name="email" size="20" maxlength="40"><br/>
            Введите код с картинки:<br/><img src="bez.php?r=' . mt_rand(11111, 99999) . '"><br/><input type="text" name="keystring"><br/>
            <input type="submit" value="Восстановить пароль"></form>';
            echo '</div>';
            knopka('pass.php?go=2', 'Подтвердить код', 1);
            knopka('index.php', 'На главную', 1);
            fin();
        }
        if (!isset($_SESSION['bez']) || $_SESSION['bez'] != $keystring) {
            msg2('Вы ввели неверный код с картинки!');
            knopka('javascript:history.go(-1)', 'Назад', 1);
            fin();
        }
        if (!preg_match('/^[a-z0-9]+([-_\.]?[a-z0-9])+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}/i', $email) || 40 < mb_strlen($email, 'UTF-8')) {
            msg2('Неверно набран e-mail. Пример: <span style="color:' . $female . '"><b>admin@hmr.su</b></span>');
            knopka('javascript:history.go(-1)', 'Назад', 1);
            fin();
        }
        $email_esc = $db->real_escape_string($email);
        $q = $db->query("SELECT * FROM `users` WHERE `email` = '{$email_esc}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg('Такой адрес email не зарегистрирован!', 1);
        $db->query("DELETE FROM `recpass` WHERE `email` = '{$email_esc}' LIMIT 1;");

        $cod = base64_encode((string)mt_rand(0, 9999)) . mt_rand(0, 9999);
        $cod = preg_replace('/[^A-z0-9]/', '', $cod);
        $subj = 'Восстановление пароля в игре ' . $settings['name'] . '!';
        $mess = 'Для восстановления пароля зайдите на главную страницу игры, выберите ссылку "Восстановление пароля", далее выберите "Подтвердить код"<br/>
        Ваш код: ' . $cod;
        $from = 'no_reply@' . $_SERVER['SERVER_NAME'];
        $db->query("INSERT INTO `recpass` VALUES (0, '{$email_esc}', '" . $db->real_escape_string($cod) . "', '{$t}');");
        if (function_exists('mail_utf8')) mail_utf8($email, $subj, $mess, $from);
        msg2('На ваш e-mail отправлен код подтверждения.');
        knopka('pass.php?go=2', 'Подтвердить код', 1);
        knopka('index.php', 'На главную', 1);
        fin();
        break;

    case 2:
        if (empty($ok)) {
            echo '<div class="board" style="text-align:left;">';
            echo '<form method="POST" action="pass.php?go=2&ok=1">
            <b>Введите ваш адрес e-mail:</b><br/><input type="text" name="email1" size="20" maxlength="40"><br/>
            Введите код, высланный вам на e-mail:<br/><input type="text" name="code1"><br/>
            <input type="submit" value="Сгенерировать пароль"></form></div>';
            knopka('index.php', 'На главную', 1);
            fin();
        }
        if (!preg_match('/^[a-z0-9]+([-_\.]?[a-z0-9])+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}/i', $email1) || 40 < mb_strlen($email1, 'UTF-8')) {
            msg2('Неверно набран e-mail. Пример: <span style="color:' . $female . '"><b>admin@hmr.su</b></span>');
            knopka('javascript:history.go(-1)', 'Назад', 1);
            fin();
        }
        $email_esc = $db->real_escape_string($email1);
        $q = $db->query("SELECT * FROM `users` WHERE `email` = '{$email_esc}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg2('Такой адрес email не зарегистрирован!', 1);
        $a = $q->fetch_assoc();
        $q = $db->query("SELECT * FROM `recpass` WHERE `email` = '{$email_esc}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg2('Для этого e-mail не был заказан код восстановления!', 1);
        $recpass = $q->fetch_assoc();
        if ($recpass['code'] != $code1) msg('Код подтверждения не верен!', 1);

        $code = base64_encode((string)mt_rand(0, 9999)) . mt_rand(0, 9999);
        $code = preg_replace('/[^A-z0-9]/', '', $code);
        $md5pass = md5($code);
        $db->query("UPDATE `users` SET `pass` = '{$md5pass}' WHERE `email` = '{$email_esc}' LIMIT 1;");
        $db->query("DELETE FROM `recpass` WHERE `email` = '{$email_esc}' LIMIT 1;");
        $subj = 'Восстановление пароля в игре «' . $settings['name'] . '»';
        $mess = 'Для вашего персонажа ' . $a['login'] . ' в игре ' . $settings['name'] . ' сгенерирован новый пароль : ' . $code;
        $from = 'noreply@' . $_SERVER['SERVER_NAME'];
        if (function_exists('mail_utf8')) mail_utf8($email1, $subj, $mess, $from);
        msg2('На ваш e-mail отправлено письмо с новым паролем!');
        knopka('index.php', 'На главную', 1);
        fin();
        break;

    default:
        msg2('Если вы хотите заказать код для восстановления пароля, вам <a href="pass.php?go=1">сюда</a>. Если вы хотите ввести код подтверждения, нажмите <a href="pass.php?go=2">здесь</a>');
        break;
}

fin();
