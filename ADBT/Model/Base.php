<?php

class ADBT_Model_Base
{

    /** @var PDO */
    protected $pdo;

    public function __construct()
    {
        $config = Config::$db;
        $dsn = 'mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'];
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password']);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

}