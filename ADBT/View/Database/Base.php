<?php

class ADBT_View_Database_Base extends ADBT_View_HTML
{

    public function outputContent()
    {
        parent::outputContent();
        if (!$this->table)
            return;
        echo '<h2>' . $this->titlecase($this->table->getName()) . '</h2>';
        if ($this->table->getComment()) {
            echo '<p>' . $this->table->getComment() . '</p>';
        }
        echo '<ol class="tabs">';
        foreach ($this->tabs as $action=>$title) {
            $class = ($this->action_name==$action) ? 'current' : '';
            echo "<li><a href='".$this->url("database/$action/".$this->table->getName())."' class='$class'>$title</a></li>";
        }
        echo '</ol>';
    }

    public function outputMenu()
    {
        parent::outputMenu();
        echo '<ol>';
        foreach ($this->tables as $table) {
            $class = ($this->table && $this->table->getName() == $table->getName()) ? 'selected' : '';
            echo '<li><a class="' . $class . '" href="' . $this->url('database/index/' . $table->getName()) . '">'
            . $this->titlecase($table->getName())
            . '</a></li>';
        }
        echo '</ol>';
    }

}