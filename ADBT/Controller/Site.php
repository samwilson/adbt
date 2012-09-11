<?php

class ADBT_Controller_Site extends ADBT_Controller_Base {

    public function home() {
        $this->view->title = 'Welcome';
        $this->view->output();
    }

    public function help() {
//        $db = new ADBT_Model_Database();
//
//        $this->view->db = $db->
    }

}
