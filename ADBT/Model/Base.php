<?php

class ADBT_Model_Base
{

    /** @var PDO */
    protected $pdo;

    public function __construct()
    {
        $config = Config::$db;
        $dsn = 'mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'];
//        try {
            $user = $config['username'];
            $pass = $config['password'];
            $attr = array(PDO::ATTR_TIMEOUT => 10);
            $this->pdo = new PDO($dsn, $user, $pass, $attr);
//        } catch (PDOException $e) {
//            die('Connection failed: ' . $e->getMessage());
//        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
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
            $stmt = $this->pdo->prepare($sql);
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
            $stmt = $this->pdo->query($sql);
        }
        if (!$stmt) {
            $msg = join(', ', $this->pdo->errorInfo());
            $msg .= " Query was: $sql";
            throw new PDOException($msg);
        }
        return $stmt->fetchAll();
    }

}