<?php
/* Main Code!  */
$id = get_option('notifixiousSourceId');
$claim = 0; #get_option('notifixiousClaim');
$login = get_option('notifixiousLogin');
$password = get_option('notifixiousPassword');
$notifixiousLoginValid = get_option('notifixiousLoginValid');
$notifixiousRegistered = get_option('notifixiousRegistered');
$notifixiousClaimed = get_option('notifixiousClaimed');

if(!$notifixiousLoginValid  && $login != "" && $password != "")
{
    /* Step 0 : check login */
    $notifixiousLoginValid = check_login_on_notifixious($login, $password);
    if($notifixiousLoginValid)
    {
        if(!$notifixiousRegistered)
        {
            /* Step 1 : Register blog*/
            $notifixiousRegistered = register_blog_on_notifixious();
        }
        if($notifixiousRegistered && !$notifixiousClaimed)
        {
            /* Step 2: Claim Blog */
            $notifixiousClaimed = claim_blog_on_notifixious();
        }
    }
}

if($_GET["register_blog"] && !$notifixiousRegistered)
{
    $notifixiousRegistered = register_blog_on_notifixious();
    if($notifixiousRegistered)
    {
        $notifixiousClaimed = claim_blog_on_notifixious();        
    }
}

if($_GET["claim_blog"] && !$notifixiousClaimed)
{
    $notifixiousClaimed = claim_blog_on_notifixious();
}

?>
<div class="wrap">
    <h2><?php _e('Notifixious Settings') ?></h2>
    <?php if(!$notifixiousLoginValid) ?>
    <p>Please enter your Notifixious login information. You can <a href="http://notifixio.us/users/new">sign-up here</a>.</p>
        <form method="post" action="options.php">
            <?php wp_nonce_field('update-options'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="notifixiouslogin">Login</label></th>
                    <td><input type="text" name="notifixiousLogin" id="notifixiousLogin" value="<?php echo $login; ?>" /></td>
                </tr>
                <tr>
                    <th><label for="notifixiouspassword">Password</label></th>
                    <td>
                        <input type="password" name="notifixiousPassword" id="notifixiousPassword" value="<?php echo $password; ?>" />
                        <div>
                            <?php
                        if($notifixiousLoginValid)
                        {
                            ?>
                            <img src="<?php echo NOTIFIXIOUS_PLUGIN_URL; ?>/accept.png" style="margin:3px; float:left;"> Login checked and valid.
                            <?php 
                                if($notifixiousRegistered && $notifixiousClaimed)
                                {
                            ?>
                                Each time you will publish a new post, a notification will be sent to your readers!
                            <?php
                                }
                            ?>
                            <?php
                        }
                        elseif($login!="" && $login!="")
                        {
                            ?>
                            <img src="<?php echo NOTIFIXIOUS_PLUGIN_URL; ?>/delete.png" style="margin:3px; float:left;"> Wrong login or password. <a href= "http://notifixio.us/forgot_password">Forgot Password?</a>
                            <?php
                        }
                        else
                        {
                            ?>
                            <a href= "http://notifixio.us/forgot_password">Forgot Password?</a>
                            <?php
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="notifixiousLoginValid" value="0" />
        <input type="hidden" name="page_options" value="notifixiousLogin,notifixiousPassword,notifixiousLoginValid" />
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    <?php if($notifixiousLoginValid)
        { 
    ?>
    <table class="form-table">
        <?php if(!$notifixiousRegistered) 
            {
                // Let's offer to the user to register his blog on Notifixious!
        ?>
        <tr>
            <th><label>Generate Source</label></th>
            <td><img src="<?php echo NOTIFIXIOUS_PLUGIN_URL; ?>/delete.png" style="margin:3px; float:left;">Your blog hasn't been registered with Notifixious. <a href="<?php echo WP_CONTENT_URL; ?>/../wp-admin/options-general.php?page=Options%20Notifixious&register_blog=true">Register now!</a><p style="margin: 5px 10px;">You need to add your blog to the list of available sources on Notifixious to be able to send Notifications when you publish a new post.</p></td>
        </tr>
        <?php
            }
            else
            {
                if(!$notifixiousClaimed)
                {
                    // Let's offer the user to claim his blog!
                    ?>
                    <tr>
                        <th><label>Claim Source</label></th>
                        <td>
                            <img src="<?php echo NOTIFIXIOUS_PLUGIN_URL; ?>/delete.png" style="margin:3px; float:left;">This blog has not been claimed. <a href="<?php echo WP_CONTENT_URL; ?>/../wp-admin/options-general.php?page=Options%20Notifixious&claim_blog=true">Claim it now!</a> <p style="margin: 5px 10px;">Once your blog has been registered, we need to check that you actually own it. Once this is done, you can publish new post that will be notified to all your readers!</p>
                        </td>
                    </tr>
                    <?php
                }
            }
        ?>
        <tr><th><label>Widget</label></th><td>Don't forget to <a href="<?php echo WP_CONTENT_URL; ?>/../wp-admin/widgets.php">install our widget</a> as well to allow your readers to choose the channels on which they want to be notified!</a>
        </td></tr>
    </table>
    <?php 
        } 
    ?>
</div>