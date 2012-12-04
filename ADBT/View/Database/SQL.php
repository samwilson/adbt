<?php

class ADBT_View_Database_SQL extends ADBT_View_HTML
{

    /** @var string The SQL statement to be formatted. */
    protected $sql;

    public function __construct($sql)
    {
        parent::__construct();
        $this->sql = $sql;
    }

    public function output()
    {
        echo '<pre>';
        $sql = preg_replace('/(FROM|WHERE|JOIN|ORDER|LIMIT)/', "\n$1", $this->sql);
        $sql = preg_replace('/,[`]/', ",\n       `", $sql);
        echo $sql;
        echo '</pre>';
    }

}