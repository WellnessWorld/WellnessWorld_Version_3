<?php

/**
 * The admin-specific Posting process to different social media.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin
 */

require_once plugin_dir_path(__FILE__) . 'class-social-booster-cron.php';
class Social_Booster_Autopost {

		public $socialbooster_schedule;

		function __construct() {

            $this->socialbooster_schedule = new Social_Booster_Cron();

        }

        function socialbooster_add_cron( $next_run, $schedule, $hookname, $args ) {
            wp_schedule_event( $next_run, $schedule, $hookname, $args );
        }

        function socialbooster_action_php_cron_event($args){

            global $wpdb;
            $tablestatus = $wpdb->prefix . 'socialbooster_statustable';
            $dbstatus = $wpdb->get_var("SELECT status FROM $tablestatus WHERE schno={$args} ");
            $dblink = $wpdb->get_var("SELECT link FROM $tablestatus WHERE schno={$args} ");
            $dbstatusmeta = $dbstatus . " " . $dblink;
            $fb = $wpdb->get_var("SELECT fb FROM $tablestatus WHERE schno={$args} ");
            $tw = $wpdb->get_var("SELECT tw FROM $tablestatus WHERE schno={$args} ");
            $ins = $wpdb->get_var("SELECT ins FROM $tablestatus WHERE schno={$args}");
            $tm = $wpdb->get_var("SELECT tm FROM $tablestatus WHERE schno={$args}");
            $insfeatured_img_url = get_the_post_thumbnail_url($args,'full');
            if (!empty($fb)) {
                if ($dbstatus != "") {
                    $this->socialbooster_facebook($dbstatus,$dblink);
                }
                else {
                    $this->socialbooster_facebooklink($dblink);
                }
            }

            if (!empty($tw)) {
                $this->socialbooster_tweetstatus($dbstatusmeta);
            }

            if (!empty($tm)) {
                if ($dbstatus != "") {
                    $this->social_booster_tumblr($dbstatus,$dblink);
                }
                else {
                    $this->social_booster_tumblrlinkonly($dblink);
                }
            }
        }

        function socialbooster_post_info() {

    
            check_ajax_referer( 'social_booster', 'security' );

            $captionsanitize = $_POST['post'];
            $captionsanitizes = sanitize_text_field( $captionsanitize );
            $schnosanitize = $_POST['schno'];
            $schnosanitizes = sanitize_text_field( $schnosanitize );
            $socialsanitize = $_POST['social'];
            $socialsanitizes = sanitize_text_field( $socialsanitize );
            $timingsanitize = $_POST['times'];
            $timingsanitizes = sanitize_text_field( $timingsanitize );
            $urlsanitize = $_POST['postid'];
            $urlsanitizes = sanitize_text_field( $urlsanitize );
            $url_test = get_permalink($urlsanitizes);
            $postnowsanitize = $_POST['postnow'];
            $postnowsanitizes = sanitize_text_field( $postnowsanitize );
            $imagesanitize = $_POST['imgpath'];
            $imagesanitizes = sanitize_text_field( $imagesanitize );
            $imgsrc = $imagesanitizes;
            $caption = $captionsanitizes;
            $schno = $schnosanitizes;
            $social = $socialsanitizes;
            $timing = $timingsanitizes;
            $url = $url_test;
            $dbstatusmeta = $caption . " " . $url;
            $postnow = $postnowsanitizes;

            $twmeta = 0;
            $fbmeta = 0;
            $insmeta = 0;
            $tmmeta = 0;
            $socialchecks = explode('&', $social);
            $socialcheckssize = sizeof($socialchecks);
            foreach ($socialchecks as $socialcheck) {
                $checkval = explode('=', $socialcheck);
                if ($checkval[1] == "check1") {
                    $twmeta = 1;
                }
                elseif ($checkval[1] == "check2") {
                    $fbmeta = 1;
                }
                elseif ($checkval[1] == "check3") {
                    $insmeta = 1;
                }
                elseif ($checkval[1] == "check4") {
                    $tmmeta = 1;
                }
                
            }

            if ($postnow=='on') {
                global $wpdb;
                $tablepost = $wpdb->prefix . 'socialbooster_postnowtable';
                $wpdb->insert(
                $tablepost,
                    array(

                        'status' => $caption,
                        'link' => $url,
                        'image' => $imgsrc,
                        'schno' => $schno,
                        'tw' => $twmeta,
                        'fb' => $fbmeta,
                        'ins' => $insmeta,
                        'tm' => $tmmeta
                    )
                );
            }

            if ($timing=='yearly') {

                $dbinterval = 31557600;
            }
            elseif ($timing=='monthly') {
                $dbinterval = 2628000;
            }
            elseif ($timing=='weekly') {
                $dbinterval = 604900;
            }
            else {
                $dbinterval = 0;
            }

            $name = 'socialbooster_'.$dbinterval;
            $interval = $dbinterval;
            $display = 'Every '.$dbinterval.' Seconds';

            if ($dbinterval!=0) {

                global $wpdb;
                $tablestatus = $wpdb->prefix . 'socialbooster_statustable';
                $prepared_schno = $wpdb->get_var( "SELECT schno FROM $tablestatus WHERE  schno = $schno ");
                if (!empty($prepared_schno)) {
                    $wpdb->update($tablestatus, array('status'=>$caption, 'link'=>$url, 'interv'=>$dbinterval, 'tw' =>$twmeta, 'fb'=> $fbmeta, 'ins'=> $insmeta, 'tm'=> $tmmeta), array('schno'=>$prepared_schno));
                }
                else
                    {
                        $wpdb->insert(
                        $tablestatus,
                        array(
                            'status' => $caption,
                            'link' => $url,
                            'image' => $imgsrc,
                            'schno' => $schno,
                            'interv' => $dbinterval,
                            'tw' => $twmeta,
                            'fb' => $fbmeta,
                            'ins' => $insmeta,
                            'tm' => $tmmeta,
                        )
                    );
                if ($wpdb->last_error !== '') {
                  
                    _e( '<h1 class="label_design">Data Already Included. Change Event ID to Save Updated Post or Delete it from Schedule Page.</h1>', 'social-booster' );
                }
                else {
                    $this->socialbooster_schedule->socialbooster_add_schedule($name, $interval, $display);
                    $this->socialbooster_add_cron(time(), 'socialbooster_'.$interval, 'crontrol_cron_job',  array('schno' => $schno));

                    }
                }
            }
            exit();
        }//end

        function socialbooster_tweet($message,$image) {
            require_once plugin_dir_path(__FILE__) . '/lib/codebird.php';  
            global $wpdb;
            $tabletw = $wpdb->prefix . 'socialbooster_twtable';
            $consumer_key = $wpdb->get_var("SELECT consumerkey FROM $tabletw ");
            $consumer_secret = $wpdb->get_var("SELECT consumersecret FROM $tabletw ");
            $access_token = $wpdb->get_var("SELECT accesstoken FROM $tabletw ");
            $access_token_secret = $wpdb->get_var("SELECT accsesstokensecret FROM $tabletw "); 
            \Codebird\Codebird::setConsumerKey( $consumer_key, $consumer_secret );
            $cb = \Codebird\Codebird::getInstance();
            $cb->setToken($access_token, $access_token_secret);
            $reply = $cb->media_upload(array(
                'media' => $image
            ));
            $mediaID = $reply->media_id_string;
            $params = array(
                'status' => $message,
                'media_ids' => $mediaID
            );

            $reply = $cb->statuses_update($params);
          
        } 

        function socialbooster_tweetstatus($message) {
            
            require_once plugin_dir_path(__FILE__) . '/lib/codebird.php';  

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

    	function socialbooster_tweetlink($image) {
            require_once plugin_dir_path(__FILE__) . '/lib/codebird.php';  
            global $wpdb;
            $tabletw = $wpdb->prefix . 'socialbooster_twtable';
            $consumer_key = $wpdb->get_var("SELECT consumerkey FROM $tabletw ");
            $consumer_secret = $wpdb->get_var("SELECT consumersecret FROM $tabletw ");
            $access_token = $wpdb->get_var("SELECT accesstoken FROM $tabletw ");
            $access_token_secret = $wpdb->get_var("SELECT accsesstokensecret FROM $tabletw "); 
            \Codebird\Codebird::setConsumerKey( $consumer_key, $consumer_secret );
            $cb = \Codebird\Codebird::getInstance();
            $cb->setToken($access_token, $access_token_secret);
            $reply = $cb->media_upload(array(
                'media' => $image
            ));

            $mediaID = $reply->media_id_string;
            $params = array(

                'media_ids' => $mediaID
            );

            $reply = $cb->statuses_update($params);
          
        }

        function socialbooster_facebook($message,$image) {

    		require_once plugin_dir_path(__FILE__) . '/lib/facebook.php';
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
    		  "message" => $message,
    		  "link" => $image

    		);
    		try {
    		  $ret = $fb->api('/'.$pageid.'/feed', 'POST', $params);
    		} catch(Exception $e) {
    		  echo $e->getMessage();
    		}
    	}

    	function socialbooster_facebookstatus($message) {

    		require_once plugin_dir_path(__FILE__) . '/lib/facebook.php';
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
    		  "message" => $message

    		);
    		try {
    		  $ret = $fb->api('/'.$pageid.'/feed', 'POST', $params);
    		} catch(Exception $e) {
    		  echo $e->getMessage();
    		}
    	}

    	function socialbooster_facebooklink($image) {

    		require_once plugin_dir_path(__FILE__) . '/lib/facebook.php';
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

        function social_booster_instagram($photo,$caption) {
            require_once plugin_dir_path(__FILE__) . 'lib/autoload.php';
            require_once plugin_dir_path(__FILE__) . 'lib/mgp25/instagram-php/src/Instagram.php';
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

        function social_booster_runinstagram($photo,$caption) {

            $resizephoto = $this->social_booster_photo_resize($photo);
            $this->social_booster_instagram($resizephoto,$caption);
            unlink($resizephoto);
            
        }

        function social_booster_photo_resize($photo) {

            require_once plugin_dir_path(__FILE__) . 'lib/resize/src/Resize.php';
            $resize = new \MSC\Instaresize\Resize();
            $path = $resize->check($photo);
            return $path;
        }

        function social_booster_tumblr($status,$link) {
            require_once plugin_dir_path(__FILE__) . 'partials/Tumblr/vendor/autoload.php';
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

        function social_booster_tumblrlinkonly($link) {
            require_once plugin_dir_path(__FILE__) . 'partials/Tumblr/vendor/autoload.php';
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
}





