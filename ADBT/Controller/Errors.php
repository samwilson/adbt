<?php

class ADBT_Controller_Errors extends ADBT_Controller_Base {

    public function general(Exception $exception) {
        $this->view->code = $exception->getCode();
        $this->view->message = $exception->getMessage();
    }

    public function sql(ADBT_Model_Exception_SQL $exception) {
        $this->view->addMessage($exception->getMessage());
        $this->view->exception = $exception;
    }

    public function notfound()
    {
        header('HTTP/1.1 404 Not Found');
    }

    public function __destruct()
    {
        $this->view->output();
    }
}