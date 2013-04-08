<?php

class ADBT_View_Site_Home extends ADBT_View_HTML
{

    public function outputContent()
    {
        parent::outputContent();
        ?>

        <h2>
            Welcome to
            <em><strong>A</strong> <strong>D</strong>ata<strong>B</strong>ase <strong>T</strong>hing</em>.
        </h2>
        <p>
            Proceed to the <a href="<?php echo $this->url('/database') ?>">database</a>.
        </p>

        <?php

    }

}