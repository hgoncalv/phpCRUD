<?php
require_once BASE_PATH . "/functions/parsingFunctions.php";

class Config
{
    private $DBHOST = '';
    private $DBUSER = '';
    private $DBPASS = '';
    private $DBNAME = '';

    private $dsn = '';
    protected $conn = null;

    // Constructor Function
    public function __construct()
    {
        $envFilePath = BASE_PATH . "/.env";
        $env = parseEnv($envFilePath);
        $this->DBHOST = $env['MYSQL_HOST'];
        $this->DBUSER = $env['MYSQL_USER'];
        $this->DBPASS = $env['MYSQL_PASSWORD'];
        $this->DBNAME = $env['MYSQL_DATABASE'];
        $this->dsn = 'mysql:host=' . $this->DBHOST . ';dbname=' . $this->DBNAME . '';

        try {
            $this->conn = new PDO($this->dsn, $this->DBUSER, $this->DBPASS);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Connectionn Failed : ' . $e->getMessage());
        }
        return $this->conn;
    }

}
