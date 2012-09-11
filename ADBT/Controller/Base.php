<?php

class ADBT_Controller_Base {

    /** @var string The name of the currently-requested action. */
    protected $currentAction;

    protected $defaultAction = 'home';

    /** @var ADBT_Model_User */
    protected $user;

    /** @var ADBT_View_Base The view object. */
    protected $view;

    public function __construct($action_name) {
        $this->currentAction = $action_name;
        $this->instantiateView();
        $this->instantiateUser();
    }

    public function currentAction() {
        if (!empty($this->currentAction)) {
            return $this->currentAction;
        } else {
            return $this->defaultAction;
        }
    }

    public function instantiateUser() {
        $this->user = $this->view->user = new ADBT_Model_User();
    }

    public function instantiateView() {
        $view_class = 'View_' . $this->getControllerName() . '_' . ucwords($this->currentAction());
        $view_classname = ADBT_App::getClassname($view_class);
        $this->view = new $view_classname();
        $this->view->controller_name = $this->getControllerName();
        $this->view->action_name = $this->currentAction();
    }

    public function getControllerName() {
        $this_class = get_class($this);
        if (substr($this_class, 0, 6) == 'Local_') {
            $prefix_length = strlen('Local_Controller_');
        } else {
            $prefix_length = strlen('ADBT_Controller_');
        }
        $controller_name = substr(get_class($this), $prefix_length);
        return $controller_name;
    }

}