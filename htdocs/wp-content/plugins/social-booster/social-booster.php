<?php
session_start();
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://rextheme.com/
 * @since             1.0.0
 * @package           Social_Booster
 *
 * @wordpress-plugin
 * Plugin Name:       Social Booster
 * Plugin URI:        http://rextheme.com/social-booster/
 * Description:       Post automatically from wordpress blog to some specific social media like facebook or twitter. It is easy and comfortable to use.
 * Version:           1.2.1
 * Author:            Rextheme
 * Author URI:        http://rextheme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       social-booster
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Currently pligin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SOCIAL_BOOSTER_VERSION', '1.0.0' );




/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-social-booster-activator.php
 */


require_once plugin_dir_path( __FILE__ ) . 'admin/class-social-booster-db-manager.php';
function activate_social_booster() {

	$dbcreate = new Social_Booster_Db_Manager();
	flush_rewrite_rules();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-social-booster-activator.php';
	Social_Booster_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-social-booster-deactivator.php
 */
function deactivate_social_booster() {
 	flush_rewrite_rules();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-social-booster-deactivator.php';
	Social_Booster_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_social_booster' );
register_deactivation_hook( __FILE__, 'deactivate_social_booster' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-social-booster.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_social_booster() {

	$plugin = new Social_Booster();
	$plugin->run();

}
run_social_booster();

require_once plugin_dir_path(__FILE__) . 'admin/metabox/metabox.php';
require_once plugin_dir_path(__FILE__) . 'admin/metabox/cmb2-conditionals-master/cmb2-conditionals.php';
require_once plugin_dir_path(__FILE__) . 'admin/metabox/init.php';	




function socialbooster_tweetstatusnow($message) {
            
    require_once plugin_dir_path(__FILE__) . 'admin/lib/codebird.php';  

    global $wpdb;
    $tabletw = $wpdb->prefix . 'socialbooster_twtable';
    $consumer_key = $wpdb->get_var("SELECT consumerkey FROM $tabletw ");
    $consumer_secret = $wpdb->get_var("SELECT consumersecret FROM $tabletw ");
    $access_token = $wpdb->get_var("SELECT accesstoken FROM $tabletw ");
    $access_token_secret = $wpdb->get_var("SELECT accsesstokensecret FROM $tabletw "); 
    \Codebird\Codebird::setConsumerKey( $consumer_key, $consumer_secret );
    $cb = \Codebird\Codebird::getInstance();

    $cb->setToken($access_token, $access_token_secret);
	$params = array(
    	'status' => $message
   
	);
	$reply = $cb->statuses_update($params);

}

function socialbooster_facebooknow($message,$image) {

    		require_once plugin_dir_path(__FILE__) . 'admin/lib/facebook.php';
    		global $wpdb;
            $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
            $appid = $wpdb->get_var("SELECT appid FROM $tablefb ");
            $app_secret = $wpdb->get_var("SELECT appsecret FROM $tablefb ");
            $pageid = $wpdb->get_var("SELECT username FROM $tablefb ");
            $access_token_secretfb = $wpdb->get_var("SELECT accsesstokensecret FROM $tablefb "); 
    		$config = array();
    		$config['appId'] = $appid;
    		$config['secret'] = $app_secret;
    		$config['fileUpload'] = true;
    		$fb = new Facebook($config);
    		wp_mail( 'me@example.net', 'test1', $config );
    		$params = array(

    		  "access_token" => $access_token_secretfb,
    		  "message" => $message,
    		  "link" => $image

    		);
            
    		try {
    		  $ret = $fb->api('/'.$pageid.'/feed', 'POST', $params);
    		} catch(Exception $e) {
    		  echo $e->getMessage();
    		}
            wp_mail( 'me@example.net', 'test2', $ret );
            wp_mail( 'me@example.net', 'test3', $e );
    	}

function socialbooster_facebooklinknow($image) {

    		require_once plugin_dir_path(__FILE__) . 'admin/lib/facebook.php';
    		global $wpdb;
            $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
            $appid = $wpdb->get_var("SELECT appid FROM $tablefb ");
            $app_secret = $wpdb->get_var("SELECT appsecret FROM $tablefb ");
            $pageid = $wpdb->get_var("SELECT username FROM $tablefb ");
            $access_token_secretfb = $wpdb->get_var("SELECT accsesstokensecret FROM $tablefb "); 
    		$config = array();
    		$config['appId'] = $appid;
    		$config['secret'] = $app_secret;
    		$config['fileUpload'] = true; 
    		$fb = new Facebook($config);
    		 
    		$params = array(

    		  "access_token" => $access_token_secretfb,
    		  "link" => $image

    		);
    		try {
    		  $ret = $fb->api('/'.$pageid.'/feed', 'POST', $params);
    		} catch(Exception $e) {
    		  echo $e->getMessage();
    		}
    	}
function social_booster_tumblrnow($status,$link) {
    require_once plugin_dir_path(__FILE__) . 'admin/partials/Tumblr/vendor/autoload.php';
    global $wpdb;
    $table = $wpdb->prefix . 'socialbooster_stmtable';
    $bgname = $wpdb->get_var("SELECT blogname FROM $table ");
    $tmkeyget = $wpdb->get_var("SELECT consumerkey FROM $table ");
    $tmsecretget = $wpdb->get_var("SELECT consumersecret FROM $table ");
    $token = $wpdb->get_var("SELECT token FROM $table ");
    $secret = $wpdb->get_var("SELECT tokensecret FROM $table ");
    $consumerKey = $tmkeyget;
    $consumerSecret = $tmsecretget;
    $client = new Tumblr\API\Client($consumerKey, $consumerSecret, $token, $secret);
    $postData = array('type'=>'link', 'title' => $status, 'url' => $link);
    $client->createPost($bgname, $postData);

}

function social_booster_tumblrnowlinkonly($link) {
    require_once plugin_dir_path(__FILE__) . 'admin/partials/Tumblr/vendor/autoload.php';
    global $wpdb;
    $table = $wpdb->prefix . 'socialbooster_stmtable';
    $bgname = $wpdb->get_var("SELECT blogname FROM $table ");
    $tmkeyget = $wpdb->get_var("SELECT consumerkey FROM $table ");
    $tmsecretget = $wpdb->get_var("SELECT consumersecret FROM $table ");
    $token = $wpdb->get_var("SELECT token FROM $table ");
    $secret = $wpdb->get_var("SELECT tokensecret FROM $table ");
    $consumerKey = $tmkeyget;
    $consumerSecret = $tmsecretget;
    $client = new Tumblr\API\Client($consumerKey, $consumerSecret, $token, $secret);
    $postData = array('type'=>'link', 'title' => "", 'url' => $link);
    $client->createPost($bgname, $postData);

}

function socialbooster_post_now() {
    global $wpdb;
    $tablepost = $wpdb->prefix . 'socialbooster_postnowtable';
    $dbstatusnow = $wpdb->get_var("SELECT status FROM $tablepost ");
    $dblinknow = $wpdb->get_var("SELECT link FROM $tablepost ");
    $dbstatusmetanow = $dbstatusnow . " " . $dblinknow;
    $schnow =  $wpdb->get_var("SELECT schno FROM $tablepost ");
    $fbnow = $wpdb->get_var("SELECT fb FROM $tablepost ");
    $twnow = $wpdb->get_var("SELECT tw FROM $tablepost ");
    $insnow = $wpdb->get_var("SELECT ins FROM $tablepost ");
    $tmnow = $wpdb->get_var("SELECT tm FROM $tablepost ");
    $insfeatured_img_urlnow = get_the_post_thumbnail_url($schnow,'full');

    if (!empty($fbnow)) {
        if ($dbstatusnow != "") {
            socialbooster_facebooknow($dbstatusnow,$dblinknow);
        }
        else {
            socialbooster_facebooklinknow($dblinknow);
        }
    }

    if (!empty($twnow)) {
        socialbooster_tweetstatusnow($dbstatusmetanow);
    }

    if (!empty($insnow)) {

        if (!empty($insfeatured_img_urlnow)) {

            if ($dbstatusnow != "") {

                social_booster_runinstagramnow($insfeatured_img_urlnow,$dbstatusnow);
            }
            else{

                social_booster_runinstagramnow($insfeatured_img_urlnow,"");
            }
        }
    }
    if (!empty($tmnow)) {
        
        if ($dbstatusnow != "") {
            social_booster_tumblrnow($dbstatusnow,$dblinknow);
        }
        else {
            social_booster_tumblrnowlinkonly($dblinknow);
        }
    }
    $wpdb->query("DELETE FROM $tablepost where 1=1 ");
}

add_action( 'save_post', 'socialbooster_post_now', 10, 3 );
add_action( 'admin_init', 'social_booster_admin_notice');
function social_booster_admin_notice() {
    $timeis = 0;
    $checkbox = get_option('social-boosterw');
    if (empty($checkbox)) {
        $timeis = time() + 31557600;
        add_option( 'social-boosterw', $timeis ); 
    }
    else {
        $timeis = time() + 31557600;
        $checkbox = get_option('social-boosterw');
        if ($checkbox <= time()) {
              add_action('wp_dashboard_setup', 'social_booster_custom_dashboard_widgets');

            function social_booster_custom_dashboard_widgets() {
            global $wp_meta_boxes;
            
            wp_add_dashboard_widget('custom_help_widget', 'Social Booster', 'social_booster_dashboard_help');

            }
             
            function social_booster_dashboard_help() {
            echo '<h2>Welcome to the Social Booster</h2><a href="https://wordpress.org/plugins/social-booster/#reviews">Rate this plugin</a><br><a href="mailto:sakib@coderex.co">Report! If you do not like this plugin</a>';
            } 
        }
    }
      
}


function social_booster_instagramnow($photo,$caption) {
    require_once plugin_dir_path(__FILE__) . 'admin/lib/autoload.php';
    require_once plugin_dir_path(__FILE__) . 'admin/lib/mgp25/instagram-php/src/Instagram.php';
    set_time_limit(0);
    date_default_timezone_set('UTC');

    /////// CONFIG ///////
    global $wpdb;
    $tableins = $wpdb->prefix . 'socialbooster_instable';
    $usernameinstagram = $wpdb->get_var("SELECT username FROM $tableins ");
    $passwordinstagram = $wpdb->get_var("SELECT password FROM $tableins ");
    $salt1 = str_replace("dj515656ksfj51sdj","",$passwordinstagram);
    $salt2 = str_replace("sd85fssf5656664f56sd5","",$salt1);
    $mainstr = trim($salt2);
    $username = $usernameinstagram;
    $password = $mainstr;
    $debug = false;
    $truncatedDebug = false;
    /////// MEDIA ////////
    $photoFilename = $photo;
    $captionText = $caption;
    //////////////////////

    $i = new \InstagramAPI\Instagram($username, $password, $debug);

    try {
        $i->login();
    } catch (Exception $e) {
        $e->getMessage();
        exit();
    }

    try {
        $i->uploadPhoto($photoFilename, $captionText);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

function social_booster_runinstagramnow($photo,$caption) {

    $resizephoto = social_booster_photo_resizenow($photo);
    social_booster_instagramnow($resizephoto,$caption);
    unlink($resizephoto);
    
}

function social_booster_photo_resizenow($photo) {

    require_once plugin_dir_path(__FILE__) . 'admin/lib/resize/src/Resize.php';
    $resize = new \MSC\Instaresize\Resize();
    $path = $resize->check($photo);
    return $path;
}

function social_booster_admin_notice__error() {
    global $wpdb;
    $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
    $appid = $wpdb->get_var("SELECT appid FROM $tablefb ");
    $access_token_secretfb = $wpdb->get_var("SELECT accsesstokensecret FROM $tablefb "); 
    if (!empty($appid)) {
        $url = 'https://graph.facebook.com/me/accounts?access_token='.$access_token_secretfb;
        $contextOptions = [
        'ssl' => [
                'verify_peer' => false,
                'allow_self_signed' => true
            ]
        ];
        $sslContext = stream_context_create($contextOptions);
        $json = file_get_contents_curl($url);
        $obj = json_decode($json,true);
        $expire_message = $obj['error']['message'];
        $expire_code = $obj['error']['code'];
        if ($expire_code != 100 && !empty($expire_message)) {
        ?>
            <p class="notice notice-warning"><?php echo __( 'Facebook Access token expired & '.$expire_message.'', 'sample-text-domain' ); ?></p>
        <?php    
        }
    }
}
add_action( 'admin_notices', 'social_booster_admin_notice__error' );

function file_get_contents_curl( $url ) {

    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );

    $data = curl_exec( $ch );
    curl_close( $ch );

    return $data;

}

function social_booster_check_https() {
    
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        
        return true; 
    }
    return false;
}