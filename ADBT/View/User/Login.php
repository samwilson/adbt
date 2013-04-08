<?php

class ADBT_View_User_Login extends ADBT_View_HTML {

    /** @var ADBT_Model_User */
    public $username;
    
    public function outputContent() {
        $this->outputMessages();
        ?>
<form action="" method="post" id="login">
    <table class="vertical">
        <tr>
            <th><label for="username">Username:</label></th>
            <td><input type="text" name="username" id="username" value="<?php echo $this->username ?>" /></td>
        </tr>
        <tr>
            <th><label for="password">Password:</label></th>
            <td><input type="password" name="password" id="password" /></td>
        </tr>
        <?php /* if ($this->useLdap): ?>
        <tr>
            <th><label for="domain">Domain:</label></th>
            <td>
                <select name="domain" id="domain">
                <?php foreach ($this->ldapDomains as $domain) {
                    echo "<option value='$domain'>$domain</option>";
                } ?>
                </select>
            </td>
        </tr>
        <?php endif // if ($this->useLdap) */ ?>
        <tr>
            <td></td>
            <td><input type="submit" value="Log in" /></td>
        </tr>
    </table>
</form>
        <?php
    }

}