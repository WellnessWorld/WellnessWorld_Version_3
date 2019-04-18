jQuery(document).ready(function($){

	//Don't allow to dismiss. Instead, users can minimize it, but the message should stay there and they can expand again.
	jQuery('#welcomepanelnonce').remove();
	jQuery('a.welcome-panel-close').remove();

	var robot_icon_exported= fi_new_welcome_panel.robot_icon_exported;

	if ( ! robot_icon_exported ) {
		//Hide the rest of the dashboard.
		//Dashboard is too big for this
		jQuery('#dashboard-widgets-wrap').remove(); 
	}

    //Toggle mechanism
    jQuery( ".wpvlive-toggle" ).click(function() {
        var $togle = $(this);
        if( $togle.hasClass('expanded') ) {
            jQuery(".wpvlive-content").slideUp('slow');
            jQuery(".wpvlive-container").animate({
                "margin-left" : "84px"
            });
            jQuery(".wpvlive-image").animate({
                "width" : "54px"
            });
            $togle.html( fi_new_welcome_panel.expand + "<span class='dashicons dashicons-arrow-down'></span>" );
        } else {
            jQuery(".wpvlive-content").slideDown('slow');

            jQuery(".wpvlive-robot .wpvlive-container").animate({
                "margin-left" : "230px"
            });
            jQuery(".wpvlive-robot .wpvlive-image").animate({
                "width" : "200px"
            });

            jQuery(".wpvlive-norobot .wpvlive-container").animate({
                "margin-left" : "0px"
            });
            jQuery(".wpvlive-norobot .wpvlive-image").animate({
                "width" : "0px"
            });
            $togle.html( fi_new_welcome_panel.minimize + "<span class='dashicons dashicons-arrow-up'></span>" );
        }
        $togle.toggleClass('expanded');

    });

    var weAreOnStandalone = fi_new_welcome_panel.we_are_standalone;

    //Load JS below if we are on standalone mode
    if ( weAreOnStandalone ) {
        //Disable the Framework installer deactivate button when user does not yet agree
        jQuery('input[type="submit"][id="wpvdemo_read_understand_button"]').prop('disabled', true);

        //The button should only be active when the checkbox is selected.
        //This ensures us that the user pays (some) attention to our message.
        jQuery('#wpvdemo_read_understand_checkbox').click(function(){
            if (jQuery(this).is(':checked')) {
                jQuery('input[type="submit"][id="wpvdemo_read_understand_button"]').prop('disabled', false);
            } else {
                jQuery('input[type="submit"][id="wpvdemo_read_understand_button"]').prop('disabled', true);
            }
        });

        //Confirmation dialog
        jQuery("#wpvdemo_client_fi_confirmation_form").submit(function(e){
            var are_you_sure_js= fi_new_welcome_panel.are_you_sure_msg;
            if ( !confirm(are_you_sure_js) ){
                e.preventDefault();
                return;
            }
        });

        if ( jQuery(".wpvdemo_framework_installer_deactivated")[0] ){
            location.reload();
        }
    }
		
});

