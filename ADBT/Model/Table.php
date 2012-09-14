<?php

class ADBT_Model_Table extends ADBT_Model_Base
{

    /** @var ADBT_Model_Database The database to which this table belongs. */
    protected $database;

    /** @var string The name of this table. */
    protected $name;

    /** @var string This table's comment. False until initialised. */
    protected $comment = false;

    /** @var string The SQL statement used to create this table. */
    protected $_definingSql;

    /**
     * @var array[string => ADBT_Model_Table] Array of tables referred to by
     * columns in this one.
     */
    protected $_referenced_tables;

    /**
     * @var array[string => ADBT_Model_Column] Array of column names and
     * objects for all of the columns in this table.
     */
    protected $columns;

    /** @var Pagination */
    protected $_pagination;

    /** @var array */
    protected $_filters = array();

    /** @var array Permitted operators. */
    protected $_operators = array(
        'like' => 'contains',
        'not like' => 'does not contain',
        '=' => 'is',
        '!=' => 'is not',
        'empty' => 'is empty',
        'not empty' => 'is not empty',
        '>=' => 'is greater than or equal to',
        '>' => 'is greater than',
        '<=' => 'is less than or equal to',
        '<' => 'is less than'
    );

    /**
     * @var integer|false The number of currently-filtered rows, or false if no
     * query has been made yet or the filters have been reset.
     */
    protected $_row_count = FALSE;

    /**
     * Create a new database table object.
     *
     * @param ADBT_Model_Database The database to which this table belongs.
     * @param string $name The name of the table.
     */
    public function __construct($db, $name)
    {
        parent::__construct();
        $this->database = $db;
        $this->name = $name;
        if (!isset($this->columns)) {
            $this->columns = array();
            $columns = $this->selectQuery("SHOW FULL COLUMNS FROM $name");
            foreach ($columns as $column_info) {
                $column = new ADBT_Model_Column($this, $column_info);
                $this->columns[$column->getName()] = $column;
            }
        }
    }

    public function add_filter($column, $operator, $value)
    {
        $valid_columm = in_array($column, array_keys($this->columns));
        $valid_operator = in_array($operator, array_keys($this->_operators));
        $valid_value = (strpos($operator, 'empty') !== false)
                || (strpos($operator, 'empty') === false && !empty($value));
        if ($valid_columm && $valid_operator && $valid_value) {
            $this->_filters[] = array(
                'column' => $column,
                'operator' => $operator,
                'value' => trim($value)
            );
        }
    }

    /**
     * Add all of the filters given in $_GET['filters'].  This is used in both
     * the [index](api/Controller_WebDB#action_index)
     * and [export](api/Controller_WebDB#action_export) actions.
     *
     * @return void
     */
    public function add_GET_filters()
    {
        $filters = Arr::get($_GET, 'filters', array());
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $column = arr::get($filter, 'column', FALSE);
                $operator = arr::get($filter, 'operator', FALSE);
                $value = arr::get($filter, 'value', FALSE);
                $this->add_filter($column, $operator, $value);
            }
        }
    }

    /**
     *
     * @param Database_Query_Builder_Select $query
     */
    public function apply_filters(&$query)
    {
        $fk1_alias = '';
        foreach ($this->_filters as $filter) {

            // FOREIGN KEYS
            $column = $this->columns[$filter['column']];
            if ($column->is_foreign_key()) {
                $fk1_table = $column->get_referenced_table();
                $fk1_title_column = $fk1_table->get_title_column();
                $fk1_alias .= 'f';
                $query->join(array($fk1_table->getName(), $fk1_alias), 'LEFT OUTER')
                        ->on($this->name . '.' . $column->get_name(), '=', $fk1_alias . '.id');
                $filter['column'] = $fk1_alias . '.' . $fk1_title_column->getName();
                // FK is also an FK?
                if ($fk1_title_column->is_foreign_key()) {
                    $fk2_table = $fk1_title_column->get_referenced_table();
                    $fk2_title_column = $fk2_table->get_title_column();
                    $fk2_alias = $fk1_alias . 'f';
                    $query->join(array($fk2_table->getName(), $fk2_alias), 'LEFT OUTER')
                            ->on($fk1_alias . '.' . $fk1_title_column->getName(), '=', $fk2_alias . '.id');
                    $filter['column'] = $fk2_alias . '.' . $fk2_title_column->getName();
                }
            }

            // LIKE or NOT LIKE
            if ($filter['operator'] == 'like' || $filter['operator'] == 'not like') {
                $filter['value'] = '%' . $filter['value'] . '%';
                $filter['column'] = DB::expr('CONVERT(' . $filter['column'] . ', CHAR)');
            }

            // IS EMPTY
            if ($filter['operator'] == 'empty') {
                $query->where($filter['column'], 'IS', NULL);
                $query->or_where($filter['column'], '=', '');
                $filter['column'] = '';
            }

            // IS NOT EMPTY
            if ($filter['operator'] == 'not empty') {
                $query->where($filter['column'], 'IS NOT', NULL);
                $query->and_where($filter['column'], '!=', '');
                $filter['column'] = '';
            }

            if (!empty($filter['column'])) {
                $query->where($filter['column'], $filter['operator'], $filter['value']);
            }
        } // end foreach filter
        // Get WHERE permissions
        foreach ($this->get_permissions() as $perm) {
            if (!empty($perm['where_clause'])) {
                $query->and_where(DB::expr($perm['where_clause'] . ' AND 1'), '=', 1);
            }
        }
    }

    /**
     *
     * @param Database_Query_Builder_Select $query
     */
    public function apply_ordering(&$query)
    {
        $this->orderby = Arr::get($_GET, 'orderby', '');
        $this->orderdir = (Arr::get($_GET, 'orderdir', 'desc') == 'asc') ? 'asc' : 'desc';
        if (!in_array($this->orderby, array_keys($this->get_columns()))) {
            $this->orderby = $this->get_title_column()->get_name();
        }
        if ($this->get_column($this->orderby)->is_foreign_key()) {
            $fk1_alias = 'o1';
            $fk1_table = $this->get_column($this->orderby)->get_referenced_table();
            $query->join(array($fk1_table->getName(), $fk1_alias), 'LEFT OUTER');
            $query->on($this->getName() . '.' . $this->orderby, '=', "$fk1_alias.id");
            $orderby = $fk1_alias . '.' . $fk1_table->get_title_column()->getName();
            if ($fk1_table->get_title_column()->is_foreign_key()) {
                $fk2_alias = 'o2';
                $fk2_table = $fk1_table->get_title_column()->get_referenced_table();
                $query->join(array($fk2_table->getName(), $fk2_alias), 'LEFT OUTER');
                $query->on($fk1_alias . '.' . $fk1_table->get_title_column()->getName(), '=', "$fk2_alias.id");
                $orderby = $fk2_alias . '.' . $fk2_table->get_title_column()->getName();
            }
            $query->order_by($orderby, $this->orderdir);
        } else {
            $query->order_by($this->getName() . '.' . $this->orderby, $this->orderdir);
        }
    }

    public function getOrderBy()
    {
        return $this->orderby;
    }

    public function getOrderDir()
    {
        return $this->orderdir;
    }

    /**
     * Get rows, with pagination.
     *
     * Note that rows are returned as arrays and not objects, because MySQL
     * allows column names to begin with a number, but PHP does not variables to
     * do so.
     *
     * @return array[array[string=>string]] The row data
     */
    public function getRows($with_pagination = TRUE)
    {
        $columns = array();
        foreach (array_keys($this->columns) as $col) {
            $columns[] = $this->name . '.' . $col;
        }
        $selectClause = 'SELECT ' . join(', ', $columns);
        $fromClause = ' FROM `' . $this->getName() . '`';
        //$query->from($this->getName());
        //$this->apply_filters($query);
        //$this->apply_ordering($query);
        $orderClause = ' ORDER BY ' . $this->getOrderBy() . ' ' . $this->getOrderDir();
        $sql = $selectClause . $fromClause . $orderClause;

        // Then limit to the ones on the current page.
        if ($with_pagination) {
            $pagination = $this->get_pagination();
            //$sql = preg_replace('/SELECT(.*)FROM/', 'SELECT COUNT(*) FROM', $sql);
            $sql .= ' LIMIT '.$pagination['rows_per_page'];
            $sql .= ' OFFSET '.$pagination['starting_row'];
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $rows = $this->selectQuery($sql);
            
//            $pagination_query = clone $query;
//            $row_count = $pagination_query
//                    ->select_array(array(DB::expr('COUNT(*) AS total')))
//                    ->execute($this->_db)
//                    ->current();
//            $this->_row_count = $row_count['total'];
//            $config = array('total_items' => $this->_row_count);
            //$this->_pagination = new Pagination($config);
//            $query->offset($this->_pagination->offset);
//            $query->limit($this->_pagination->items_per_page);
        } else {
            $rows = $this->selectQuery($sql);
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $rows;
    }

    /**
     * Get a single row as an associative array.
     *
     * @param integer $id The ID of the row to get.
     * @return array
     */
    public function get_row($id)
    {
        $pk_column = $this->get_pk_column();
        $pk_name = (!$pk_column) ? 'id' : $pk_column->getName();
        $sql = "SELECT * FROM `".$this->getName()."` "
             . "WHERE $pk_name = :id "
             . "LIMIT 1";
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $row = $this->selectQuery($sql, array(':id'=>$id));
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        return $row[0];
    }

    public function get_default_row()
    {
        $row = array();
        foreach ($this->getColumns() as $col) {
            $row[$col->getName()] = $col->get_default();
        }
        return $row;
    }

    /**
     * Get this table's database object.
     *
     * @return ADBT_Model_Database The database to which this table belongs.
     */
    /* public function getDatabase()
      {
      return $this->_db;
      } */

    /**
     * Get this table's name.
     *
     * @return string The name of this table.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get a list of permitted operators.
     *
     * @return array[string]=>string List of operators.
     */
    public function get_operators()
    {
        return $this->_operators;
    }

    /**
     * Get the pagination object for this table.
     *
     * @return Pagination
     */
    public function get_pagination()
    {
        if (!isset($this->_pagination)) {
            $total_row_count = $this->count_records();
            $this->_pagination = array(
                'total_count' => $total_row_count,
                'rows_per_page' => 10,
                'pages' => ceil($total_row_count/10),
                'starting_row' => 1,
                'current_page' => 1,
            );
        }
        return $this->_pagination;
    }

    /**
     * Get the number of rows in the current filtered set.  This leaves the
     * actual counting up to `$this->get_rows()`, rather than doing the query
     * itself, because filtering is applied in that method, and I didn't want to
     * duplicate that here (or anywhere else).
     *
     * @todo Rename this to `row_count()`.
     * @return integer
     */
    public function count_records()
    {
        if (!$this->_row_count) {
            $this->_row_count = count($this->getRows(FALSE));
        }
        return $this->_row_count;
    }

    /**
     * Get one of this table's columns.
     *
     * @return ADBT_Model_Column The column.
     */
    public function get_column($name)
    {
        return $this->columns[$name];
    }

    /**
     * Get a list of this table's columns.
     *
     * @return array[ADBT_Model_Column] This table's columns.
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the table comment text.
     *
     * @return string
     */
    public function getComment()
    {
        if (!$this->comment) {
            $sql = $this->_get_defining_sql();
            $comment_pattern = '/.*\)(?:.*COMMENT.*\'(.*)\')?/si';
            preg_match($comment_pattern, $sql, $matches);
            $this->comment = (isset($matches[1])) ? $matches[1] : '';
        }
        return $this->comment;
    }

    /**
     * Get the title text for a given row.  This is the value of the 'title
     * column' for that row.  If the title column is a foreign key, then the
     * title of the foreign row is used (this is recursive, to allow FKs to
     * reference FKs to an arbitrary depth).
     *
     * @param integer $id The row ID.
     * @return string The title of this row.
     */
    public function get_title($id)
    {
        $row = $this->get_row($id);
        $title_column = $this->get_title_column();
        // If the title column is  FK, pass the title request through.
        if ($title_column->is_foreign_key()) {
            $fk_row_id = $row[$title_column->getName()];
            return $title_column->get_referenced_table()->get_title($fk_row_id);
        }
        // Otherwise, get the text.
        if (isset($row[$title_column->getName()])) {
            return $row[$title_column->getName()];
        } else {
            var_dump($row);
            return implode(' | ', $row); // This is ridiculous.
        }
    }

    /**
     * Get the first unique-keyed column, or if there is no unique non-ID column
     * then use the second column (because this is often a good thing to do).
     * Unless there's only one column; then, just use that.
     * 
     * @return ADBT_Model_Column
     */
    public function get_title_column()
    {
        // Try to get the first unique key
        foreach ($this->getColumns() as $column) {
            if ($column->is_unique_key())
                return $column;
        }
        // But if that fails, just use the second (or the first) column.
        $columnIndices = array_keys($this->columns);
        if (isset($columnIndices[1])) {
            $titleColName = $columnIndices[1];
        } else {
            $titleColName = $columnIndices[0];
        }
        //$titleColName = Arr::get($columnIndices, 1, Arr::get($columnIndices, 0, 'id'));
        return $this->columns[$titleColName];
    }

    /**
     * Get the SQL statement used to create this table, as given by the 'SHOW
     * CREATE TABLE' command.
     *
     * @return string The SQL statement used to create this table.
     */
    private function _get_defining_sql()
    {
        if (!isset($this->_definingSql)) {
            $defining_sql = $this->pdo->query("SHOW CREATE TABLE `$this->name`");
            //exit(var_dump($defining_sql));
            if ($defining_sql->columnCount() > 0) {
                $defining_sql = $defining_sql->fetch();
                //$defining_sql->next();
                //$defining_sql = $defining_sql->as_array();
                //$defining_sql = $defining_sql[0];
                if (isset($defining_sql->{'Create Table'})) {
                    $defining_sql = $defining_sql->{'Create Table'};
                } elseif (isset($defining_sql->{'Create View'})) {
                    $defining_sql = $defining_sql->{'Create View'};
                }
            } else {
                throw new Kohana_Exception('Table not found: ' . $this->name);
            }
            $this->_definingSql = $defining_sql;
        }
        return $this->_definingSql;
    }

    /**
     *
     */
    public function get_permissions()
    {
        $out = array();
        foreach ($this->database->getPermissions() as $perm) {
            if ($perm['table_name'] == '*' OR $perm['table_name'] == $this->name) {
                $out[] = $perm;
            }
        }
        return $out;
    }

    /**
     * Get this table's Primary Key column.
     * 
     * @return ADBT_Model_Column The PK column.
     */
    public function get_pk_column()
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isPrimaryKey())
                return $column;
        }
        return FALSE;
    }

    /**
     * Get a list of a table's foreign keys and the tables to which they refer.
     * This does <em>not</em> take into account a user's permissions (i.e. the
     * name of a table which the user is not allowed to read may be returned).
     *
     * @return array[string => string] The list of <code>column_name => table_name</code> pairs.
     */
    public function get_referenced_tables()
    {
        if (!isset($this->_referenced_tables)) {
            $definingSql = $this->_get_defining_sql();
            $foreignKeyPattern = '|FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)`|';
            preg_match_all($foreignKeyPattern, $definingSql, $matches);
            if (isset($matches[1]) && count($matches[1]) > 0) {
                $this->_referenced_tables = array_combine($matches[1], $matches[2]);
            } else {
                $this->_referenced_tables = array();
            }
        }
        return $this->_referenced_tables;
    }

    /**
     * Get tables with foreign keys referring here.
     *
     * @return array Of the format: `array('table' => ADBT_Model_Table, 'column' => string)`
     */
    public function get_referencing_tables()
    {
        $out = array();
        foreach ($this->database->getTables() as $table) {
            $foreign_tables = $table->get_referenced_tables();
            foreach ($foreign_tables as $foreign_column => $foreign_table) {
                if ($foreign_table == $this->name) {
                    $out[] = array('table' => $table, 'column' => $foreign_column);
                }
            }
        }
        return $out;
    }

    public function get_filters()
    {
        return $this->_filters;
    }

    /**
     * Get a list of the names of the foreign keys in this table.
     *
     * @return array[string] Names of foreign key columns in this table.
     */
    public function get_foreign_key_names()
    {
        return array_keys($this->get_referenced_tables());
    }

    /**
     * Find out whether or not the current user has the given permission for any
     * of the records in this table.
     *
     * @return boolean
     */
    public function can($perm)
    {
        foreach ($this->getColumns() as $column) {
            if ($column->can($perm)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get the database to which this table belongs.
     *
     * @return ADBT_Model_Database The database object.
     */
    public function getDatabase()
    {
        return $this->database;
    }

    public function getOneLineSummary()
    {
        $colCount = count($this->get_columns());
        return $this->name . " ($colCount columns)";
    }

    /**
     * Get a string representation of this table; a succinct summary of its
     * columns and their types, keys, etc.
     *
     * @return string A summary of this table.
     */
    public function __toString()
    {
        $colCount = count($this->get_columns());
        $out = "\n+-----------------------------------------+\n";
        $out .= "| " . $this->name . " ($colCount columns)\n";
        $out .= "+-----------------------------------------+\n";
        foreach ($this->get_columns() as $column) {
            $out .= "| $column \n";
        }
        $out .= "+-----------------------------------------+\n\n";
        return $out;
    }

    /**
     * Get an XML representation of the structure of this table.
     *
     * @return DOMElement The XML 'table' node.
     */
    public function toXml()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $table = $dom->createElement('table');
        $dom->appendChild($table);
        $name = $dom->createElement('name');
        $name->appendChild($dom->createTextNode($this->name));
        $table->appendChild($name);
        foreach ($this->get_columns() as $column) {
            $table->appendChild($dom->importNode($column->toXml(), true));
        }
        return $table;
    }

    /**
     *
     * @return <type>
     */
    public function toJson()
    {
        $json = new Services_JSON();
        $metadata = array();
        foreach ($this->get_columns() as $column) {
            $metadata[] = array(
                'name' => $column->getName()
            );
        }
        return $json->encode($metadata);
    }

    /**
     * Remove all filters.
     *
     * @return void
     */
    public function reset_filters()
    {
        $this->_filters = array();
        $this->_row_count = FALSE;
    }

    /**
     * Save data to this table.  If the 'id' key of the data array is numeric,
     * the row with that ID will be updated; otherwise, a new row will be
     * inserted.
     *
     * @param array  $data  The data to insert; if 'id' is set, update.
     * @return int          The ID of the updated or inserted row.
     */
    public function save_row($data)
    {

        $columns = $this->getColumns();

        /*
         * Check permissions on each column.
         */
        foreach ($columns as $column_name => $column) {
            if (!isset($data[$column_name])) {
                continue;
            }
            $can_update = $column->can('update');
            $can_insert = $column->can('insert');
            if ($column_name != 'id' && (
                    (!$can_update && isset($data['id'])) || (!$can_insert && !isset($data['id']))
                    )) {
                unset($data[$column_name]);
            }
        }

        /*
         * Go through all data and clean it up before saving.
         */
        foreach ($data as $field => $value) {

            // Make sure this column exists in the DB.
            if (!isset($columns[$field])) {
                unset($data[$field]);
                continue;
            }

            $column = $columns[$field];

            /*
             * Booleans
             */
            if ($column->get_type() == 'int' && $column->get_size() == 1) {
                if (($value == NULL || $value == '') && !$column->is_required()) {
                    $data[$field] = NULL;
                } elseif ($value === '0'
                        || $value === 0
                        || strcasecmp($value, 'false') === 0
                        || strcasecmp($value, 'off') === 0
                        || strcasecmp($value, 'no') === 0) {
                    $data[$field] = 0;
                } else {
                    $data[$field] = 1;
                }
                //exit(kohana::debug($data[$field]));
            }

            /*
             * Nullable empty fields should be NULL.
             */ elseif (!$column->is_required() && empty($value)) {
                $data[$field] = NULL;
            }

            /*
             * Foreign keys
             */ elseif ($column->is_foreign_key() && ($value <= 0 || $value == '')) {
                $data[$field] = NULL;
            }

            /*
             * Numbers
             */ elseif (!is_numeric($value)
                    && (substr($column->get_type(), 0, 3) == 'int'
                    || substr($column->get_type(), 0, 7) == 'decimal'
                    || substr($column->get_type(), 0, 5) == 'float')
            ) {
                $data[$field] = NULL; // Stops empty strings being turned into 0s.
            }

            /*
             * Dates & times
             */ elseif (($column->get_type() == 'date' || $column->get_type() == 'datetime' || $column->get_type() == 'time') && $value == '') {
                $data[$field] = null;
            }
        }
        //print_r($data); exit();

        // Update?
        $pk_name = $this->get_pk_column()->getName();
        if (isset($data[$pk_name]) && is_numeric($data[$pk_name])) {
            $sql = "UPDATE ".$this->getName()." SET ";
            foreach ($data as $col => $val) {
                $sql .= "`$col` => :$col, ";
            }
            $sql .= "WHERE $pk_name = :$pk_name";
            var_dump($sql);
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $col => $val) {
                $stmt->bindParam(":$col", $val);
            }
            $stmt->execute();
            $id = $data[$pk_name];
        }
        // Or insert?
        else {
            $sql = "INSERT INTO ".$this->getName()
                 . " ( `".join("`, `", array_keys($data))."` ) VALUES "
                 . " ( :".join(", :", array_keys($data))." )";
            $stmt = $this->pdo->prepare($sql);
            foreach ($data as $col => $val) {
                $stmt->bindParam(":$col", $value);
            }
            $stmt->execute();
            $id = $this->pdo->lastInsertId();
        }
        return $id;
    }

}