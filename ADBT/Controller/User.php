<?php

class ADBT_Controller_User extends ADBT_Controller_Base {

    public function login() {
        $this->view->ldapDomains = $this->user->getDomains();
        if (isset($_POST['email_address'])) {
            $this->user->processLogin($_POST['email_address'], $_POST['password']);
            if ($this->user->loggedIn()) {
                echo 'yes';
            } else {
                echo 'no';
            }
        }
        $this->view->output();
    }

}