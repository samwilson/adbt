<?php
/**
 * General configuration file.
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
 * The Config class.  Never instantiated; all attributes public and static.
 *
 * @category Applications
 * @package  ADBT
 * @author   Sam Wilson <sam@samwilson.id.au>
 * @license  Simplified BSD License
 * @link     http://github.com/samwilson/adbt
 */
class Config
{

    public static $path_to_local = '../adbt_local'; // No slash
    public static $base_path = '/adbt'; // No slash
    public static $db = array(
        'hostname' => 'localhost',
        'username' => '',
        'password' => '',
        'database' => ''
    );
    public static $ldap = array(
        'hostname' => '',
        'suffix' => '',
    );
    public static $permissions_table = false;
    public static $site_title = 'ADBT';
    public static $rows_per_page = 10;
}
