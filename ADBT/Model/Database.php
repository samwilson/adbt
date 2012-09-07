<?php

class ADBT_Model_Database extends ADBT_Model_Base {

    protected $table_names;

    public function getName() {
        return $this->config['database'];
    }

    public function getTables() {
        if (!is_array($this->table_names)) {
            $this->table_names = array();
            $stmt = $this->pdo->query("SHOW TABLES");
            foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $table) {
                $this->table_names[] = $table[0];
            }
        }
        return $this->table_names;
    }

}
