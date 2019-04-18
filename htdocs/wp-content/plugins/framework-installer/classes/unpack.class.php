<?php
/**
 * Date: 05/04/18
 * Time: 10:43
 */

class Toolset_Framework_Installer_Unpack extends Toolset_Framework_Installer_Install_Step {

	/**
	 * Step 2: Unpack files
	 * @return array
	 *
	 */
	function unpack_site(){
		global $frameworkinstaller;

		$status = $this->unzip_site( $this->dest );
		if ( ! $status ) {
			return $this->generate_respose_error( false, __( 'Cannot unpack demo files', 'wpvdemo' ) );
		}

		if ( ! $frameworkinstaller->is_discoverwp() && ! is_multisite() ) {

			$status = true;
			if ( ! $this->is_theme_installed( $this->get_selected_theme(), $this->get_theme_version() ) ) {
				$status = $this->unzip_theme( $this->theme_dest, $this->get_selected_theme() );
			}

			$parent_theme = $this->get_parent_theme();
			if ( ! empty( $parent_theme ) ) {
				if ( ! $this->is_theme_installed( $this->get_parent_theme(), $this->get_parent_theme_version() ) ) {
					$status = $this->unzip_theme( $this->theme_parent_dest, $this->get_parent_theme() );
				}
			}

			if ( ! $status ) {
				return $this->generate_respose_error( false, __( 'Cannot unpack theme files', 'wpvdemo' ) );
			}
		}

		if ( $status ) {
			$data =  $this->generate_respose_error( true,__( 'Files successfully unpacked', 'wpvdemo' ) );
		}
		return $data;
	}

	/**
	 * Unpack site zip to uploads directory
	 * @return bool
	 */
	function unzip_site( ){
		global $frameworkinstaller;
		$search_for = 'files/';
		if ( $this->use_optimized_version() ) {
			$search_for = 'files2/';
		}
		$status = false;
		$zip = new ZipArchive;
		if ( $zip->open( $this->dest ) === true ) {
			for( $i = 0; $i < $zip->numFiles; $i++) {

				$filename = $zip->getNameIndex($i);
				$fileinfo = pathinfo( $filename );
				if ( $fileinfo['extension'] === 'sql' ) {
					copy("zip://".$this->dest."#".$filename, $this->upload_dir['basedir'] . '/fidemo_sql_dump.sql' );
				} else {

					$upload_dir = explode('/', str_replace( $search_for, '', $fileinfo['dirname'] ) );
					$dest_path = $this->upload_dir['basedir'];
					foreach( $upload_dir as $index => $dir ) {
						if ( $dir === '.' ) {
							continue;
						}
						$dest_path .=  '/' . $dir;
						if ( ! file_exists( $dest_path  ) ) {
							mkdir( $dest_path , 0755 );
						}
					}
					copy("zip://".$this->dest."#".$filename,
						$this->upload_dir['basedir'] .'/'. str_replace( $search_for, '', $filename ) );
				}


			}
			$zip->close();
			$status = true;
		}
		if ( $frameworkinstaller->is_discoverwp() ){
			unlink( $this->dest );
		}
		return $status;
	}


	/**
	 * Unpack selected theme to the themes directory
	 * @param $file
	 * @param $theme
	 *
	 * @return bool
	 */
	function unzip_theme( $file, $theme ){
		$status = false;
		$zip = new ZipArchive;
		$theme_root = get_theme_root() . '/' . $theme;

		if ( ! file_exists( $theme_root  ) ) {
			mkdir( $theme_root , 0755 );
		}
		if ( $zip->open( $file ) === true ) {
			for( $i = 0; $i < $zip->numFiles; $i++) {

				$filename = $zip->getNameIndex($i);
				$fileinfo = pathinfo( $filename );
				$upload_dir = explode('/', $fileinfo['dirname'] );
				$dest_path = $theme_root;
				foreach( $upload_dir as $index => $dir ) {
					if ( $dir === '.' ) {
						continue;
					}
					$dest_path .=  '/' . $dir;
					if ( ! file_exists( $dest_path  ) ) {
						mkdir( $dest_path , 0755 );
					}
				}
				copy("zip://" . $file . "#" . $filename,
					$theme_root . '/' . $filename );
			}
			$zip->close();
			$status = true;
		}
		@unlink( $file );
		return $status;
	}

}
