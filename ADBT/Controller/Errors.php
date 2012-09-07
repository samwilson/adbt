<?php

class Controller_Errors extends Controller_Base {

    public function general($code, $message) {
        header('HTTP/1.1 404 Not Found');
        $view = new View_Errors_General();
        $view->code = $code;
        $view->message = $message;
        $view->output();
    }

}