<?php

class ADBT_View_Site_Home extends ADBT_View_HTML {

    public function output() {
        echo $this->outputHeader('ADBT', '/');
        ?>

        <p>
            Welcome to
            <em><strong>A</strong> <strong>D</strong>ata<strong>B</strong>ase <strong>T</strong>hing</em>.
        </p>

        <?php
        echo $this->outputFooter();
    }

}