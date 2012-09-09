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
        } else {
            $this->view->table = false;
        }
        $this->view->output();
    }

}