<?php

class ADBT_View_Database_Index extends ADBT_View_Database_Base
{

    /** @var ADBT_Model_Table */
    public $table;

    public $title = "Database";

    public function __construct()
    {
        parent::__construct();
    }

    public function outputContent()
    {
        parent::outputContent();
        $this->tableView = new ADBT_View_Database_Table();
        $this->tableView->table = $this->table;
        $this->tableView->output();
    }

}