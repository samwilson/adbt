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
    $path = str_replace('_', '/', $className);
    $filename = $path . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullPath)) {
            include_once $filename;
        }
    }
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    echo '<div class="backtrace">'
        .'<strong>ERROR: '.$message.'</strong>'
        .'<table><caption>Backtrace</caption><tr>'
        .'<th>File</th>'
        .'<th>Line</th>'
        .'<th>Class</th>'
        .'<th>Function</th>'
        .'</tr>';
    foreach ($trace as $step) {
        echo '<tr>'
            .'<td>'.(isset($step['file']) ? $step['file'] : '').'</td>'
            .'<td>'.(isset($step['line']) ? $step['line'] : '').'</td>'
            .'<td>'.(isset($step['class']) ? $step['class'] : '').'</td>'
            .'<td>'.(isset($step['function']) ? $step['function'] : '').'</td>'
            .'</tr>';
    }
    echo '</table></div>';
}
set_error_handler('exceptions_error_handler');


$app = new ADBT_App();
$app->run();
