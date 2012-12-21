<?php

class ADBT_App
{

    protected $default_controller_name = 'Site';

    public function __construct()
    {
        $path_to_local = Config::$path_to_local;
        set_include_path($path_to_local . PATH_SEPARATOR . get_include_path());
    }

    /**
     * Get the actual class name of a given class, with 'ADBT_' or 'Local_'
     * prepended, depending on which exists.  If neither do, return false.
     *
     * @uses class_exists()
     * @param string $classname The 'virtual' class name.
     * @return string|false The existing class name, or false if none found.
     */
    public static function getClassname($classname)
    {
        if (class_exists("Local_$classname")) {
            return "Local_$classname";
        } elseif (class_exists("ADBT_$classname")) {
            return "ADBT_$classname";
        } else {
            return false;
        }
    }

    public function run()
    {
        $base_url_length = strlen(Config::$base_url) + 1;
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
            $controller = new $controller_classname($action_name);
            $action_name = $controller->currentAction();
            if (method_exists($controller, $action_name)) {
                call_user_func_array(array($controller, $action_name), $request);
            }
        } else {
            $error_controller_classname = ADBT_App::getClassname('Controller_Errors');
            $error = new $error_controller_classname('general');
            $error->general(404, 'Controller Not Found: ' . $controller_name);
        }
    }

//    public function substringUpToFirstDelimiter($str)
//    {
//        //echo $str.'<br />';
//        $delim_pos = false;
//        if (strpos($str, '/') !== false) {
//            $delim_pos = strpos($str, '/');
//        } elseif (strpos($str, '?') !== false) {
//            $delim_pos = strpos($str, '?');
//        }
//        //echo $delim_pos.'<br />';
//        $out = ($delim_pos) ? substr($str, 0, $delim_pos) : $str;
//        //echo $out.'<br />';
//        return $out;
//    }
}

