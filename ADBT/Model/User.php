<?php

class ADBT_Model_User extends ADBT_Model_Base
{

    /** @var boolean */
    protected $loggedIn = false;

    public function __construct()
    {
        parent::__construct();
        $timeout = 60 * 30; // In seconds, i.e. 30 minutes.
        $fingerprint = $this->getSessionFingerprint();
        session_start();
        $timed_out = isset($_SESSION['last_active']) && ($_SESSION['last_active'] < (time() - $timeout));
        $wrong_fingerprint = (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] != $fingerprint);
        if ($timed_out || $wrong_fingerprint) {
            $this->logout();
        }
        session_regenerate_id();
        $_SESSION['last_active'] = time();
        $_SESSION['fingerprint'] = $fingerprint;
        if (isset($_SESSION['username'])) {
            $this->loggedIn = true;
        }
    }

    protected function getSessionFingerprint()
    {
        return md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    public function login($username, $password)
    {
        if ($this->fromLdap()) {
            $this->loggedIn = $this->checkLdap($username, $password);
        } else {
            $this->loggedIn = $this->checkLocal($username, $password);
        }
        if ($this->loggedIn()) {
            $_SESSION['username'] = $username;
            $_SESSION['last_active'] = time();
            $_SESSION['fingerprint'] = $this->getSessionFingerprint();
        }
    }

    public function logout()
    {
        setcookie(session_name(), '', time() - 3600, Config::$base_path);
        session_destroy();
    }

    public function fromLdap()
    {
        return isset(Config::$ldap['hostname']);
    }

    protected function checkLocal($username, $password)
    {
        session_start();
        $username = $this->pdo->quote($username);
        $passwordHash = new PasswordHash();
        $password = $this->pdo->quote($passwordHash->HashPassword($password));
        $sql = 'SELECT 1 FROM `users` '
                . 'WHERE username=:username AND password=SHA1(:password) '
                . 'LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $matching_users = $stmt->rowCount();
        return ($matching_users > 0);
    }

    public function checkLdap($username, $password)
    {
        // Prevent binding with an empty password.
        if (empty($password)) {
            return false;
        }
        $config = Config::$ldap;
        $hostname = $config['hostname'];
        $conn = ldap_connect($hostname);
        if (!$conn) {
            throw new Exception("Unable to connect to LDAP server $hostname");
        }
        if (!empty($config['suffix'])) {
            $username = $username . $config['suffix'];
        }
        try {
            return ldap_bind($conn, $username, $password);
        } catch (Exception $e) {
            return false;
        }
    }

    public function loggedIn()
    {
        return $this->loggedIn;
    }

    public function getUsername()
    {
        return ($this->loggedIn) ? $_SESSION['username'] : '';
    }

    public function getGroups()
    {
        return array();
    }

}
