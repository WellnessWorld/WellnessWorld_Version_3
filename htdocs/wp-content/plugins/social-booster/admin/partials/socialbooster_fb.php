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
?>
<html>
  <body> 

    <h1 class="headposter">Social Booster</h1>
    <div class="navigation_bar">
      
            <ul>

              <li><a href="#">Facebook</a></li>
              <li><a href="admin.php?page=socialbooster_twitter">Twitter</a></li>
              <li><a href="admin.php?page=socialbooster_gplus">Google Plus</a></li>
              <li><a href="admin.php?page=socialbooster_instragram">Instagram</a></li>
              <li><a href="admin.php?page=socialbooster_tumblr">Tumblr</a></li>

            </ul>
      
    </div>
      <div class="soc_form_primary">
          
            <div class="fbinfo_form">
                  <h3>Information Form for Facebook</h3>
                  <div id="fbsavedinfo" class= "fbsaved">
        
                  </div>
                  <form id="fbform" class="fabform" action="" method="post">

                    <label class="label_design">App ID</label><br>
                    <input type="text" name="appidfb" id="fullname" class="fbappid" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
                    $appid = $wpdb->get_var("SELECT appid FROM $tablefb ");
                    _e( $appid, 'social-booster' );
                    ?>><br>
                    <label class="label_design">App Secret</label><br>
                    <input type="text" name="appsecretfb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
                    $appsecret = $wpdb->get_var("SELECT appsecret FROM $tablefb ");
                    _e( $appsecret, 'social-booster' );
                    ?>><br>
                    <label class="label_design">Page ID</label><br>
                    <input type="text" name="pageidfb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'socialbooster_fbtable';
                    $appsecret = $wpdb->get_var("SELECT username FROM $tablefb ");
                    _e( $appsecret, 'social-booster' );
                    ?>><br>
                    <input type="submit" name="submit_form" id="submit_form" value="Authorize">&nbsp;
                    <img src="<?php echo admin_url('/images/spinner.gif');?>" class="waiting" id="loadimg" style="display:none;" />
                    <img src="<?php echo admin_url('/images/yes.png');?>" class="waiting" id="yesimg" style="display:none;" /><h3 id="savedmessage" style="display:none;">You Are Authorized</h3>
                  </form>
            </div>
      </div>
     <div id="fbsavedinfo">
      <?php 

        ?>
        </div>
        <div class="sb_guide_core">
          <h1 class="sb_guide">Setup Guide</h1>
          <ul class="sb_guide_list">
            <li><?php if( isset($_SERVER['HTTPS'] ) ) {
              echo __('1. Your server is secured and running with https.', 'social-booster');
            }
            else {
              echo __('1. https:// required to configure facebook. Facebook doesn\'t allow insecured url. So move your server form http to https or try loading your site using https. Check <a href="https://developers.facebook.com/docs/facebook-login/review/requirements/">https://developers.facebook.com/docs/facebook-login/review/requirements/</a> for more information.', 'social-booster');
              } ?></li>
            <li><?php echo __('2. Go to <a href="https://developers.facebook.com/apps">https://developers.facebook.com/apps</a> and click on "+ Add a New App"', 'social-booster'); ?></li>
            <li><?php echo __('3. Go to Settings->Basic and put App Domain: '.$_SERVER['SERVER_NAME'].'', 'social-booster'); ?></li>
            <li><?php echo __('4. Put valid Privacy Policy URL and Terms of Service URL', 'social-booster'); ?></li>
            <li><?php echo __('5. Fill up all required information', 'social-booster'); ?></li>
            <li><?php echo __('6. Go to "+Add Platform" and click on "Website". Now put Your Site URL: '.site_url().' ', 'social-booster'); ?></li>
            <li><?php echo __('7. Go to "PRODUCTS +" from the left menu bar and click on Facebook Login "Set Up".', 'social-booster'); ?></li>
            <li><?php echo __('8. Click "Web" and fill up your informations. A new product will be added below "PRODUCTS" named Fcebook Login.', 'social-booster'); ?></li>
            <li><?php
            $current_url="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
            echo __('9. Go to Facebook Login->Settings and put Valid OAuth Redirect URIs: '.$current_url.'', 'social-booster'); ?></li>
            <li><?php echo __('10. In the end turn your application status on to make it public and you are ready to configure.', 'social-booster'); ?></li>
            <li><?php echo __('11. Go to app Settings->Basic and copy "APP ID" & "APP SECRET". Paste them on Facebook information form.', 'social-booster'); ?></li>
            <li><?php echo __('12. Go to your facebook page where you want to post. Click on "About" and you will find your page id there. Copy and paste it on Facebook information from and authorize.', 'social-booster'); ?></li>
          </ul>
        </div>
  </body>
</html>







