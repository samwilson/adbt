<?php

class ADBT_View_HTML extends ADBT_View_Base
{

    protected $mainMenu;

    public function __construct()
    {
        parent::__construct();
        $this->mainMenu = array(
            '/' => 'Home',
            '/database' => 'Database',
        );
    }

    public function output()
    {
        $this->outputHeader('ADBT');
        $this->outputFooter();
    }

    public function outputHeader($title, $current_url=false)
    {
        ?>

        <!DOCTYPE html>
        <html>
            <head>
                <meta charset='utf-8'>
                <title><?php echo $title ?></title>
                <link rel="stylesheet" href="<?php echo $this->url('/resources/css/base.css') ?>" />
                <?php if (method_exists($this, 'outputStyles')) { ?>
                <style type="text/stylesheet">
                    <?php echo $this->outputStyles() ?>
                </style>
                <?php } // if (method_exists($this, 'outputStyles')) ?>
            </head>
            <body>
                <div id="header">
                    <p class="user">
                        <?php if ($this->user->loggedIn()) { ?>
                        <a href="<?php echo $this->url('/user/account') ?>">Account</a>
                        <a href="<?php echo $this->url('/user/logout') ?>">Logout</a>
                        <?php } else { // if ($this->user->loggedIn())  ?>
                        <a href="<?php echo $this->url('/user/login') ?>">Login</a>
                        <?php } // if ($this->user->loggedIn())  ?>
                    </p>
                    <h1><?php echo $title ?></h1>
                    <ol class="mainmenu tabs">
                        <?php foreach ($this->mainMenu as $url=>$text) {
                        $class = ($current_url==$url) ? 'current' : '';
                        echo "<li><a href='".$this->url($url)."' class='$class'>$text</a></li>";
                        } ?>
                    </ol>
                </div>

        <?php
    }

    public function outputFooter()
    {
        ?>
                <div id="footer">
                    Thank you for using ADBT.
                    Please <a href="http://github.com/samwilson/adbt/issues/new"
                    title="Lodge a new bug report or feature request via Github.">report</a>
                    any bugs.
                </div>
            </body>
        </html>
        <?php
    }

}