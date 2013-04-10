<?php

class ADBT_Model_User extends ADBT_Model_Base
{

    /** @var boolean */
    protected $loggedIn = false;
    protected $username = false;
    protected $password = false;

    public function __construct($app)
    {
        parent::__construct($app);
        $timeout = 60 * 30; // In seconds, i.e. 30 minutes.
        $fingerprint = $this->getSessionFingerprint();
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        $timed_out = isset($_SESSION['last_active']) && ($_SESSION['last_active'] < (time() - $timeout));
        $wrong_fingerprint = (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] != $fingerprint);
        if ($timed_out || $wrong_fingerprint) {
            $this->logout();
        } else {
            session_regenerate_id();
            $_SESSION['last_active'] = time();
            $_SESSION['fingerprint'] = $fingerprint;
            if (isset($_SESSION['username'])) {
                $this->loggedIn = true;
                $this->username = $_SESSION['username'];
                $this->password = $_SESSION['password'];
                global $database_config;
                if (empty($database_config['username'])) {
                    $database_config['username'] = $this->username;
                    $database_config['password'] = $this->password;
                }
            }
        }
    }

    public function getExpiryTime() {
        return time() - $_SESSION['last_active'];
    }

    protected function getSessionFingerprint()
    {
        return md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    public function login($username, $password)
    {
        if ($this->useLdap()) {
            $this->loggedIn = $this->checkLdap($username, $password);
        } elseif ($this->useLocal()) {
            $this->loggedIn = $this->checkLocal($username, $password);
        } else {
            $this->loggedIn = $this->checkDB($username, $password);
        }
        if ($this->loggedIn()) {
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;
            $_SESSION['last_active'] = time();
            $_SESSION['fingerprint'] = $this->getSessionFingerprint();
        }
    }

    public function logout()
    {
        setcookie(session_name(), '', time() - 3600, BASE_URL);
        session_destroy();
    }

    public function useLdap()
    {
        global $ldap_config;
        return !empty($ldap_config['hostname']);
    }

    public function useLocal()
    {
        global $database_config;
        return !$this->useLdap() && !empty($database_config['username']);
    }

    public function useDB()
    {
        global $database_config;
        return empty($database_config['username']);
    }

    public function checkDB($username, $password) {
        global $database_config;
        try {
            $database_config['username'] = $username;
            $database_config['password'] = $password;
            $this->dbInit();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function checkLocal($username, $password)
    {
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
        global $ldap_config;
        $hostname = $ldap_config['hostname'];
        $conn = ldap_connect($hostname);
        if (!$conn) {
            throw new Exception("Unable to connect to LDAP server $hostname");
        }
        if (!empty($ldap_config['suffix'])) {
            $username = $username . $ldap_config['suffix'];
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

    public function getPassword()
    {
        return ($this->loggedIn) ? $_SESSION['password'] : '';
    }

    public function getGroups()
    {
        return array();
    }

}
