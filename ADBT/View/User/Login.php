<?php

class ADBT_View_User_Login extends ADBT_View_HTML {

    public function output() {
        $this->outputHeader('Log in');
        ?>
<form action="" method="post" id="login">
    <table>
        <tr>
            <th><label for="email_address">Email Address:</label></th>
            <td><input type="text" name="email_address" id="email_address" /></td>
        </tr>
        <tr>
            <th><label for="password">Password:</label></th>
            <td><input type="password" name="password" id="password" /></td>
        </tr>
        <?php if ($this->ldapDomains) { ?>
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
        <?php } // if ($this->useLdap) ?>
        <tr>
            <td></td>
            <td><input type="submit" value="Log in" /></td>
        </tr>
    </table>
</form>
        <?php
        $this->outputFooter();
    }

}