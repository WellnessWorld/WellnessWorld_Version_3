<?php
/**
 * Date: 05/04/18
 * Time: 10:44
 */

class Toolset_Framework_Installer_Finalize extends Toolset_Framework_Installer_Install_Step {
	/**
	 * Step 5: Send stats and generate final message
	 * @return array
	 */
	function finalize_site(){

		//Send stats
		do_action( 'fidemo_log_refsites_to_toolset' );

		//Set current installed site
		update_option( 'fidemo_installed', $this->current_site->shortname );
		$theme = $this->get_selected_theme();

		//Show final message
		$output = '<p>' . sprintf( __( 'The reference site was successfully imported. We\'ve activated the theme: %s. This test site should look the same as our reference site.', 'wpvdemo' ),
				ucwords( $theme ) ) . '</p>';

		$output .= '<p>
			<a href="' . admin_url() . '" class="button button-primary">' . __( 'Manage your site', 'wpvdemo' ) . '</a> 
			<a href="' . $this->site_url . '" class="button button-primary" target="_blank">' . __( 'Visit the site\'s front-end', 'wpvdemo' ) . '</a> ';

		if ( isset( $this->current_site->tutorial_url ) && ! empty( $this->current_site->tutorial_url ) ) {
			$output .= '<a href="' . $this->current_site->tutorial_url . '" class="button button-secondary"  target="_blank">' . __( 'View site tutorial', 'wpvdemo' ) . '</a>';
		}

		$output .= '</p>';

		$data = $this->generate_respose_error( true, $output );

		return $data;
	}

}