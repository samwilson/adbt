<?php

class ADBT_View_Errors_General {

    public function output() {
        echo "ERROR: $this->code $this->message";
    }

}
