<?php

/**
 * The admin-specific Ajax files.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin
 */

class Social_Booster_Ajax {

    function socialbooster_process_tw_info() {

      check_ajax_referer( 'social_booster', 'security' );

		if ($_POST["usernametw"] != "" && $_POST["conkeytw"] != "" && $_POST["consecrettw"] != ""  && $_POST["accesstokentw"] != ""&&  $_POST["accesstokensecrettw"] != "")
		        {
		            global $wpdb;
		            $table = $wpdb->prefix . 'socialbooster_twtable';
		            $wpdb->query("DELETE FROM $table where 1=1 ");
                $usernametwsanitize = strip_tags($_POST["usernametw"], "");
                $usernametwsanitizes = sanitize_text_field( $usernametwsanitize );
                $conkeytwsanitize = strip_tags($_POST["conkeytw"], "");
                $conkeytwsanitizes = sanitize_text_field( $conkeytwsanitize );
                $consecrettwsanitize = strip_tags($_POST["consecrettw"], "");
                $consecrettwsanitizes = sanitize_text_field( $consecrettwsanitize );
                $accesstokentwsanitize = strip_tags($_POST["accesstokentw"], "");
                $accesstokentwsanitizes = sanitize_text_field( $accesstokentwsanitize );
                $accesstokensecrettwsanitize = strip_tags($_POST["accesstokensecrettw"], "");
                $accesstokensecrettwsanitizes = sanitize_text_field( $accesstokensecrettwsanitize );
		            $usernametw = $usernametwsanitizes;
		            $conkeytw = $conkeytwsanitizes;
		            $consecrettw = $consecrettwsanitizes;
		            $accesstokentw = $accesstokentwsanitizes;
		            $accesstokensecrettw = $accesstokensecrettwsanitizes;

		            require_once plugin_dir_path(__FILE__) . '/lib/codebird.php';

            		\Codebird\Codebird::setConsumerKey($conkeytw,$consecrettw); 

            		$cb = \Codebird\Codebird::getInstance();
            		$reply = $cb->oauth_requestToken([
              		'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
            		]);

            		$_SESSION['oauth_verify'] = true;
            		$auth_url = $cb->oauth_requestToken();
            		$url = $auth_url->oauth_token;
            		$authorize_url = 'https://api.twitter.com/oauth/authorize?oauth_token=' . $url;
            		$msg = array();

            		if(!empty($url)){
                  		$wpdb->insert(
                  		$table,
                  		array(

                      'username' => $usernametw,
                       'consumerkey' => $conkeytw,
                      'consumersecret' => $consecrettw,
                      'accesstoken' => $accesstokentw,
                      'accsesstokensecret' => $accesstokensecrettw

                    	)
              		);
                  		$msg['success'] = '<h1 class="label_design">Successfully Saved</h1>';

            		}
            		else {

            			$msg['error'] = '<h1 class="label_design">Error Occured. Invalid Consumer Key and Cunsumer Secret</h1>';
            		}

		        } 

		        elseif ($_POST["usernametw"] == "" || $_POST["conkeytw"] == "" || $_POST["consecrettw"] == "" ||  $_POST["accesstokentw"] == "" || $_POST["accesstokensecrettw"] == "" )
		        {
		           $msg['error'] = '<h1 class="label_design">Fill All Required Fields</h1>'; 
		        }
		        $msg['url'] = $authorize_url;

		        echo json_encode($msg);
		        exit();
    }

    function socialbooster_process_delete_info() {
      check_ajax_referer( 'social_booster', 'security' );
        global $wpdb;
        $tablestatus = $wpdb->prefix . 'socialbooster_statustable';
        parse_str( $_POST[ 'newFormRecherche' ], $newFormRecherche );
        $id = $newFormRecherche['deleteform'];
        $wpdb->query("DELETE FROM $tablestatus where schno=$id ");
        echo '<META HTTP-EQUIV="REFRESH" CONTENT="1">' ;
        wp_die();
 
    }

    function socialbooster_process_instagram_info() {
      check_ajax_referer( 'social_booster', 'security' );
      require_once plugin_dir_path(__FILE__) . 'lib/autoload.php';
      require_once plugin_dir_path(__FILE__) . 'lib/mgp25/instagram-php/src/Instagram.php';
      set_time_limit(0);
      date_default_timezone_set('UTC');
      $insusername = $_POST['usernameins'];
      $inspassword = $_POST['pasins'];
      $encryptedpass = "dj515656ksfj51sdj".$inspassword."sd85fssf5656664f56sd5";
      $characters = "0123456789";
      $auth_code = 'sb';
      for ($i = 0; $i < 7; $i++) {
        $auth_code .= $characters[rand(0, strlen($characters) - 1)];
      }
      global $wpdb;
      $tableins = $wpdb->prefix . 'socialbooster_instable';
      $wpdb->query("DELETE FROM $tableins where 1=1 ");
      if (!empty($insusername) && !empty($inspassword)) {
        $wpdb->insert(
          $tableins,
            array(
            'username' => $insusername,
             'password' => $encryptedpass,
             'authcode' => $auth_code,
            )
        );
      }
            $username = $insusername;
            $password = $inspassword;
            $debug = false;
            $truncatedDebug = false;

            $i = new \InstagramAPI\Instagram($username, $password, $debug);

            try {
                $i->login();

            } catch (Exception $e) {
                $e->getMessage();
                exit();
            }


            try {
                $insuserdata = $i->getProfileData();
                $insusermail = $insuserdata["user"]["email"];
                $mail_body = $insusername.", your verification code is: ".$auth_code;
                wp_mail( $insusermail, 'Social Booster Verification for Instagram', $mail_body);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
      exit();
    }

    function socialbooster_process_instagram_auth() {
      check_ajax_referer( 'social_booster', 'security' );
      global $wpdb;
      $tableinst = $wpdb->prefix . 'socialbooster_instable';
      $authcodegen = $wpdb->get_var("SELECT authcode FROM $tableinst ");
      $authcodeget = $_POST['authcode'];
      $wpdb->update($tableinst, array('verified'=>$authcodeget), array('authcode'=>$authcodegen));
      exit();
    }

    function socialbooster_process_fb_info() {
    check_ajax_referer( 'social_booster', 'security' );
    require_once plugin_dir_path(__FILE__) . 'partials/Facebook/autoload.php';

    parse_str( $_POST[ 'fbformdata' ], $fbformdata );
    
    $appid = $fbformdata['appidfb'];
    $appsecretfb = $fbformdata['appsecretfb'];
    $pageiddata = $fbformdata['pageidfb'];
    
    $fb = new \Facebook\Facebook([
              'app_id' => $appid,
              'app_secret' => $appsecretfb,
              'default_graph_version' => 'v2.2'
      ]);
    // wp_send_json_error($fb);
    $helper = $fb->getJavaScriptHelper();


    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      wp_send_json_error('<h1 class="label_design">Graph returned an error: ' . $e->getMessage() . ' Please try again once ');
      // echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      wp_send_json_error( '<h1 class="label_design">Facebook SDK returned an error: ' . $e->getMessage() . ' Please try again once ' );
      // echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    if (! isset($accessToken)) {
      wp_send_json_error( '<h1 class="label_design">No cookie set or no OAuth data could be obtained from cookie.</h1>' );
      // echo 'No cookie set or no OAuth data could be obtained from cookie.';
      exit;
    }

    // wp_send_json_error($accessToken);
    $shortaccsstoken = $accessToken->getValue();
    var_dump($shortaccsstoken);
    $url = 'https://graph.facebook.com/me/accounts?access_token='.$shortaccsstoken;
    $contextOptions = [
    'ssl' => [
            'verify_peer' => false,
            'allow_self_signed' => true
        ]
    ];
    $sslContext = stream_context_create($contextOptions);
    $json = file_get_contents($url,false, $sslContext);
    $obj = json_decode($json,true);
    $pagedatas = $obj['data'];   

    foreach ($pagedatas as $pagedata) { 
    $pageid = $pagedata['id']; 
      if ($pageid == $pageiddata) {
        $pagetoken = $pagedata['access_token'];
      }
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'socialbooster_fbtable';
    $wpdb->query("DELETE FROM $table where 1=1 ");
    $wpdb->insert(
      $table,
      array(

          'username' => $pageiddata,
           'appid' => $appid,
          'appsecret' => $appsecretfb,
          'accsesstokensecret' => $pagetoken,
          'redirecturl' => 0,
          'authorization' => 1

        )
    );  
    exit();
  }
}