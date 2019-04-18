<?php

/**
 * Class TLM_EditorDialogs
 */
class TLM_EditorDialogs extends Toolset_DialogBoxes{

	public function template(){
		ob_start();?>

		<script type="text/html" id="tlm-finish-dialog">
			<div id="js-dialog-dialog-container">
				<div class="ddl-dialog-content" id="js-dialog-content-dialog">
					<p><?php printf( __('A Content Layout for "%s" "%s" has been created.', 'ddl-layouts'), "{{{ post_type }}}", "{{{ post_title }}}" );?></p>
					<# if( template ){ #>
						<p><?php printf( __('A Template Layout "%s" has been assigned to "%s" "%s".', 'ddl-layouts'), "{{{ template_name }}}", "{{{ post_title }}}", "{{{ post_type }}}" );?></p>
						<# } #>
					<# if( current_layout_deleted ){ #>
					<p><?php printf( __('A Template Layout with ID "%s" has been removed.', 'ddl-layouts'), "{{{ current_layout }}}" );?></p>
						<# } else {
							if ( is_wpml_active ){
							#>
							<?php printf( __('The Template Layout with ID "%s" has not been removed since it is still in use in other languages. Switch language with the language selector in admin bar to load items in other languages.', 'ddl-layouts'), "{{{ current_layout }}}" );?>
							<# } #>

								<# } #>
				</div>
			</div>
		</script>

		<script type="text/html" id="tlm-error-dialog">
			<div id="js-dialog-dialog-container">
				<div class="ddl-dialog-content" id="js-dialog-content-dialog">
					<p>{{{error}}}</p>
				</div>
			</div>
		</script>

	   <?php
		echo ob_get_clean();
	}
}