<?php

class ADBT_Controller_Database extends ADBT_Controller_Base {

    protected $db;
    protected $defaultAction = 'index';

    public function __construct($action_name) {
        parent::__construct($action_name);
        $this->db = new ADBT_Model_Database();
        $this->view->database = $this->db;
        $this->view->tables = $this->db->getTables();
    }

    public function index($table = false, $row = false) {
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

}