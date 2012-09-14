<?php

class ADBT_App
{

    public function __construct()
    {
        $path_to_local = Config::$path_to_local;
        set_include_path($path_to_local . PATH_SEPARATOR . get_include_path());;
    }

    /**
     * Get the actual class name of a given class, with 'ADBT_' or 'Local_'
     * prepended, depending on which exists.  If neither do, return false.
     *
     * @param string $classname The 'virtual' class name.
     * @return string|false The existing class name, or false if none found.
     */
    public static function getClassname($classname)
    {
        if (class_exists("Local_$classname")) {
            return "Local_$classname";
        } elseif (class_exists("ADBT_$classname")) {
            return "ADBT_$classname";
        }
        user_error("Unable to find class $classname", E_USER_ERROR);
    }

    public function run()
    {
        $basepath = Config::$base_path;
        $request = strtolower(substr($_SERVER['REQUEST_URI'], strlen($basepath) + 1));
        preg_match_all('|^/?([a-z0-9]*)/?([a-z0-9]*)/?([^?]*)|', $request, $matches);
        $controller_name = empty($matches[1][0]) ? 'Site' : $matches[1][0];
        $action_name = empty($matches[2][0]) ? false : $matches[2][0];
        $param_string = empty($matches[3][0]) ? '' : $matches[3][0];
//        $query_string = $matches[4][0];
//        if (!empty($request)) {
//            $controller_name = ucwords($this->substringUpToFirstDelimiter($request));
//            $request_without_controller = substr($request, strlen($controller_name) + 1);
//            if (!empty($request_without_controller)) {
//                echo $request_without_controller.'<br />';
//                $action_name = $this->substringUpToFirstDelimiter($request_without_controller);
//                echo $action_name.'<br>';
//                $param_string = substr($request_without_controller, strlen($action_name) + 1);
//                exit($param_string);
//            }
//        }
        $controller_classname = ADBT_App::getClassname("Controller_$controller_name");
        if (class_exists($controller_classname)) {
            $controller = new $controller_classname($action_name);
            $action_name = $controller->currentAction();
            if (method_exists($controller, $action_name)) {
                $params = explode('/', $param_string);
                call_user_func_array(array($controller, $action_name), $params);
            }
        } else {
            $error = new Controller_Errors('general');
            $error->general(404, 'Controller Not Found: ' . $controller_name);
        }
    }

    public function substringUpToFirstDelimiter($str)
    {
        //echo $str.'<br />';
        $delim_pos = false;
        if (strpos($str, '/')!==false) $delim_pos = strpos($str, '/');
        elseif (strpos($str, '?')!==false) $delim_pos = strpos($str, '?');
        //echo $delim_pos.'<br />';
        $out = ($delim_pos) ? substr($str, 0, $delim_pos) : $str;
        //echo $out.'<br />';
        return $out;
    }

}