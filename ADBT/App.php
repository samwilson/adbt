<?php

class ADBT_App
{
    public $user;

    protected $default_controller_name = 'Database';

    /** @var array[string] */
    protected $moduleNames = array('ADBT');

    public function __construct()
    {
        if (substr(BASE_URL, -1) == '/') {
            echo 'BASE_URL should not have a trailing slash.';
            exit(1);
        }
    }

    public function setModules($names) {
        $this->moduleNames = $names;
    }

    /**
     * Get the actual class name of a given class, with the module name
     * prepended, if it exists.  If none does, return false.
     *
     * @uses class_exists()
     * @param string $classname The 'virtual' class name.
     * @return string|false The existing class name, or false if none found.
     */
    public function getClassname($classname)
    {
        foreach ($this->getModuleNames() as $modName) {
            $fullClassname = $modName.'_'.$classname;
            if (class_exists($fullClassname)) {
                return $fullClassname;
            }
        }
        return false;
    }

    public function getModuleNames() {
        return $this->moduleNames;
    }

    public function run()
    {
        $base_url_length = strlen(BASE_URL) + 1;
        $uri = strtolower(substr($_SERVER['REQUEST_URI'], $base_url_length));
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $request = explode('/', $uri);
        $controller_name = ucwords(array_shift($request));
        if (empty($controller_name)) {
            $controller_name = $this->default_controller_name;
        }
        $action_name = array_shift($request);
        $controller_classname = ADBT_App::getClassname("Controller_" . $controller_name);
        if (class_exists($controller_classname)) {
            $controller = new $controller_classname($this, $action_name);
            $action_name = $controller->currentAction();
            if (method_exists($controller, $action_name)) {
                call_user_func_array(array($controller, $action_name), $request);
            }
        } else {
            header('HTTP/1.1 404 Not Found');
            echo "Controller Not Found: $controller_name";
            exit(1);
        }
    }

}

