<?php

class ADBT_View_HTML extends ADBT_View_Base
{

    protected $mainMenu;
    public $messages;
    protected $scripts;

    /** @var string The title of the HTML page. */
    public $title;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->mainMenu = array(
            '/' => 'Home',
            '/database' => 'Database',
        );
        $this->scripts = array(
            'jquery-1.8.0.min.js',
            'jquery-ui-1.8.23.custom.min.js',
            'base.js'
        );
        $this->messages = array();
    }

    public function addMessage($message, $type = 'notice')
    {
        $this->messages[] = array(
            'type' => $type,
            'message' => $message,
        );
    }

    public function addDelayedMessage($message, $type = 'notice')
    {
        $_SESSION['delayedMessages'][] = array(
            'type' => $type,
            'message' => $message,
        );
    }

    public function getSelectElement($name, $options, $selected)
    {
        $out = "<select name='$name'>";
        foreach ($options as $value => $name) {
            $sel = ($value == $selected) ? ' selected' : '';
            $out .= "<option value='$value'$sel>$name</option>";
        }
        $out .= "</select>";
        return $out;
    }

    /**
     * The same as $this->output(), but returns the output instead of printing
     * it to STDOUT.
     * 
     * @return string The output.
     */
    public function getOutput() {
        ob_start();
        $this->output();
        $output = ob_get_clean();
        return $output;
    }

    public function output()
    {
        $header_classname = $this->app->getClassname('View_Header');
        $header = new $header_classname($this->app, $this->title);
        $header->user = $this->user;
        ?>
        <!DOCTYPE html>
        <html>
            <head><?php $this->outputHead() ?></head>
            <body class="<?php echo strtolower($this->controller_name . '-controller ' . $this->action_name . '-action') ?>">
                <div id="header"><?php $header->output() ?></div>
                <div id="menu"><?php $this->outputMenu() ?></div>
                <div id="content"><?php $this->outputContent() ?></div>
                <div id="footer"><?php $this->outputFooter() ?></div>
            </body>
        </html>
        <?php
    }

    public function outputHead()
    {
        ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta charset='utf-8'>
        <title><?php echo $this->title ?></title>
        <link rel="stylesheet" href="<?php echo $this->url('site/resources/css/jquery-ui.css') ?>" media="screen" />
        <link rel="stylesheet" href="<?php echo $this->url('site/resources/css/all.css') ?>" media="all" />
        <link rel="stylesheet" href="<?php echo $this->url('site/resources/css/screen.css') ?>" media="screen" />
        <link rel="stylesheet" href="<?php echo $this->url('site/resources/css/print.css') ?>" media="print" />
        <?php $this->outputScripts() ?>
        <?php
    }

    public function outputScripts()
    {
        foreach ($this->scripts as $script) {
            echo '<script type="text/javascript" src="' . $this->url('/site/resources/js/'.$script) . '"></script>'."\n";
        }
    }

    public function outputContent()
    {
        $this->outputMessages();
    }

    /**
     * Thanks to http://en.wikipedia.org/wiki/Template:Ambox
     */
    public function outputMessages()
    {
        if (isset($_SESSION['delayedMessages']) && is_array($_SESSION['delayedMessages'])) {
            $messages = array_merge($this->messages, $_SESSION['delayedMessages']);
        } else {
            $messages = $this->messages;
        }
        if (count($messages) > 0) {
            echo '<ul class="messages">';
            foreach ($messages as $message) {
                $type = $message['type'];
                $icon_url = $this->url("site/resources/img/icon_$type.png");
                echo "<li class='$type message' style='background-image:url(\"$icon_url\")'>";
                echo $message['message'];
                echo '</li>';
            }
            echo '</ul>';
        }
        $_SESSION['delayedMessages'] = array();
    }

    public function outputMenu()
    {
        
    }

    public function outputFooter()
    {
        ?>
        <p>
            Powered by <abbr title="A Database Thing">ADBT</abbr>.
            Please <a href="http://github.com/samwilson/adbt/issues/new"
                      title="Lodge a new bug report or feature request">report</a>
            any issues.
        </p>
        <?php
    }

}