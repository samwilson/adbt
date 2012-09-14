<?php

class ADBT_Model_Database extends ADBT_Model_Base
{

    protected $table_names;
    protected $tables;
    /** @var ADBT_Model_User */
    protected $user;

    public function __construct($user)
    {
        parent::__construct();
        $this->user = $user;
        if (in_array('permissions', $this->getTableNames())) {
            $permissions = $this->selectQuery("SELECT * FROM permissions");
        } else {
            $permissions = false;
        }
    }

    public function getPermissions()
    {
        $default_permissions = array(array(
                'table_name' => '*',
                'column_names' => '*',
                'where_clause' => NULL,
                'permission' => '*',
                'identifier' => '*',
                ));
        $permissions_table = Config::$permissions_table;
        if (!$permissions_table || !in_array($permissions_table, $this->getTableNames())) {
            return $default_permissions;
        }
        $sql = "SELECT * FROM `$permissions_table` WHERE `group` IN (".join(',',$this->user->getGroups()).")";
        return $this->selectQuery($sql);
    }

    public function getName()
    {
        return Config::$db['database'];
    }

    public function getTableNames()
    {
        if (!is_array($this->table_names)) {
            $this->table_names = array();
            $stmt = $this->pdo->query("SHOW TABLES");
            if (!$stmt) {
                $error = $this->pdo->errorInfo();
                //0 SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
                //1 Driver-specific error code.
                //2 Driver-specific error message.
                trigger_error(join(', ', $error), E_USER_ERROR);
            }
            foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $table) {
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
