<?php

/**
 * Class Toolset_Framework_Installer_Ajax
 */
class Toolset_Framework_Installer_Ajax {

	public function __construct(){

		add_action( 'wp_ajax_fidemo_installation_proccess', array( $this, 'fidemo_installation_proccess' ) );

	}

	/**
	 *
	 */
	function fidemo_installation_proccess(){
		$step = intval( $_POST['step'] );
		$capability = apply_filters( 'fidemo_ajax_minimal_capability', 'manage_options' );

		if ( ! current_user_can( $capability ) ) {
			$data = array(
				'type'		=> 'capability',
				'message'	=> __( 'You do not have permissions for that.', 'wpcf-access' )
			);
			wp_send_json_error( $data );
		}

		if ( ! isset( $_POST['wpnonce'] ) || ! wp_verify_nonce( $_POST['wpnonce'], 'fidemo_nonce' )
		) {
			$data = array(
				'type'    => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpcf-access' )
			);
			wp_send_json_error( $data );
		}
		$data = array();


		require_once FIDEMO_ABSPATH . '/classes/helper.class.php';

		switch ( $step ){
			case 0:
				require_once FIDEMO_ABSPATH . '/classes/download.class.php';
				$fi_download = new Toolset_Framework_Installer_Download();
				$data = $fi_download->download_site();
				break;
			case 1:
				require_once FIDEMO_ABSPATH . '/classes/unpack.class.php';
				$fi_unpack = new Toolset_Framework_Installer_Unpack();
				$data = $fi_unpack->unpack_site();
				break;
			case 2:
				require_once FIDEMO_ABSPATH . '/classes/import_db.class.php';
				$fi_unpack = new Toolset_Framework_Installer_Import_Db();
				$data = $fi_unpack->import_db();
				break;
			case 3:
				require_once FIDEMO_ABSPATH . '/classes/configure_site.class.php';
				$fi_unpack = new Toolset_Framework_Installer_Configure();
				$data = $fi_unpack->configure_site();
				break;
			case 4:
				require_once FIDEMO_ABSPATH . '/classes/finalize_site.class.php';
				$fi_unpack = new Toolset_Framework_Installer_Finalize();
				$data = $fi_unpack->finalize_site();
				break;
		}

		if ( $data['status'] === 'error' ) {
			wp_send_json_error( $data );
		} else {
			wp_send_json_success( $data );
		}



		die();
	}
}