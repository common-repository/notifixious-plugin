<?php
/*
    Plugin Name: Notifixio.us
    Plugin URI: http://wordpress.org/extend/plugins/notifixious-plugin/
    Description: This plugin allows you to send notifications to your followers on Notifixious through the channels they use (Email, SMS, Instant Messaging : MSN, AIM, GTalk, Yahoo! Messenger...). Once activated, please <a href="/wp-admin/options-general.php?page=Options%20Notifixious"> enter your Notifixious credentials</a>. Finally : don't forget to <a href="/wp-admin/widgets.php">use our Widget</a> to allow your readers to subscribe to this blog!
    Version: 0.8
    Author: Mathieu BUONOMO
    Author URI: http://www.mathieubuonomo.com
*/
?>
<?php
/*  
    Copyright 2008  Mathieu BUONOMO / Notifixious  (email : http://www.notifixio.us)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */

/* Generic stuff */
/* Defining the WP_CONTENT_URL */
if ( !defined('WP_CONTENT_URL') ) {
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}
if ( !defined('PLUGINDIR') ) {
    define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
}

if(!defined('NOTIFIXIOUS_URL')) {
    define( 'NOTIFIXIOUS_URL', 'notifixio.us' ); 
}

/* Plugin base name */
function notifixious_plugin_basename($file) {
    $file = dirname($file);
    // From WP2.5 wp-includes/plugin.php:plugin_basename()
    $file = str_replace('\\','/',$file); // sanitize for Win32 installs
    $file = preg_replace('|/+|','/', $file); // remove any duplicate slash
    $file = preg_replace('|^.*/' . PLUGINDIR . '/|','',$file); // get relative path from plugins dir
    if ( strstr($file, '/') === false ) {
        return $file;
    }
    $pieces = explode('/', $file);
    return !empty($pieces[count($pieces)-1]) ? $pieces[count($pieces)-1] : $pieces[count($pieces)-2];
}

/* Pluin URL to get the images!*/
define('NOTIFIXIOUS_PLUGIN_URL', WP_CONTENT_URL . '/plugins/' . notifixious_plugin_basename(__FILE__));
/* End of generic stuff */

/* Init the widget */
function init_notifixious_widget() 
{
    register_sidebar_widget(array('Notifixious Widget', 'widgets'), 'notifixious_widget');
    register_widget_control(array('Notifixious Widget', 'widgets'), 'notifixious_widget_control', 300, 600);
}

/* shows the widget*/
function notifixious_widget($args) 
{
    extract( $args );
    $source_id = get_option('notifixiousSourceId');
    $widget_title = get_option('notifixiousWidgetTitle');
    echo $before_widget;
    echo $before_title.$widget_title.$after_title;
    echo "<script src=\"https://s3.amazonaws.com/notifixious_assets/notifixious_widget.js\"></script>";
    echo $after_widget;
}

/* Shows the widget administration */
function notifixious_widget_control() 
{
    $source_id = get_option('notifixiousSourceId');
    $notifixiousRegistered = get_option('notifixiousRegistered');
    
    if(isset($_POST['notifix_widget_submit']))
    {
        $widget_title = strip_tags(stripslashes($_POST['notifix_widget_title']));
        update_option('notifixiousWidgetTitle',$widget_title, '', 'no');
    }
    else
    {
        $widget_title = get_option('notifixiousWidgetTitle');
        ?>
        <p>
            <label for="notifix_widget_title">Widget Title:</label>
            <input type="text" id="notifix_widget_title" name="notifix_widget_title" value="<?php echo htmlspecialchars($widget_title, ENT_QUOTES)?>" />
            <input type="hidden" id="notifix_widget_submit" name="notifix_widget_submit" value="1" />
        </p>
        <?php
    }
}

/* This function adds a header tag for the claim */
function add_notifixious_header_tag()
{
    $notifixiousTagName = get_option('notifixiousTagName');
    $notifixiousTagContent = get_option('notifixiousTagContent');
    if($notifixiousTagName!="" && $notifixiousTagContent!="")
    {
        echo '<meta name="'.$notifixiousTagName.'" content="'.$notifixiousTagContent.'" />';
    }
}

/* Displays the menu */ 
function notifixious_menu()
{
    global $wpdb;
    include 'notifixious_admin.php';
}

/* Display the actions */
function notifixious_admin_actions()
{
    add_options_page("Notifixio.us", "Notifixio.us", 1,"Options Notifixious", 'notifixious_menu');
}

/* When installing plugin */
function notifixious_install(){
    # Adding options in the database!
    add_option('notifixiousClaimed','0', '', 'yes');
    add_option('notifixiousRegistered','0', '', 'yes');
    add_option('notifixiousLoginValid','0', '', 'yes');
    add_option('notifixiousPassword','0', '', 'yes');
    add_option('notifixiousLogin','0', '', 'yes');
    add_option('notifixiousSourceId','0', '', 'yes');
    add_option('notifixiousWidgetTitle','Notification', '', 'yes');    
    add_option('notifixiousTagContent','', '', 'yes');
    add_option('notifixiousTagName','', '', 'yes');
    register_blog_on_notifixious();
}

/* When installing plugin */
function notifixious_uninstall(){
    # Adding options in the database!
    delete_option('notifixiousClaimed');
    delete_option('notifixiousRegistered');
    delete_option('notifixiousLoginValid');
    delete_option('notifixiousPassword');
    delete_option('notifixiousLogin');
    delete_option('notifixiousSourceId');
    delete_option('notifixiousWidgetTitle');
    delete_option('notifixiousTagContent');
    delete_option('notifixiousTagName');
}

/* Function that performs the call to the Notifixious API. Returns some JSON */
function notifixious_http_request($link, $method)
{
    $url_parts = @parse_url( $link );
    $host = $url_parts["host"];
    $path = $url_parts["path"];
    if($url_parts["query"])
    {
        $query = $url_parts["query"];
        $http_request  = "$method $path?$query HTTP/1.0\r\n";
    }
    else
    {
        $http_request  = "$method $path HTTP/1.0\r\n";
    }
    if($url_parts["user"] && $url_parts["pass"])
    {
        $user = $url_parts["user"];
        $pass = $url_parts["pass"];
        $auth = $user.":".$pass ; 
        $encoded_auth = base64_encode($auth);
        $http_request .= "Authorization: Basic ".$encoded_auth."\r\n";
    }
    $port = 80;
    $http_request .= "Host: $host\r\n";        	
    $http_request .= "User-Agent: WordPress \r\n";
    $http_request .= "\r\n";
    $response = '';
    if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
        fwrite($fs, $http_request);
        while ( !feof($fs) )
            $response .= fgets($fs); // One TCP-IP packet
        fclose($fs);
    }
    $response = explode("\r\n\r\n", $response, 2);
    return $response;
}

/* This method sends a new notifix to Notifixious when a new blog post is published */
function send_new_notifix_to_notifixious($id_post)
{
    $pass = get_option('notifixiouspassword');
    $login = get_option('notifixiouslogin');
    $source_id = get_option('notifixiousSourceId');
    $post = get_post($id_post);
    $title = urlencode($post->post_title);
    $text = urlencode($post->post_content);
    $link = urlencode($post->guid);
    $url = "http://".urlencode($login).":".urlencode($pass)."@".NOTIFIXIOUS_URL."/sources/".$source_id."/events.json?"."event[title]=".$title."&event[text]=".$text."&event[link]=".$link;
    $result = notifixious_http_request($url, "POST");
    /* We should check the results here... later! */
}

/*  This method registers the blog on Notifixious
    In case of success, the SourceId is saved, and the Claim is set to false
    In case of failure, the SourceId stays at 0, and Claim is set to false as well */
function register_blog_on_notifixious()
{
    $url = "http://".NOTIFIXIOUS_URL."/sources/find.json";
    $params = "url=".urlencode(get_bloginfo('rss2_url'));
    $results = notifixious_http_request($url."?".$params, "GET");
    $jsonobj = json_decode($results[1]);
    $source_id = $jsonobj->sources->source->permalink;
    if($source_id != "")
    {
        update_option('notifixiousSourceId',''.$source_id.'', '', 'no');
        update_option('notifixiousRegistered','1', '', 'no');
        update_option('notifixiousClaimed','0', '', 'yes');
        return true;
    }
    else
    {
        update_option('notifixiousSourceId','0', '', 'no');
        update_option('notifixiousRegistered','0', '', 'no');
        return false;
    }
}

/*  This methods claims the blog on notifixious. For now, the method used is a file, which will change very soon. */
function claim_blog_on_notifixious()
{
    $login = get_option('notifixiousLogin');
    $password = get_option('notifixiousPassword');
    $source_id = get_option('notifixiousSourceId');
    $url = "http://".urlencode($login).":".urlencode($password)."@".NOTIFIXIOUS_URL."/sources/".$source_id."/ownerships/new.json";
    $results = notifixious_http_request($url, "GET");
    $jsonobj = json_decode($results[1]);
    
    $methods =  $jsonobj->ownership->claim_method;
    
    foreach($methods as $method)
    {
        if($method->name=="tag")
        {
            $tagname = $method->description->tag_name;
            $tagcontent = $method->description->tag_content;
        }
    }
    
    update_option('notifixiousTagName', ''.$tagname.'', '', '0');
    update_option('notifixiousTagContent', ''.$tagcontent.'', '', '0');
    $url =  "http://".urlencode($login).":".urlencode($password)."@".NOTIFIXIOUS_URL."/sources/".$source_id."/ownerships.json";
    $params = "method=tag";
    $results = notifixious_http_request($url."?".$params, "POST");
    $jsonobj = json_decode($results[1]);
    $ownership_source_id = $jsonobj->ownership->source->permalink;
    if($jsonobj->ownership->id != "" && $ownership_source_id == $source_id)
    {
        update_option('notifixiousClaimed','1', '', 'yes');
        return true;
    }
    else
    {
        update_option('notifixiousClaimed','0', '', 'yes');
        return false;
    }
}

/*  This method checks a login and a user name
    In case of success, the notifixiousLoginValid is set to true, else it stays false */
function check_login_on_notifixious($login = "", $password = "")
{
    $url = "http://".NOTIFIXIOUS_URL."/session.json";
    $params = "login=".urlencode($login)."&password=".urlencode($password);
    $results = notifixious_http_request($url."?".$params, "POST");
    # Good, let's check that the login is the same as the one provided ;-)
    $jsonobj = json_decode($results[1]);
    if($jsonobj->session->login == $login)
    {
        update_option('notifixiousLoginValid','1', '', 'yes');
        return true;
    }
    else
    {
        update_option('notifixiousLoginValid','0', '', 'yes');
        return false;
    }
}

/* Loading JSON */
if ( !function_exists('json_decode') )
{
    function json_decode($content, $assoc=false) {
        require_once 'JSON.php';
        if ( $assoc ){
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

// Register for activation
register_activation_hook( __FILE__, 'notifixious_install');
register_deactivation_hook( __FILE__, 'notifixious_uninstall');
add_action('admin_menu', 'notifixious_admin_actions');
add_action('publish_post', 'send_new_notifix_to_notifixious');
add_action('wp_head', 'add_notifixious_header_tag');
add_action('plugins_loaded', 'init_notifixious_widget');

?>