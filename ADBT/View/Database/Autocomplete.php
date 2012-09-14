<?php

class ADBT_View_Database_Autocomplete
{

    public function output()
    {
        header("Content-Type:text/plain");
        echo json_encode($this->data);
    }
}