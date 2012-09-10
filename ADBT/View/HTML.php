<?php

class ADBT_View_HTML extends ADBT_View_Base
{

    protected $mainMenu;

    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->mainMenu = array(
            '/' => 'Home',
            '/database' => 'Database',
        );
        $this->messages = array();
    }

    public function addMessage($message, $type = 'notice') {
        $this->messages[] = array(
            'type' => $type,
            'message' => $message,
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
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta http-equiv="Content-Script-Type" content="text/javascript" />
                <meta charset='utf-8'>
                <title><?php echo $title ?></title>
                <link rel="stylesheet" href="<?php echo $this->url('/resources/css/jquery-ui-1.8.23.custom.css') ?>" />
                <link rel="stylesheet" href="<?php echo $this->url('/resources/css/base.css') ?>" />
            </head>
            <body>
                <div id="header">
                    <p class="user">
                        <?php if ($this->user->loggedIn()) { ?>
                        You are logged in as <?php echo $this->user->getUsername() ?>.
                        <a href="<?php echo $this->url('/user/logout') ?>">Logout</a>.
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
                <?php echo $this->outputMessages() ?>

        <?php
    }

    public function outputMessage($message, $type)
    {
        echo "<div class='message $type'>";
        echo $message;
        echo '</div>';
    }

    /**
     * Thanks to http://en.wikipedia.org/wiki/Template:Ambox
     */
    public function outputMessages() {
        if (count($this->messages) > 0) {
            echo '<ul class="messages">';
            foreach ($this->messages as $message) {
                $type = $message['type'];
                $icon_url = $this->url("resources/img/icon_$type.png");
                echo "<li class='$type message' style='background-image:url('$icon_url')'>";
                echo $message['message'];
                echo '</li>';
            }
            echo '</ul>';
        }
    }

    public function outputFooter()
    {
        ?>
                <div id="footer">
                    Powered by <abbr title="A Database Thing">ADBT</abbr>.
                    Please <a href="http://github.com/samwilson/adbt/issues/new"
                    title="Lodge a new bug report or feature request">report</a>
                    any issues.
                </div>
                <script type="text/javascript" src="<?php echo $this->url('/resources/js/jquery-1.8.0.min.js') ?>"></script>
                <script type="text/javascript" src="<?php echo $this->url('/resources/js/jquery-ui-1.8.23.custom.min.js') ?>"></script>
                <script type="text/javascript" src="<?php echo $this->url('/resources/js/base.js') ?>"></script>
            </body>
        </html>
        <?php
    }

}