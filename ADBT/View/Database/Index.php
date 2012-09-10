<?php

class ADBT_View_Database_Index extends ADBT_View_HTML {

    /** @var ADBT_Model_Table */
    public $table;

    public function __construct() {
        parent::__construct();
    }

    public function output() {
        $this->outputHeader('Database', '/database');
        echo '<div id="content" class="leftmenu">';
        echo '<div class="menu">';
        echo '<ol class="sidebar">';
        foreach ($this->tables as $table) {
            echo '<li><a href="' . $this->url('database/index/' . $table->getName()) . '">'
            . $this->titlecase($table->getName())
            . '</a></li>';
        }
        echo '</ol>';
        echo '</div><!-- .menu -->';
        echo '<div class="content">';
        if (!$this->table) { ?>
        <p class="message">Please select from the list at left.</p>
        
        <?php } else { ?>
            <h2><?php echo $this->titlecase($this->table->getName()) ?></h2>
            <?php $this->tableView->output() ?>
        <?php } // if (!$this->table) ?>
        </div><!-- .content -->
        </div><!-- #content -->
        <?php
        $this->outputFooter();
    }

}