jQuery( document ).ready( function( $ ) {
	
	//Remove add new link
	$('a.add-new-h2').remove();
	
	//Remove add description link
	$('a.js-wpcf-toggle-description').remove();
	
	//Remove submit div
	$('div#submitdiv').remove();
	$('div.postbox-container').remove();
	
	//Remove all remove field links
	$('a.js-wpcf-field-remove').remove();

});