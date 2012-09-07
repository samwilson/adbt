<?php

class ADBT_View_Database_Index extends ADBT_View_HTML {

    public function __construct()
    {
        parent::__construct();
    }
    public function output() {
        //$this->mainMenu['/database'] = ADBT_View_Base::titlecase($this->database->getName());
        $this->outputHeader('Database', '/database');
        echo '<ol class="">';
        foreach ($this->tables as $table) {
            echo '<li><a href="'.$this->url("database/index/$table").'">'
                .$this->titlecase($table)
                .'</a></li>';
        }
        echo '</ol>';
        $this->outputFooter();
    }

}