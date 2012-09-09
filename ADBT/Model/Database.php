<?php

class ADBT_Model_Database extends ADBT_Model_Base {

    protected $table_names;
    protected $tables;

    public function getName() {
        return $this->config['database'];
    }

    public function getTableNames() {
        if (!is_array($this->table_names)) {
            $this->table_names = array();
            $stmt = $this->pdo->query("SHOW TABLES");
            foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $table) {
                $this->table_names[] = $table[0];
            }
        }
        return $this->table_names;
    }

    public function getTable($tableName) {
        if (!isset($this->tables[$tableName])) {
            $this->tables[$tableName] = new ADBT_Model_Table($this, $tableName);
        }
        return $this->tables[$tableName];
    }

    /**
     * Get a list of tables of this database.
     *
     * The `$grouped` parameter...
     *
     * PhpMyAdmin does this for database names
     *
     * @param boolean $grouped Whether or not to return a nested array of table objects.
     * @return array[Webdb_DBMS_Table] Array of [Webdb_DBMS_Table] objects.
     */
    public function getTables($grouped = false) {
        $tablenames = $this->getTableNames();
        asort($tablenames);
        foreach ($tablenames as $tablename) {
            $this->getTable($tablename);
        }
        if (!$grouped) {
            return $this->tables;
        }

        // Group tables together by common prefixes.
        $prefixes = WebDB_Arr::get_prefix_groups(array_keys($this->_tables));
        $groups = array('miscellaneous' => $this->_tables);
        // Go through each table,
        foreach (array_keys($this->_tables) as $table) {
            // and each LCP,
            foreach ($prefixes as $lcp) {
                // and, if the table name begins with this LCP, add the table
                // to the LCP group.
                if (strpos($table, $lcp) === 0) {
                    $groups[$lcp][$table] = $this->_tables[$table];
                    unset($groups['miscellaneous'][$table]);
                }
            }
        }
        return $groups;
    }

}
