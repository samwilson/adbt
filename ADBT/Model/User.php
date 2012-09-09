<?php

class ADBT_Model_User extends ADBT_Model_Base {

    public function processLogin($email_address, $password) {
        session_start();
        $email_address = $this->pdo->quote($email_address);
        $passwordHash = new PasswordHash();
        $password = $this->pdo->quote($passwordHash->HashPassword($password));
        $sql = "SELECT 1 FROM `users` WHERE email_address=:email_address AND password=SHA1(:password) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email_address', $email_address);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $matching_users = $stmt->rowCount();
        if ($matching_users > 0) {
            // User exists; log user in.
            $_SESSION['email_address'] = $email_address;
            return true;
        } else {
            // Login failed; re-display login form.
            return false;
        }
    }

    public function loggedIn() {
        
    }

    public function getDomains() {
        if (isset(Config::$ldap) && count(Config::$ldap) > 0) {
            return array_keys(Config::$ldap);
        } else {
            return array();
        }
    }

}
