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
<head>
<style>
table {
    border-collapse: collapse;
    width: 80%;
}

th, td {
    text-align: center;
    padding: 20px;
}

tr:nth-child(even){background-color: #f2f2f2}
</style>
</head>
<body>
<?php

global $wpdb;
$tablestatus = $wpdb->prefix . 'socialbooster_statustable';

$data =   $wpdb->get_results("SELECT *FROM $tablestatus  "); ?>
<table>
    <tr>
      <th>Schedule No</th>
      <th>Interval</th>
      <th>Blog</th>
      <th>Erase</th>
    </tr> 

    <?php
foreach ($data as  $value) {
  ?>
  
    <tr class="tr">
      <td><?php echo $value->schno; ?></td>
      <td><?php 
      $timecount = $value->interv;
      if ($timecount == 31557600) {
        _e( 'Yearly', 'social-booster' );
      }
      elseif ($timecount == 2628000) {
        _e( 'Monthly', 'social-booster' );
      }
      elseif ($timecount == 604900) {
        _e( 'Weekly', 'social-booster' );
      }
      
      ?></td>
      <td><?php echo $value->link; ?></td>
      <td><form class="delform" method="GET">
        <input type="hidden" name="deleteform" value="<?php echo $value->schno; ?>">
          <input type="submit" name="deleteform" value="Delete">&nbsp;
          <img src="<?php echo admin_url('/images/spinner.gif');?>" class="waiting" id="loadimg" style="display:none;" />
      </form></td>

    </tr>

  <?php
}
 ?>
</table>
</body>
</html> 





