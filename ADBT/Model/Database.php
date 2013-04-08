<?php

class ADBT_Model_Database extends ADBT_Model_Base
{

    protected $table_names;
    protected $tables;

    public function __construct()
    {
        parent::__construct();
        $this->dbInit();
    }

    /**
     * Get the database name.
     * 
     * @global array[string] $database_config
     * @return string The database name
     * @return boolean False if no DB name is given in config.php
     */
    public function getName()
    {
        global $database_config;
        if (isset($database_config['database']) && !empty($database_config['database'])) {
            return $database_config['database'];
        } else {
            return false;
        }
    }

    public function getTableNames()
    {
        if (!is_array($this->table_names)) {
            $this->table_names = array();
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
            $tables = $this->selectQuery("SHOW TABLES");
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            foreach ($tables as $table) {
                $this->table_names[] = $table[0];
            }
        }
        return $this->table_names;
    }

    /**
     * Wrapper for PDO::getAvailableDrivers()
     * 
     * @return array
     */
    public function getAvailableDrivers()
    {
        return $this->pdo->getAvailableDrivers();
    }

    /**
     * @param string $tableName
     * @return ADBT_Model_Table The table object.
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            $table = new ADBT_Model_Table($this, $tableName);
            if ($table->can('read')) {
                $this->tables[$tableName] = $table;
            }
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
    public function getTables($grouped = false)
    {
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
