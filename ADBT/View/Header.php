<?php

class ADBT_View_Header extends ADBT_View_Base
{

    /** @var string The title. */
    protected $title;

    public function __construct($app, $title)
    {
        parent::__construct($app);
        $this->title = $title;
    }

    public function output()
    {
        ?>
        <p class="user">
        <?php if ($this->user->loggedIn()) { ?>
                You are logged in as <?php echo $this->user->getUsername() ?>.
                <a href="<?php echo $this->url('/user/logout') ?>">Logout</a>.
            <?php } else { // if ($this->user->loggedIn())    ?>
                <a href="<?php echo $this->url('/user/login') ?>">Login</a>
        <?php } // if ($this->user->loggedIn())    ?>
        </p>
        <h1>
            <a href="<?php echo $this->url('/') ?>" title="Go to site homepage"><?php echo SITE_TITLE ?></a>
            :: <?php echo $this->title ?>
        </h1>
        <!--ol class="mainmenu tabs">
        <?php /* foreach ($this->mainMenu as $url=>$text) {
          $class = ($current_url==$url) ? 'current' : '';
          echo "<li><a href='".$this->url($url)."' class='$class'>$text</a></li>";
          } */ ?>
        </ol-->
        <?php
    }

}