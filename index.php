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
 * @link     http://github.com/samwilson/adbt
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
    $path = str_replace('_', DIRECTORY_SEPARATOR, $className);
    $filename = $path . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullPath)) {
            include_once $filename;
        }
    }
}

function adbt_exception_handler(Exception $e)
{
    echo "<pre class='exception'>---- ERROR ----\n";
    try {
        echo "Class   = ".get_class($e)."\n"
            ."Code    = ".$e->getCode()."\n"
            ."Message = ".$e->getMessage()."\n"
            ."File    = ".$e->getFile()."\n"
            ."Line    = ".$e->getLine()."\n"
            ."Backtrace:\n".$e->getTraceAsString()."\n";
    } catch (Exception $e) {
         print get_class($e)." thrown within the exception handler. Message: ".$e->getMessage()." on line ".$e->getLine();
    }
    echo "---------------</pre>";
    exit(1);
}

/**
 * Turn errors into exceptions.
 * 
 * @param int $errno the level of the error raised
 * @param string $errstr the error message
 * @param string $errfile filename that the error was raised in
 * @param int $errline  line number the error was raised at
 * @param array $errcontext an array that points to the active symbol table at the point the error occurred. In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in. User error handler must not modify error context. 
 * @throws ErrorException
 */
function adbt_error_handler($errno, $errstr, $errfile = null, $errline = null, $errcontext = null)
{
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    return true;
}

set_error_handler('adbt_error_handler');
set_exception_handler('adbt_exception_handler');

set_include_path('.' . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . get_include_path());

require_once __DIR__ . '/config.php';

if (realpath($_SERVER['SCRIPT_FILENAME']) == realpath(__FILE__)) {
    $app = new ADBT_App();
    $app->run();
}

