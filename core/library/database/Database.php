<?php
/**
 * Created By Fumihiro
 * Created At 2014/06/20 23:25
 */

namespace core\library\database;

require_once __DIR__ . '/../../vendor/spyc.php';

class Database
{
    const CONNECTION_MASTER = 'master';

    const CONNECTION_NAME_DEFAULT = 'default';

    /**
     * @var array $setting
     */
    private $setting;

    /**
     * @var string $connectionName
     */
    private $connectionName;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $statusOpen = false;

    /**
     * @var \Mysqli
     */
    private $mysql;

    /**
     * @param string $connectionName
     */
    public function __construct($connectionName = self::CONNECTION_NAME_DEFAULT)
    {

        $this->connectionName = $connectionName;

        $this->readConfig();

        $this->initialize();

    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function open()
    {

        if (!$this->isOpen()) {
            $this->mysql = mysqli_connect($this->host, $this->user, $this->password, $this->database, $this->port);
            $this->statusOpen = true;

        }

        return $this->statusOpen;

    }

    /**
     * @return bool
     */
    public function close()
    {

        $result = true;

        if ($this->mysql) {
            $result           = $this->mysql->close();
            $this->statusOpen = false;

            $this->mysql = null;

        }

        return $result;

    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return $this->statusOpen;
    }

    /**
     * @param $sql
     */
    public function query($sql)
    {
        if (!$this->isOpen()) {
            throw new \Exception('[Database] Connection Closed.');
        }

        $result = mysqli_query($this->mysql, $sql);

        if ($result === true) {
            // Success DDL, DQL(INSERT, UPDATE, DELETE, etc...)
            return true;

        } elseif ($result === false) {
            // Query Failed
            throw new \Exception('[Database] Query failure.' . PHP_EOL . $this->mysql->error);

        } else {
            // Success SELECT
            $list = array();
            while ($obj = $result->fetch_object()) {
                $list[] = $obj;
            }

            return $list;
        }

    }

    /**
     * 設定情報（yml ファイル）を取り込む
     */
    private function readConfig()
    {

        $this->setting = spyc_load_file(__DIR__ . '/../../config/database.yml');

    }

    /**
     * 初期化
     */
    private function initialize()
    {

        $this->checkConnection();

        $this->checkConnectionConfig();

        $this->setConnectionParameter();

    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkConnection()
    {

        if (!isset($this->setting[self::CONNECTION_MASTER])) {
            throw new \Exception('[Connection] Not found master setting.');
        }

        if (!isset($this->setting[self::CONNECTION_MASTER][$this->connectionName])) {
            throw new \Exception('[Connection] Not found "' . $this->connectionName . '" setting.');
        }

        return true;

    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkConnectionConfig()
    {

        $params = array(
            'host',
            'database',
            'user',
        );

        $connection = $this->setting[self::CONNECTION_MASTER][$this->connectionName];

        foreach ($params as $param) {
            if (!isset($connection[$param])) {
                throw new \Exception('[Connection Config] Not found "' . $param . '" parameter.');
            }

            if ($connection[$param] === null || $connection[$param] === '') {
                throw new \Exception('[Connection Config] Null "' . $param . '" parameter.');
            }
        }

        if (!is_string($connection['host'])) {
            throw new \Exception('[Connection Config] Not string "host".');
        }

        if (isset($connection['port']) && !is_string($connection['port'])) {
            throw new \Exception('[Connection Config] Not numeric "port".');
        }

        if (!is_string($connection['database'])) {
            throw new \Exception('[Connection Config] Not string "database".');
        }

        if (!is_string($connection['user'])) {
            throw new \Exception('[Connection Config] Not string "user".');
        }

        if (isset($connection['password']) && !is_string($connection['password'])) {
            throw new \Exception('[Connection Config] Not numeric "password".');
        }

        return true;

    }

    /**
     *
     */
    private function setConnectionParameter()
    {

        $connection = $this->setting[self::CONNECTION_MASTER][$this->connectionName];

        foreach ($connection as $key => $value) {
            $connection[$key] = $value === '' ? null : $value;
        }

        $this->host     = $connection['host'];
        $this->port = isset($connection['port']) ? (int) $connection['port'] : 3306;
        $this->database = $connection['database'];
        $this->user     = $connection['user'];
        $this->password = isset($connection['password']) ? $connection['password'] : '';

    }

}