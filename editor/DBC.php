<?php
/**
 * Класс работы с MySQL (редактор карт).
 * PHP 8.2-совместимая версия.
 *
 * ВНИМАНИЕ: это отдельная копия для редактора. В игре используется class/DBC.php.
 */

define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_BASE', 'game');
define('DB_CHARSET', 'utf8mb4');

final class DBC
{
    private ?mysqli $_handle = null;
    private static ?DBC $_instance = null;

    private function __construct()
    {
        $this->connect();
    }

    public static function instance(): DBC
    {
        if (self::$_instance === null) {
            self::$_instance = new DBC();
        }
        return self::$_instance;
    }

    private function connect(): void
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->_handle = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_BASE);
        if ($this->_handle->connect_error) {
            error_log('MySQL connect error: ' . $this->_handle->connect_error);
            exit('Ошибка соединения с базой данных, повторите через несколько секунд или обратитесь к администратору!');
        }
        $this->_handle->set_charset(DB_CHARSET);
    }

    public function query(string $q)
    {
        $result = $this->_handle->query($q);
        if ($result === false) {
            error_log('SQL error: ' . $this->_handle->error . ' | Query: ' . $q);
        }
        return $result;
    }

    public function insert_id(): int
    {
        return $this->_handle->insert_id;
    }

    public function real_escape_string(string $s): string
    {
        return $this->_handle->real_escape_string($s);
    }

    public function error(): string
    {
        return $this->_handle->error;
    }
}

$db = DBC::instance();
