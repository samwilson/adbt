<?php

class ADBT_Controller_Database extends ADBT_Controller_Base
{

    protected $db;
    protected $defaultAction = 'index';

    public function __construct($action_name)
    {
        parent::__construct($action_name);
        $this->db = new ADBT_Model_Database($this->user);
        $this->view->database = $this->db;
        $this->view->tables = $this->db->getTables();
        $this->view->tabs = array(
            'index'  => 'Browse &amp; Search',
            'edit'   => 'New',
            'import' => 'Import',
            'export' => 'Export',
        );
    }

    public function index($table = false, $row = false)
    {
        if ($table) {
            $this->view->table = $this->db->getTable($table);
            $this->view->tableView = new ADBT_View_Database_Table();
            $this->view->tableView->table = $this->view->table;
            $this->view->columns = $this->view->table->getColumns();
        } else {
            $this->view->table = false;
            $this->view->addMessage('Please select from the list at left.', 'info');
        }
        $this->view->output();
    }

    public function edit($table_name = false, $id = false)
    {
        $table = $this->db->getTable($table_name);
        $this->view->table = $table;

        /*
         * Save submitted data.
         */
        if (isset($_POST['save'])) {
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
                $this->view->addMessage('Record saved.', 'info');
                //$url = 'edit/' . $this->database->get_name() . '/' . $this->table->get_name() . '/' . $id;
                //$this->request->redirect($url);
            }
        }

        /*
         * Get data to populate edit form (or give message why not).
         */
        if ($id) {
            $this->view->row = $table->get_row($id);
        } else {
            if (!$table->can('insert')) {
                $this->add_template_message('You do not have permission to add a new record to this table.');
                $this->template->content = null;
                return;
            }
            // Get default data from the database and HTTP request.
            $this->view->row = array_merge($table->get_default_row(), $_GET);
        }
        $this->view->output();
    }

}