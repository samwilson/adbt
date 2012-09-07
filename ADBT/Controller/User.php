<?php

class ADBT_Controller_User extends ADBT_Controller_Base {

    public function login() {
        $this->view->ldapDomains = $this->user->getDomains();
        $this->view->output();
    }

}