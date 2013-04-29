<?php

abstract class ADBT_Controller_Base
{

    protected $name;

    /** @var string The name of the currently-requested action. */
    protected $currentAction;
    protected $defaultAction = 'home';
    /** @var ADBT_App The application. */
    protected $app;

    /** @var ADBT_Model_User */
    protected $user;

    /** @var ADBT_View_HTML The view object. */
    protected $view = false;

    public function __construct($app, $action_name)
    {
        $this->app = $app;
        $this->currentAction = $action_name;
        $this->instantiateView();
        $this->instantiateUser();
        $this->queryStringSession();
    }

    public function currentAction()
    {
        if (!empty($this->currentAction)) {
            return $this->currentAction;
        } else {
            return $this->defaultAction;
        }
    }

    public function instantiateUser()
    {
        $model_user = $this->app->getClassname('Model_User');
        $this->user = new $model_user($this->app);
        $this->app->user = $this->user;
        if ($this->view) {
            $this->view->user = $this->user;
        }
    }

    public function instantiateView()
    {
        $view_class = 'View_' . $this->getName() . '_' . ucwords($this->currentAction());
        $view_classname = $this->app->getClassname($view_class);
        if (!$view_classname) {
            $view_classname = $this->app->getClassname('View_HTML');
        }
        $this->view = new $view_classname($this->app);
        $this->view->controller_name = $this->getName();
        $this->view->action_name = $this->currentAction();
    }

    public function getName()
    {
        $last_underscore = strrpos(get_class($this), '_');
        return substr(get_class($this), $last_underscore+1);
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
        if (count($_GET) > 0) {
            $existing_saved = (isset($_SESSION['qs'])) ? $_SESSION['qs'] : array();
            $_SESSION['qs'] = array_merge($existing_saved, $_GET);
        }

        // Load query string variables, unless they're already present.
        if (isset($_SESSION['qs']) && count($_SESSION['qs']) > 0) {
            $has_new = false; // Whether there's anything in SESSION that's not in GET
            foreach ($_SESSION['qs'] as $key => $val) {
                if (!isset($_GET[$key])) {
                    $_GET[$key] = $val;
                    $has_new = true;
                }
            }
            if ($has_new) {
                /*$query = '?';
                foreach ($_SESSION['qs'] as $key=>$val) {
                    $query .= "&$key=$val";
                }*/
                header('Location:?' . http_build_query($_SESSION['qs']));
                /*$query = URL::query($_SESSION['qs']);
                $_SESSION['qs'] = array();
                $uri = $this->url(FALSE, TRUE).$this->request->uri.$query;
                $this->request->redirect($uri);*/
            }
        }
    }
}
