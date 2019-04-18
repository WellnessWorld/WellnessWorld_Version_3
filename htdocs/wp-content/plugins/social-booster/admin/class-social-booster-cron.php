<?php

/**
 * The admin-specific Cron jobs.
 *
 * @link       http://rextheme.com/
 * @since      1.0.0
 *
 * @package    Social_Booster
 * @subpackage Social_Booster/admin
 */

class Social_Booster_Cron {

    public function socialbooster_filter_cron_schedules($scheds){
        $new_scheds = get_option( 'socialbooster-schedules', array() );
        return array_merge( $new_scheds, $scheds );
    }

    public function socialbooster_add_schedule($name, $interval, $display){
        $old_scheds = get_option( 'socialbooster-schedules', array() );
        $old_scheds[ $name ] = array(
            'interval' => $interval,
            'display'  => $display,
        );
        update_option( 'socialbooster-schedules', $old_scheds );
    }
}