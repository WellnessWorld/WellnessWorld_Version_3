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
require_once plugin_dir_path(__FILE__) . '../lib/autoload.php';
require_once plugin_dir_path(__FILE__) . '../lib/mgp25/instagram-php/src/Instagram.php';
global $wpdb;
$tableins = $wpdb->prefix . 'socialbooster_instable'; 
$username = $wpdb->get_var("SELECT username FROM $tableins ");
?>
<html>
  <body> 
  <h1 class="headposter">Social Booster</h1>
    <div class="navigation_bar">     
      <ul>

        <li><a href="admin.php?page=socialbooster_autopost">Facebook</a></li>
        <li><a href="admin.php?page=socialbooster_twitter">Twitter</a></li>
        <li><a href="admin.php?page=socialbooster_gplus">Google Plus</a></li>
        <li><a href="#">Instagram</a></li>
        <li><a href="admin.php?page=socialbooster_tumblr">Tumblr</a></li>

      </ul>
    </div>
      <div class="soc_form_primary">
          
            <div class="fbinfo_form">
              <h3>Information form for Instagram</h3>
              <form id="insform" action="" method="post">
                <label class="label_design">Username</label><br>
                <input type="text" name="usernameins" id="fullname" class="usernameins" placeholder="Username" value="<?php echo $username; ?>"><br>
                <label class="label_design">Password</label><br>
                <input type="password" name="passwordins" id="fullname" class="passwordins" placeholder="Password" value=""><br>
                <input type="submit" name="submit_form" id="submit_form" value="Save">&nbsp;
                <img src="<?php echo admin_url('/images/spinner.gif');?>" class="waiting" id="loadimg" style="display:none;" />
                <img src="<?php echo admin_url('/images/yes.png');?>" class="waiting" id="yesimg" style="display:none;" /><h3 id="savedmessage" style="display:none;">Saved</h3>
              </form>
            </div>
      </div>
  </body>
</html>
<?php
  $authcode = $wpdb->get_var("SELECT authcode FROM $tableins ");
  $verified = $wpdb->get_var("SELECT verified FROM $tableins ");
  if (!empty($authcode) && empty($verified)) {?>
    <label class="authlabel">Authorization code is sent to your Instagram Email address. Put that code to authorize your account</label><br>
    <input type="text" name="authcode" id="authcode" class="usernameins" >&nbsp;&nbsp;<button type="button" class="authbtn">Authorize</button>
    <img src="<?php echo admin_url('/images/spinner.gif');?>" class="waiting" id="loadimg1" style="display:none;" />
 <?php }
 if (!empty($authcode) && !empty($verified)) {
    if ($authcode == $verified) {
    _e( '<h1 class="label_design2">Authorized</h1>', 'social-booster' );
     }
   else {
    _e( '<h1 class="label_design2">Wrong validation code. Please authorize again to use instagram autopost feature</h1>', 'social-booster' );
    $wpdb->query("DELETE FROM $tableins where 1=1 ");
   }
 }

?>

<div class="sb_guide_core">
  <h1 class="sb_guide" style="margin-top: 15%;">Setup Guide</h1>
  <ul class="sb_guide_list">
   <li><?php echo __('1. We are not using official instagram api. You maight get "Suspicious Login Attempt" warning for the first time.', 'social-booster'); ?></li>
   <li><?php echo __('2. Go to your instagram account. You will get a warning window. Select "This Was Me".', 'social-booster'); ?></li>
   <li><?php echo __('3. Social Booster will fail to send authorization code if instagram block login session', 'social-booster'); ?></li>
   <li><?php echo __('4. Don\'t forget to check your instagram profile email field is filled with valid email address and you are logging in with correct password. ', 'social-booster'); ?></li>
   <li><?php echo __('5. Check your server\'s SMTP service should be turned on to send any email. ', 'social-booster'); ?></li>
  </ul>
</div>



























