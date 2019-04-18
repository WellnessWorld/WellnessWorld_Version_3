<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin/partials
 */
session_start();
?>
<html>
  <body> 
  <h1 class="headposter">Social Booster</h1>
    <div class="navigation_bar">
      
            <ul>

              <li><a href="admin.php?page=socialbooster_autopost">Facebook</a></li>
              <li><a href="admin.php?page=socialbooster_twitter">Twitter</a></li>
              <li><a href="admin.php?page=socialbooster_gplus">Google Plus</a></li>
              <li><a href="admin.php?page=socialbooster_instragram">Instagram</a></li>
              <li><a href="#">Tumblr</a></li>

            </ul>
      
    </div>
      <div class="soc_form_primary">
          
            <div class="fbinfo_form">
              <h3>Information Form for Tumblr</h3>

              <form id="fbform" action="" method="post">

                <label class="label_design">Blog URL</label><br>
                <input type="text" name="tmblogname" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tabletm = $wpdb->prefix . 'socialbooster_stmtable';
                    $bgname = $wpdb->get_var("SELECT blogname FROM $tabletm ");
                    _e( $bgname, 'social-booster' );
                    ?>><br>
                <label class="label_design">Consumer Key</label><br>
                <input type="text" name="tmkey" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tabletm = $wpdb->prefix . 'socialbooster_stmtable';
                    $bgkey = $wpdb->get_var("SELECT consumerkey FROM $tabletm ");
                    _e( $bgkey, 'social-booster' );
                    ?>><br>
                <label class="label_design">Consumer Secret</label><br>
                <input type="text" name="tmkeyscret" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tabletm = $wpdb->prefix . 'socialbooster_stmtable';
                    $bgkeysecret = $wpdb->get_var("SELECT consumersecret FROM $tabletm ");
                    _e( $bgkeysecret, 'social-booster' );
                    ?>><br>
                <input type="submit" name="submit_formtumb" id="submit_form" value="Authorize">&nbsp;

              </form>
            </div>

      </div>
    <?php
      require_once "Tumblr/vendor/autoload.php";
      if (isset($_POST["submit_formtumb"]) && $_POST["tmblogname"] != "" && $_POST["tmkey"] != "" && $_POST["tmkeyscret"] != "" ) 
      {
          $blogname = strip_tags($_POST["tmblogname"], "");
          $blognames = sanitize_text_field( $blogname );
          $tmkey = strip_tags($_POST["tmkey"], "");
          $tmkeys = sanitize_text_field( $tmkey );
          $tmkeysecret = strip_tags($_POST["tmkeyscret"], "");
          $tmkeysecrets = sanitize_text_field( $tmkeysecret );
          $tumblrkey = $tmkeys;
          $tumblrsecret = $tmkeysecrets;
          // var_dump($tumblrkey.$tumblrsecret.$blognames);
          global $wpdb;
          $table = $wpdb->prefix . 'socialbooster_stmtable';
          $wpdb->query("DELETE FROM $table where 1=1 ");
          $wpdb->insert(
                    $table,
                    array(

                        'blogname' => $blognames,
                        'consumerkey' => $tumblrkey,
                        'consumersecret' => $tumblrsecret,
                        'authorization' => 0

                      )
          );

          // some variables that will be pretttty useful
          $consumerKey = $tumblrkey;
          $consumerSecret = $tumblrsecret;
          $client = new Tumblr\API\Client($consumerKey, $consumerSecret);
          $requestHandler = $client->getRequestHandler();
          $requestHandler->setBaseUrl('https://www.tumblr.com/');
          // start the old gal up
          $resp = $requestHandler->request('POST', 'oauth/request_token', array());
          // get the oauth_token
          $out = $result = $resp->body;
          $data = array();
          parse_str($out, $data);

          $_SESSION['request_token'] = $data['oauth_token'];
          $_SESSION['request_token_secret'] = $data['oauth_token_secret'];

          if($data['oauth_callback_confirmed']) {
              // redirect
              $url = 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $data['oauth_token'];
              echo "<script>window.top.location.href='".$url."'</script>";
          } else {
              echo 'Could not connect to Tumblr. Refresh the page or try again later.';
          }
          exit();
      }
      if (isset($_POST["submit_formtumb"]) && ($_POST["tmblogname"] == "" || $_POST["tmkey"] == "" || $_POST["tmkeyscret"] == ""))
        {         
             _e( '<h1 class="label_design">Fill All Required Fields</h1>', 'social-booster' );     
        }
      if (isset($_GET['oauth_verifier'])) {
          global $wpdb;
          $table = $wpdb->prefix . 'socialbooster_stmtable';
          $tmkeyget = $wpdb->get_var("SELECT consumerkey FROM $table ");
          $tmsecretget = $wpdb->get_var("SELECT consumersecret FROM $table ");
          $consumerKey = $tmkeyget;
          $consumerSecret = $tmsecretget;
          $client = new Tumblr\API\Client($consumerKey, $consumerSecret, $_SESSION['request_token'], $_SESSION['request_token_secret']);
          $requestHandler = $client->getRequestHandler();
          $requestHandler->setBaseUrl('https://www.tumblr.com/');

          unset($_SESSION['request_token']);
          unset($_SESSION['request_token_secret']);

          $link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
          $verifier = $_GET['oauth_verifier'];

          $resp = $requestHandler->request('POST', 'oauth/access_token', array('oauth_verifier' => $verifier));
          $out = $result = $resp->body;
          $data = array();
          parse_str($out, $data);

          // and print out our new keys
          $token = $data['oauth_token'];
          $secret = $data['oauth_token_secret'];
          $wpdb->update($table, array('token'=>$token), array('consumerkey'=>$consumerKey)); 
          $wpdb->update($table, array('tokensecret'=>$secret), array('consumerkey'=>$consumerKey));
          $wpdb->update($table, array('authorization'=>1), array('consumerkey'=>$consumerKey)); 
          _e( '<h1 class="label_design2">Successfully Authorized</h1>', 'social-booster' );
      } 
        ?>
        <div id="fbsavedinfo">
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'socialbooster_stmtable';
        $authorized = $wpdb->get_var("SELECT authorization FROM $table ");
        if($authorized!=1)
        {
          _e( '<h1 class="label_design">You are currently not authorized</h1>', 'social-booster' );
        }
        ?>
        </div>
        <?php 
    ?>
    <div class="sb_guide_core">
          <h1 class="sb_guide">Setup Guide</h1>
          <ul class="sb_guide_list">
            <li><?php echo __('1. Go to <a href="https://www.tumblr.com/oauth/apps">https://www.tumblr.com/oauth/apps</a> and click on "+ Register application"', 'social-booster'); ?></li>
            <li><?php echo __('2. Fill up all required fields.', 'social-booster'); ?></li>
            <li><?php 
            if (social_booster_check_https()) {
              $current_url="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
              echo __('3. Put  Default callback URL: '.$current_url.'', 'social-booster');
            }
            else {
              $current_url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
              echo __('3. Put  Default callback URL: '.$current_url.'', 'social-booster');
            }            
            ?></li>
            <li><?php echo __('4. Save changes. You can see "OAuth Consumer Key" and click on "Show secret key".', 'social-booster'); ?></li>
            <li><?php echo __('5. Copy both and paste them on Tumblr information form. Go to Account->Settings and from the right menu tab choose the blog you want to congfigure.', 'social-booster'); ?></li>
            <li><?php echo __('6. Copy blogs Tumblr URL and paste it on "Blog URL". Now you are ready to authorize.', 'social-booster'); ?></li>
          </ul>
        </div>
  </body>
</html>







