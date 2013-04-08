<?php

class ADBT_Model_Base
{

    /** @var PDO */
    static protected $pdo;

    public function __construct()
    {
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
     * 
     * @param string $sql
     * @param array $params
     * @return array Of arrays or objects, depending on PDO::ATTR_DEFAULT_FETCH_MODE
     * @throws PDOException
     */
    public function selectQuery($sql, $params = false)
    {
        if ($params) {
            $stmt = self::$pdo->prepare($sql);
            foreach ($params as $placeholder => $value) {
                // Parameters are numbered from 1, not 0
                $stmt->bindParam($placeholder+1, $value);
            }
            try {
                $stmt->execute();
            } catch (Exception $e) {
                $sql_view = new ADBT_View_Database_SQL($sql);
                $sql_view->output();
                throw $e;
            }
        } else {
            $stmt = self::$pdo->query($sql);
        }
        if (!$stmt) {
            $msg = join(', ', $this->pdo->errorInfo());
            $msg .= " Query was: $sql";
            throw new PDOException($msg);
        }
        return $stmt->fetchAll();
    }

}