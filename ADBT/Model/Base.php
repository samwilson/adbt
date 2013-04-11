<?php

class ADBT_Model_Base
{

    /** @var PDO */
    static protected $pdo;

    /** @var ADBT_App The application. */
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function dbInit() {
        global $database_config;
        $dsn = 'mysql:host=' . $database_config['hostname'] . ';dbname=' . $database_config['database'];
        $attr = array(PDO::ATTR_TIMEOUT => 10);
        self::$pdo = new PDO($dsn, $database_config['username'], $database_config['password'], $attr);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return true;
    }

    /**
     * Fetch all.
     * 
     * @deprecated Use $this->query() instead.
     * @param string $sql
     * @param array $params
     * @return array Of arrays or objects, depending on PDO::ATTR_DEFAULT_FETCH_MODE
     */
    public function selectQuery($sql, $params = false)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get a result statement for a given query. Handles errors.
     * 
     * @param string $sql The SQL statement to execute.
     * @param array $params Array of param => value pairs.
     * @return PDOStatement Resulting PDOStatement.
     */
    public function query($sql, $params = false)
    {
        if (is_array($params) && count($params) > 0) {
            $stmt = self::$pdo->prepare($sql);
            foreach ($params as $placeholder => $value) {
                $stmt->bindValue($placeholder, $value);
            }
            $result = $stmt->execute();
            if (!$result) {
                throw new PDOException('Unable to execute: '.$sql);
            } else {
            //echo '<p>Executed: '.$sql.'<br />with '.  print_r($params, true).'</p>';
            }
        } else {
            $stmt = self::$pdo->query($sql);
        }
        return $stmt;
    }

}
