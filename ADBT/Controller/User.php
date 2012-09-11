<?php

class ADBT_Controller_User extends ADBT_Controller_Base
{

    public function login()
    {
        if (isset($_POST['username'])) {
            $this->user->login($_POST['username'], $_POST['password']);
            if ($this->user->loggedIn()) {
                echo 'yes';
            } else {
                echo 'no';
            }
        }
        $this->view->setTitle('Log In');
        $this->view->output();
    }

    public function logout()
    {
        $this->user->logout();
    }

}