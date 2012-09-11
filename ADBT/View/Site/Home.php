<?php

class ADBT_View_Site_Home extends ADBT_View_HTML
{

    public function outputContent()
    {
        parent::outputContent();
        ?>

        <p>
            Welcome to
            <em><strong>A</strong> <strong>D</strong>ata<strong>B</strong>ase <strong>T</strong>hing</em>.
        </p>
        <p>
            <a href="<?php echo $this->url('/database') ?>">Database</a>
        </p>

        <?php

    }

}