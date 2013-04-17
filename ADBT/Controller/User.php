<?php

class ADBT_Controller_User extends ADBT_Controller_Base
{

    protected $name = 'User';

    /** @var ADBT_View_HTML */
    protected $view;

    public function login()
    {
        $this->view->username = '';
        if (isset($_POST['username'])) {
            $this->view->username = trim($_POST['username']);
            $this->user->login($_POST['username'], $_POST['password']);
            if ($this->user->loggedIn()) {
                $this->view->addDelayedMessage('You are now logged in.', 'success');
                header('Location:' . $this->view->url('/'));
                exit();
            } else {
                $this->view->addMessage('Invalid Credentials', 'notice');
            }
        }
        $this->view->useLdap = $this->user->useLdap();
        $this->view->useDB = $this->user->useDB();
        $this->view->title = 'Log In';
        $this->view->output();
    }

    public function logout()
    {
        $this->user->logout();
        header('Location:' . $this->view->url('/user/login'));
    }

}