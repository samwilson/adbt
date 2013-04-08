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

if (!defined('BASE_URL')) define('BASE_URL', '/adbt');

if (!defined('SITE_TITLE')) define('SITE_TITLE', 'ADBT');

if (!defined('ROWS_PER_PAGE')) define('ROWS_PER_PAGE', 25);

// Database settings
if (!isset($database_config)) $database_config = array();
if (!isset($database_config['hostname'])) $database_config['hostname'] = 'localhost';
if (!isset($database_config['database'])) $database_config['database'] = 'adbt';
if (!isset($database_config['username'])) $database_config['username'] = false;
if (!isset($database_config['password'])) $database_config['password'] = false;

// LDAP settings
if (!isset($ldap_config)) $ldap_config = array();
if (!isset($ldap_config['hostname'])) $ldap_config['hostname'] = false;
if (!isset($ldap_config['suffix'])) $ldap_config['suffix'] = false;
