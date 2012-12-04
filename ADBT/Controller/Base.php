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
        $this->queryStringSession();
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

    /**
     * Save and load query string (i.e. `$_GET`) variables from the `$_SESSION`.
     * The idea is to carry query string variables between requests, even
     * when those variables have been omitted in the URI.
     *
     * 1. If a request has query string parameters, they are saved to
     *    `$_SESSION['qs']`, merging with whatever is already there.
     * 2. If there are parameters saved in `$_SESSION['qs']`, and if they're
     *    not already in the query string, add them and redirect the request to
     *    the resulting URI.
     *
     * @return void
     */
    private function queryStringSession()
    {
        // Save the query string, adding to what's already saved.
        if (count($_GET)>0) {
            $existing_saved = (isset($_SESSION['qs'])) ? $_SESSION['qs'] : array();
            $_SESSION['qs'] = array_merge($existing_saved, $_GET);
        }

        // Load query string variables, unless they're already present.
        if (isset($_SESSION['qs']) && count($_SESSION['qs'])>0) {
            $has_new = FALSE; // Whether there's anything in SESSION that's not in GET
            foreach ($_SESSION['qs'] as $key=>$val) {
                if (!isset($_GET[$key])) {
                    $_GET[$key] = $val;
                    $has_new = TRUE;
                }
            }
            if ($has_new) {
//                $query = '?';
//                foreach ($_SESSION['qs'] as $key=>$val) {
//                    $query .= "&$key=$val";
//                }
                header('Location:?'.http_build_query($_SESSION['qs']));
//                $query = URL::query($_SESSION['qs']);
//                $_SESSION['qs'] = array();
//                $uri = $this->url(FALSE, TRUE).$this->request->uri.$query;
//                $this->request->redirect($uri);
            }
        }
    }
}