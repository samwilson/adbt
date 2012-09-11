<?php
/**
 * The entrance point into ADBT.  This file defines class autoloading, and
 * launches the ADBT application.
 *
 * PHP Version 5.3
 * 
 * @category Applications
 * @package  ADBT
 * @author   Sam Wilson <sam@samwilson.id.au>
 * @license  Simplified BSD License
 * @link     http://github.com/samwilson/kohana_webdb
 */
/**
 * A PEAR-style autoloader that looks through the include path segments.
 * 
 * @param string $className The name of the class to load.
 * 
 * @return void
 */
function __autoload($className)
{
    $path = str_replace('_', '/', $className);
    $filename = $path . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullPath)) {
            include_once $filename;
        }
    }
}

$app = new ADBT_App();
$app->run();
