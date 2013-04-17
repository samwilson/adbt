<?php

class ADBT_Controller_Database extends ADBT_Controller_Base
{

    /** @var ADBT_Model_Database */
    protected $db;

    /** @var string */
    protected $name = 'Database';

    /** @var string */
    protected $defaultAction = 'index';

    public function __construct($app, $action_name)
    {
        parent::__construct($app, $action_name);
        try {
            $model_database = $this->app->getClassname('Model_Database');
            $this->db = new $model_database($this->app, $this->user);
        } catch (PDOException $e) {
            // If we can't connect and don't have a username, redirect to login.
            global $database_config;
            if (empty($database_config['username'])) {
                header('Location:'.$this->view->url('/user/login'));
                exit(0);
            }
            $this->view->addMessage("Unable to instantiate database: ".$e->getMessage());
        }
        if ($this->db) {
            $this->view->database = $this->db;
            $this->view->tables = $this->db->getTables();
        }
        $this->view->tabs = array(
            'index'  => 'Browse &amp; Search',
            'edit'   => 'New',
            'import' => 'Import',
            'export' => 'Export',
        );
    }

    public function autocomplete($table)
    {
        $table = $this->db->getTable($table);
        if (isset($_GET['term'])) {
            $table->addFilter($table->get_title_column()->getName(), 'like', $_GET['term']);
        }
        $this->view->data = array();
        $pk_column_name = $table->get_pk_column()->getName();
        foreach ($table->getRows() as $row) {
            $row['label'] = $table->get_title($row[$pk_column_name]);
            $this->view->data[] = $row;
        }
        $this->view->output();
    }

    public function index($table_name = false)
    {
        if ($table_name) {
            $table = $this->db->getTable($table_name);

            // Filters
            if (isset($_GET['filters']) && is_array($_GET['filters'])) {
                foreach ($_GET['filters'] as $filter) {
                    $column = $filter['column'];
                    $operator = $filter['operator'];
                    $value = $filter['value'];
                    $table->addFilter($column, $operator, $value);
                }
            }

            // Pagination
            $page_num = (isset($_GET['page'])) ? $_GET['page'] : 1;
            if ($page_num > $table->get_page_count())
            {
                // Redirect to proper URL with max page count.
                $page_num = $table->get_page_count();
            }
            $table->page($page_num);

            // Sorting
            if (isset($_GET['orderby'])) {
                $table->setOrderBy($_GET['orderby']);
            }
            if (isset($_GET['orderdir'])) {
                $table->setOrderDir($_GET['orderdir']);
            }

            $this->view->title = $this->view->titlecase($table->getName());
            $table_classname = $this->app->getClassname('View_Database_Table');
            $this->view->tableView = new $table_classname($this->app);
            $this->view->tableView->table = $table;
            $this->view->filters = $table->getFilters();
            $this->view->filters[] = array(
                'column' => $table->get_title_column()->getName(),
                'operator' => 'contains',
                'value' => '',
            );
        } else {
            $table = false;
            $this->view->addMessage('Please select from the list at left.', 'info');
        }
        $this->view->table = $table;
        $this->view->output();
    }

    public function edit($table_name = false, $id = false)
    {
        $table = $this->db->getTable($table_name);
        $this->view->setId($id);
        $this->view->setTable($table);

        /*
         * Save submitted data.
         */
        if (isset($_POST['save']) && ($table->can('insert') || $table->can('update'))) {
            // Get row (the first element of $_POST, to permit for multiples).
            $row = array_shift($_POST['data']);
            // Assume unset (i.e. unsent) checkboxes are unchecked.
            foreach ($table->getColumns() as $column_name => $column) {
                if ($column->get_type() == 'int' && $column->get_size() == 1 && !isset($row[$column_name])) {
                    $row[$column_name] = 0;
                }
            }
            // Save row
            $id = $table->save_row($row);
            if (!empty($id)) {
                $this->view->addDelayedMessage('Record saved.', 'info');
                $url = (isset($_POST['return_to'])) ? $_POST['return_to'] : 'database/edit/'.$table->getName().'/'.$id;
                header('Location:'.$this->view->url($url));
            } else {
                $this->view->addMessage('Unable to save record.', 'error');
            }
        }

        /*
         * Get data to populate edit form (or give message why not).
         */
        if ($id) {
            $this->view->row = $table->get_row($id);
        } else {
            if (!$table->can('insert')) {
                $this->view->addMessage('You do not have permission to add a new record to this table.');
                $this->view->row = false;
            } else {
                // Get default data from the database and HTTP request.
                $this->view->row = array_merge($table->get_default_row(), $_GET);
            }
        }
        $this->view->output();
    }

    public function export($table_name) {
        if ($table_name) {
            $table = $this->db->getTable($table_name);

            // Filters
            if (isset($_GET['filters']) && is_array($_GET['filters'])) {
                foreach ($_GET['filters'] as $filter) {
                    $column = $filter['column'];
                    $operator = $filter['operator'];
                    $value = $filter['value'];
                    $table->addFilter($column, $operator, $value);
                }
            }

            // Sorting and ordering
            if (isset($_GET['orderby'])) {
                $table->setOrderBy($_GET['orderby']);
            }
            if (isset($_GET['orderdir'])) {
                $table->setOrderDir($_GET['orderdir']);
            }

            $tmp_filename = $table->export();

            // Add headers
            $headers = $this->view->titlecase(array_keys($table->getColumns()));
            $headers = join(',',$headers)."\r\n";

            // Send file with a decent name, as UTF
            $filename = date('Y-m-d').'_'.$table->getName();
            header('Content-Encoding: UTF-8');
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-disposition: attachment;filename='.$filename.'.csv');
            // "\xEF\xBB\xBF" is the UTF-8 BOM
            echo "\xEF\xBB\xBF".$headers.file_get_contents($tmp_filename);
            exit(0);
        }
    }

}
