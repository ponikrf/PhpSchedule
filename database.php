<?php

class Database
{

    protected static $db = false;
    protected static $username;
    protected static $password;
    protected static $host;
    protected static $database;

    /**
     * Настройка подключения к базе данных и попытка подключения
     *
     * @param $username
     * @param $password
     * @param $host
     * @param $database
     */
    public static function connect($username, $password, $host, $database)
    {
        self::$username = $username;
        self::$password = $password;
        self::$host = $host;
        self::$database = $database;
        return self::reconnect();
    }

    /**
     *
     *
    */
    public static function reconnect()
    {
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ];

        self::$db = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$database . ";", self::$username, self::$password, $opt);
    }

    /**
     * Send query
     *
     * @param $query
     * @return mixed
     */
    public static function query($query){
        return self::$db->query($query);
    }

    /**
     * Пыполнение запроса в вовзратом единичного результата
     * ```
     * echo Database::scalar("select now()");
     * ```
     * print: '2017-01-01 10:49:40'
     *
     * @param string $query Query string
     * @return string
     */
    public static function scalar($query)
    {
        $row = self::$db->query("SELECT ($query) AS RAW")->fetch();
        return $row['RAW'];
    }

    /**
     * Магический метод для запуска функций mysql как статический метод класса
     * ```
     * echo Database::now();
     * ```
     * print: '2017-01-01 10:49:40'
     *
     * Аргументы будут преобразованны в строки если это необходимо. Крайне простое
     * и не безопасное преобразование.
     * ```
     * $dateNow = Database::now();  // return '2017-01-01 10:49:40'
     * echo Database::date($dateNow);
     * ```
     * print: '2017-01-01'
     *
     * @param string $name Function name
     * @param array $arguments Array arguments
     * @return string
     */
    public static function __callStatic($name, $arguments)
    {
        $function = $name;
        $arg = self::parseArg($arguments);
        return self::scalar("SELECT $function($arg)");
    }

    /**
     * Преобразует арументы в виде массива в Sql формат
     *
     * @param array $argArray argument array
     * @return string formatted arguments
     */
    public static function parseArg($argArray)
    {
        $returnArray = [];

        foreach ($argArray as $key => $value) {
            if (is_int($value)) {
                $returnArray[$key] = $value;
            } else {
                if ($value == NULL) {
                    $returnArray[$key] = 'NULL';
                } else {
                    $returnArray[$key] = "'" . $value . "'";
                }
            }
        }

        return Util::ArrayToString($returnArray, ',');
    }

}