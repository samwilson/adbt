<?php

class ADBT_View_Database_Autocomplete
{

    public function output()
    {
        header('Content-type:text/plain; charset=UTF-8');
        echo json_encode($this->data);
    }
}
