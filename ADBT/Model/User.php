<?php

class ADBT_Model_User extends ADBT_Model_Base
{
    public function loggedIn() {
        
    }

    public function getDomains() {
        return array_keys(Config::$ldap);
    }
}
