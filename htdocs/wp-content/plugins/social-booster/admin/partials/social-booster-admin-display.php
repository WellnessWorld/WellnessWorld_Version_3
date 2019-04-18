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
<!DOCTYPE html>
  <body> 
<h1 class="headposter">Social Booster</h1>
    <div class="navigation_bar">
      
            <ul>

              <li><a href="#">Facebook</a></li>
              <li><a href="admin.php?page=socialbooster_twitter">Twitter</a></li>
              <li><a href="admin.php?page=socialbooster_gplus">Google Plus</a></li>
              <li><a href="admin.php?page=socialbooster_instragram">Instragram</a></li>
              <li><a href="admin.php?page=socialbooster_tumblr">Tumblr</a></li>

            </ul>
      
    </div>
      <div class="soc_form_primary">
          
            <div class="fbinfo_form">
                  <h3>Information Form for Facebook</h3>
                  <div id="fbsavedinfo" class= "fbsaved">
        
                  </div>

                  <form id="fbform" action="" method="post">

                    <label class="label_design">Username</label><br>
                    <input type="text" name="usernamefb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'epfbtable';
                    $username = $wpdb->get_var("SELECT username FROM $tablefb ");
                    echo "$username";
                    ?>><br>
                    <label class="label_design">App ID</label><br>
                    <input type="text" name="appidfb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'epfbtable';
                    $appid = $wpdb->get_var("SELECT appid FROM $tablefb ");
                    echo "$appid";
                    ?>><br>
                    <label class="label_design">App Secret</label><br>
                    <input type="text" name="appsecretfb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'epfbtable';
                    $appsecret = $wpdb->get_var("SELECT appsecret FROM $tablefb ");
                    echo "$appsecret";
                    ?>><br>
                    <label class="label_design">Access token secret</label><br>
                    <input type="text" name="accesstokenserectfb" id="fullname" placeholder=<?php             
                    global $wpdb;
                    $tablefb = $wpdb->prefix . 'epfbtable';
                    $accesstokensecret = $wpdb->get_var("SELECT accsesstokensecret FROM $tablefb ");
                    echo "$accesstokensecret";
                    ?>><br><br>
                    <input type="submit" name="submit_form" id="submit_formfb" value="Authorize">&nbsp;
                    <img src="<?php echo admin_url('/images/spinner.gif');?>" class="waiting" id="loadimg" style="display:none;" />
                  </form>
            </div>
      </div>

      
      <?php 

        ?>


  </body>
</html>







