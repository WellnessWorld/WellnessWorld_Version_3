FidemoSettings = function( $ ) {
    var self = this,
        timer = '',
        is_site_installed = false,
        is_installing = false,
        current_site = {};

    self.spinner = '<span class="fidemo-loading ajax-loader js-fidemo-spinner"></span>';

    jQuery(document).on('click', '.js-fidemo-force-refresh', function (e) {
        document.location.href = 'admin.php?page=manage-refsites&force_refresh=1';
    });



    jQuery(document).on('mouseover', '.js-fidemo-refsite', function (e) {
        jQuery(this).addClass('focus');
    });

    jQuery(document).on('mouseout', '.js-fidemo-refsite', function (e) {
        jQuery(this).removeClass('focus');
    });

    fidemo_dialog_texts.sites = jQuery.parseJSON( fidemo_dialog_texts.sites );
    var installation_steps = jQuery.parseJSON(fidemo_dialog_texts.installation_steps);
    var existing_plugins = jQuery.parseJSON( fidemo_dialog_texts.existing_plugins );
    var site_is_ready = jQuery('.fidemo-site-is-not-ready').val();

    self.init_dialogs = function() {
        jQuery('body').append('<div id="js-fidemo-dialog-container" class="fidemo-dialog-container js-fidemo-dialog-container"></div>');
        self.dialog_callback = '';
        self.dialog_callback_params = [];
        self.fidemo_dialog = jQuery("#js-fidemo-dialog-container").dialog({
            dialogClass: 'js-fidemo-dialog fidemo-dialog',
            autoOpen: false,
            modal: true,
            minWidth: 450,
            show: {
                effect: "blind",
                duration: 800
            },
            open:		function( event, ui ) {
                jQuery('body').addClass('modal-open');
                if ( ! fidemo_dialog_texts.is_discover ) {
                    jQuery('.js-fidemo-process-button')
                        .addClass('button-secondary')
                        .removeClass('button-primary ui-button-disabled ui-state-disabled')
                        .prop('disabled', true)
                        .css({'marginLeft': '15px', 'display': 'inline'});
                }

                if ( fidemo_settings.dialog_callback_params.tutorial_url == '' ){
                    jQuery('.js-fidemo-tutorial-button').hide();
                }else{
                    jQuery('.js-fidemo-tutorial-button').show();
                }

            },
            close:		function( event, ui ) {
                jQuery('body').removeClass('modal-open');
                if ( is_site_installed ) {
                    document.location.href = 'admin.php?page=manage-refsites';
                }
            },
            buttons: [
                {
                    class: 'button-primary js-fidemo-process-button',
                    text: fidemo_dialog_texts.install,
                    click: function () {
                        if ( self.dialog_callback != '' ) {
                            self.dialog_callback.call( null, self.dialog_callback_params );
                        }
                        jQuery('#js-fidemo-dialog-container').animate({
                            scrollTop: 0
                        }, 'fast');
                    }
                },
                {
                    class: 'button-secondary js-fidemo-tutorial-button',
                    text: fidemo_dialog_texts.tutorial,
                    click: function () {
                        window.open( fidemo_settings.dialog_callback_params.tutorial_url );
                    }
                },
                {
                    class: 'button-secondary ',
                    text: fidemo_dialog_texts.live_preview,
                    click: function () {
                        window.open( fidemo_settings.dialog_callback_params.preview_site );
                    }
                }
            ]
        });
    };

    self.init = function() {
        self.init_dialogs();
    };

    self.init();

    jQuery(document).on('change', '.js-fidemo-ready-to-install,.js-fidemo-db-backup', function (e) {

        if ( jQuery('.js-fidemo-ready-to-install').prop('checked') === true && jQuery('.js-fidemo-db-backup').prop('checked') === true ){
            fidemo_change_action_button( true );
        } else {
            fidemo_change_action_button( false );
        }
    });

    jQuery(document).on('click', '.js-fidemo-refsite', function (e) {
        if ( is_installing ) {
            return;
        }
        var current_site_id = jQuery(this).data('site'),
            can_install = true,
            allow_wpml = false;
        if ( site_is_ready == 0 ) {
            can_install = false;
        }
        current_site = fidemo_dialog_texts.sites[current_site_id];

        var description = current_site.short_description,
            required_plugins = '',
            required_plugins_wpml = '',
            themes = '';

        var site_size = ( current_site.downloads['files.zip']  / 1024 / 1024 ).toFixed(2) + ' MB';

        var preview_html = '<div class="fidemo-refsite-info">'+
            '<div class="fidemo-refsite-image"><img src="' + current_site.large_image + '"></div>' +
            '<div class="fidemo-refsite-action"><h1>' + current_site.title + '</h1>' +
            '<span>By OnTheGoSystems </span>' +
            '<p>' + description.replace(/\\"/g, '"') + '</p>'+
            '<p><strong>' + fidemo_dialog_texts.site_size_notice.replace( /%s/g, site_size ) + '</strong></p>';


        if ( typeof current_site.themes.additional_themes !== 'undefined' ) {
            var themes_exists = false;
            var theme_switcher = '<h3>' + fidemo_dialog_texts.available_themes + '</h3>' +
                    '<ul class="js-fidemo-available-themes">';
            jQuery.each(current_site.themes.additional_themes, function( index, value ) {


                var theme_selected =  '',
                    theme_disabled = '',
                    theme_message = '';
                if ( fidemo_dialog_texts.is_multisite && ! value.theme_status ) {
                    theme_disabled = 'disabled';
                    theme_message = ' <span class="fidemo-plugin-error">(' + fidemo_dialog_texts.theme_not_found + ')</span>';
                } else {
                    themes_exists = true;
                }
                if ( index === current_site.themes.theme && theme_disabled === '' ) {
                    theme_selected = ' checked="checked"';
                }
                theme_switcher += '<label><input type="radio" name="multiple-themes" ' + theme_disabled + ' value="' + index +'" '+ theme_selected +'> '
                    + value.name + theme_message + '</label><br>';

            });
            theme_switcher += '</ul>';

            if ( fidemo_dialog_texts.is_multisite && ! themes_exists ) {
                preview_html += '<p><span class="fidemo-plugin-error">' +
                    fidemo_dialog_texts.themes_required + '</span></p>';
                can_install = false;
            } else {
                preview_html += theme_switcher;
            }


        } else {
            if ( fidemo_dialog_texts.is_multisite && ! current_site.themes.theme_status ) {
                preview_html += '<p><span class="fidemo-plugin-error">' +
                    fidemo_dialog_texts.theme_required_single.replace( /%s/g, current_site.themes.theme ) + '</span></p>';
                can_install = false;
            }
        }

        jQuery.each(current_site.plugins, function( index, value ) {
            if ( typeof  value.title !== 'undefined'  ) {
                var update_required_text = '';
                var download_required_text = '';
                if ( typeof existing_plugins[index] === 'undefined' ){
                    download_required_text = ' ' + fidemo_dialog_texts.plugin_no_exists.replace( /%s/g, value.url );
                }
                if ( download_required_text === '' ) {
                    var current_version = existing_plugins[index].version;
                    var needed_version = value.version;
                    if ( versionCompare( current_version, needed_version) < 0 ) {
                        update_required_text = ' ' + fidemo_dialog_texts.plugin_outdated.replace(/%s/g, value.version);
                    }
                }

                var addon_text = '';
                if ( download_required_text !== '' ) {
                    addon_text = '<span class="fidemo-plugin-error">' + download_required_text + '</span>';
                }
                if ( update_required_text !== '' ) {
                    addon_text = '<span class="fidemo-plugin-error">' + update_required_text + '</span>';
                }
                if ( download_required_text === '' && update_required_text === '' ) {
                    addon_text = ' <i class="fa fa-check" style="font-size:18px;color:green"></i>';
                }

                if ( value.title.indexOf('WPML') === -1 && value.title.indexOf('Multilingual') === -1 ) {
                    required_plugins += '<li><a href="' + value.url + '" target="_blank">' + value.title + '</a>' + addon_text + '</li>';
                } else {
                    allow_wpml = true;
                    required_plugins_wpml += '<li class="js-fidemo-plugins-list-wpml hidden"><a href="' + value.url + '" target="_blank">' +
                        value.title + '</a>' + addon_text + '</li>';
                }
            }

        });

        can_install = can_install && fidemo_check_plugins_compatibility(current_site, false);

        if ( allow_wpml ) {
            preview_html += '<h3>' + fidemo_dialog_texts.language_select_title + ':</h3>' +
                '<label><input type="radio" name="fidemo-wpml" value="no-wpml" checked="checked"> ' +
                fidemo_dialog_texts.one_language_label +
                '</label><br>' +
                '<label><input type="radio" name="fidemo-wpml" value="wpml"> ' +
                fidemo_dialog_texts.multi_language_label +
                '</label>';
        }


        preview_html += '<h3>' + fidemo_dialog_texts.required_plugins + '</h3>' +
                        '<ul class="js-fidemo-plugins-list">' +
                        required_plugins +
                        required_plugins_wpml +
                        '</ul>';


        preview_html += '<div class="fidemo-confirmation-text">';
        if ( can_install  ) {
            preview_html += generate_confirmation_text();
        }
        preview_html += '</div>';

        preview_html += '</div></div>';
        if ( fidemo_dialog_texts.current_installed_site === current_site.shortname && ! fidemo_dialog_texts.is_discover  ) {
            preview_html += fidemo_show_notice( fidemo_dialog_texts.site_already_installed, 'fidemo-notice-green' );
        }

        preview_html += '<div class="fidemo-site-notices">';
        if ( ! can_install ) {
            preview_html += fidemo_show_notice( fidemo_dialog_texts.cannot_install_site );
        }
        if ( fidemo_dialog_texts.is_discover && ! current_site.can_install ) {
            if ( window.location.hash.indexOf('installsite') !== 1 ) {
                preview_html += fidemo_show_notice(fidemo_dialog_texts.maximum_alowed_sites.replace(/%s/g, current_site.title));
                fidemo_change_action_button( false );
                can_install = false;
            }
        }
        preview_html += '</div>';



        fidemo_settings.fidemo_dialog.html( preview_html );
        if ( typeof current_site.themes.additional_themes !== 'undefined' ) {
            jQuery('input[name="multiple-themes"]:not([disabled])').first().prop('checked', true);
        }
        var dialog_height = jQuery(window).height()-30;
        var dialog_width = jQuery(window).width() - 100;
        if ( fidemo_dialog_texts.is_discover && ! fidemo_settings.run_install ){
            fidemo_settings.dialog_callback = create_site;
        } else {
            fidemo_settings.dialog_callback = installation_preview;
        }

        fidemo_settings.dialog_callback_params = {
            'siteid': jQuery(this).data('site'),
            'preview_site': current_site.site_url,
            'tutorial_url': ''
        };
        if ( typeof current_site.tutorial_url !== 'undefined' && current_site.tutorial_url != '' && current_site.tutorial_url != '#' ){
            fidemo_settings.dialog_callback_params.tutorial_url = current_site.tutorial_url;
        }

        if ( can_install ) {
            fidemo_change_action_button( true );
        }

        fidemo_settings.fidemo_dialog.dialog('open').dialog({
            title: current_site.title,
            width: jQuery(window).width()-20,
            closeOnEscape: false,
            heiht: dialog_height,
            maxHeight: dialog_height-40,
            draggable: false,
            resizable: false,
            position: {my: "left top+20", at: "left+10 top", of: window}
        });

    });

    function generate_confirmation_text(){
        var output = '';
        if ( ! fidemo_dialog_texts.is_discover ) {
            output = '<h2 class="fidemo-installation-notice">' + fidemo_dialog_texts.install_notice + '<br>' + fidemo_dialog_texts.install_notice4 + '</h2>' +
                '<div class="message error"><p>' +
                '<label><input type="checkbox" value="1" class="js-fidemo-db-backup"> ' +
                fidemo_dialog_texts.install_notice3 +
                '</p></div>' +
                '<div class="message error"><p>' +
                '<label><input type="checkbox" value="1" class="js-fidemo-ready-to-install"> ' +
                fidemo_dialog_texts.install_notice2 +
                '</p></div>';
        }
        return output;
    }


    jQuery(document).on('change', 'input[name="fidemo-wpml"]', function (e) {
        var can_install = true;

        if ( jQuery(this).val() === 'no-wpml' ) {
            jQuery('.js-fidemo-plugins-list-wpml').hide();
            can_install = fidemo_check_plugins_compatibility( current_site, false );
        }else{
            jQuery('.js-fidemo-plugins-list-wpml').show();
            can_install = fidemo_check_plugins_compatibility( current_site, true );
        }

        if ( fidemo_dialog_texts.is_discover && ! current_site.can_install ) {
            can_install = false;
            fidemo_change_action_button( false );
            return false;
        }

        if ( can_install ) {
            jQuery('.fidemo-confirmation-text').html( generate_confirmation_text() );
            jQuery('.fidemo-site-notices').html(' ');
        } else {
            jQuery('.fidemo-confirmation-text').html( ' ' );
            jQuery('.fidemo-site-notices').html( fidemo_show_notice( fidemo_dialog_texts.cannot_install_site ) );
            fidemo_change_action_button( false );
        }
    });



    function fidemo_show_notice( text, style ) {
        var notice_class = '';
        if ( typeof style !== 'undefined' ) {
            notice_class = ' ' + style;
        }
        var output = '<div class="refsite-actions">' +
            '<div class="fidemo-notice' + notice_class + '">' +
            text +
            '</div></div>';
        return output;
    }

    function fidemo_change_action_button( state ){
        if ( state ) {
            jQuery('.js-fidemo-process-button ')
                .removeClass('button-secondary ui-button-disabled ui-state-disabled')
                .addClass('button-primary')
                .prop('disabled', false);
        }else{
            jQuery('.js-fidemo-process-button ')
                .addClass('button-secondary  ui-button-disabled ui-state-disabled')
                .removeClass('button-primary')
                .prop('disabled', true);
        }
    }

    var create_site = function(){
        var theme = 'default',
            lang = 'no-wpml';
        if ( typeof jQuery('input[name="multiple-themes"]:checked').val() !== 'undefined' ){
            theme = jQuery('input[name="multiple-themes"]:checked').val();
        }
        if ( typeof jQuery('input[name="fidemo-wpml"]:checked').val() !== 'undefined' ){
            lang = jQuery('input[name="fidemo-wpml"]:checked').val();
        }
        fidemo_settings.fidemo_dialog.dialog('close');
        wpvlive_create_new_site_onclick_handler( current_site.site_name, current_site.ID, theme, lang );
        var url = "#TB_inline?height=300&width=750&inlineId=wpvlive_new_site_form&modal=true";
        tb_show( "Enter a name for your site", url );
    };

    var installation_preview = function(){



        fidemo_settings.dialog_callback_params.wpml = '';
        fidemo_settings.dialog_callback_params.multiple_themes = '';

        if ( typeof jQuery('input[name="fidemo-wpml"]:checked').val() !== 'undefined' ){
            fidemo_settings.dialog_callback_params.wpml = jQuery('input[name="fidemo-wpml"]:checked').val();
        }

        if ( typeof jQuery('input[name="multiple-themes"]:checked').val() !== 'undefined' ){
            fidemo_settings.dialog_callback_params.multiple_themes = jQuery('input[name="multiple-themes"]:checked').val();
        }

        if ( fidemo_discover_settings.wpml != '' ) {
            fidemo_settings.dialog_callback_params.wpml = fidemo_discover_settings.wpml;
        }
        if ( fidemo_discover_settings.theme != '' && fidemo_discover_settings.theme != 'default' ) {
            fidemo_settings.dialog_callback_params.multiple_themes = fidemo_discover_settings.theme;
        }

        var install_html = '<h2>' +
            fidemo_dialog_texts.installation_proccess.replace(/%s/g, current_site.tagline)
            + '</h2>' +
        '<ul class="js-fidemo-installation-proccess">';

        jQuery.each( installation_steps, function(index, step){
            install_html += '<li>' + step + '</li>';
        } );
        install_html += '</ul>' +
        '<div class="js-fidemo-installation-status"></div>';
        jQuery('.fidemo-refsite-action').html(install_html);

        timer = new Date().getTime();

        fidemo_change_action_button( false );

        proccess_installation_step(0);

    };

    var proccess_installation_step = function( step ) {
        is_installing = true;
        jQuery('.ui-dialog-titlebar-close').hide();
        jQuery('.js-fidemo-installation-proccess li').eq(step).append( ' <i class="fa fa-cog fa-spin" style="font-size:20px"></i>' );

        var data = {
            action:          'fidemo_installation_proccess',
            wpnonce:         fidemo_dialog_texts.fidemo_nonce,
            site_id:         fidemo_settings.dialog_callback_params.siteid,
            step:            step,
            wpml:            fidemo_settings.dialog_callback_params.wpml,
            multiple_themes: fidemo_settings.dialog_callback_params.multiple_themes
        };

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function ( response ) {
                jQuery('.js-fidemo-installation-proccess li').eq(step).find('i').remove();

                if ( response.success ) {
                    jQuery('.js-fidemo-installation-proccess li').eq(step).append(' <i class="fa fa-check" style="font-size:24px;color:green"></i>');

                    if ( typeof response.data.nonce !== 'undefined' ) {
                        fidemo_dialog_texts.fidemo_nonce = response.data.nonce;
                    }
                    if (step < installation_steps.length - 1) {
                        proccess_installation_step(step + 1);
                    }
                } else {
                    jQuery('.js-fidemo-installation-proccess li').eq(step).append(' <i class="fa fa-warning" style="font-size:20px;color:red"></i>');
                    jQuery('.js-fidemo-installation-status').html( fidemo_show_notice( response.data.message ) );

                }

                if ( step === 4 ) {
                    is_site_installed = true;
                    is_installing = false;
                    jQuery('.ui-dialog-titlebar-close').show();
                    jQuery('.js-fidemo-installation-status').html( response.data.message );
                    console.log( '<br>Installation time: ' +( ( new Date().getTime() - timer ) / 1000 ) + ' seconds' );
                }

            },
            fail :  function(xhr, textStatus, error) {
                is_site_installed = true;
                is_installing = false;
                jQuery('.js-fidemo-installation-proccess li').eq(step).find('i').remove();
                jQuery('.js-fidemo-installation-status').html( fidemo_show_notice( error ) );
                jQuery('.js-fidemo-installation-proccess li').eq(step).append(' <i class="fa fa-warning" style="font-size:20px;color:red"></i>');
                jQuery('.ui-dialog-titlebar-close').show();
            },
            statusCode: {
                500: function( xhr, textStatus, error ) {
                    jQuery('.js-fidemo-installation-status').html( fidemo_show_notice( error ) );
                    is_site_installed = true;
                    is_installing = false;
                    jQuery('.js-fidemo-installation-proccess li').eq(step).find('i').remove();
                    jQuery('.js-fidemo-installation-proccess li').eq(step).append(' <i class="fa fa-warning" style="font-size:20px;color:red"></i>');
                    jQuery('.ui-dialog-titlebar-close').show();
                }
            }
        });
    };

    jQuery.each( jQuery('.js-fidemo-refsite'), function( index, refsite ) {
        var site_index = jQuery(this).data('site');
        var can_install = fidemo_check_plugins_compatibility( fidemo_dialog_texts.sites[site_index], false );
        if ( fidemo_dialog_texts.is_multisite && can_install ) {
            can_install = fidemo_check_themes_compatibility( fidemo_dialog_texts.sites[site_index] );
        }
        if ( ! can_install ) {
            jQuery(this).find('.fidemo-refsite-screenshot').addClass( 'fidemo-refsite-inactive' );
            jQuery('<div class="fidemo-refsite-inactive-info">' + fidemo_dialog_texts.requirements_not_met + '</div>')
                    .insertAfter( jQuery(this).find('.fidemo-refsite-screenshot img') );
        }
        if ( fidemo_dialog_texts.is_discover && ! fidemo_dialog_texts.sites[site_index].can_install ) {
            jQuery(this).find('.fidemo-refsite-screenshot').addClass( 'fidemo-refsite-inactive' );
            jQuery('<div class="fidemo-refsite-inactive-info">' + fidemo_dialog_texts.maximum_alowed_sites.replace(/%s/g, fidemo_dialog_texts.sites[site_index].title) + '</div>')
                .insertAfter( jQuery(this).find('.fidemo-refsite-screenshot img') );
        }
    });

    function fidemo_check_themes_compatibility( current_site ){
        var can_install = true;
        if ( typeof current_site.themes.additional_themes === 'undefined' ) {
            if ( ! current_site.themes.theme_status ) {
                can_install = false;
            }
        } else {
            can_install = false;
            jQuery.each(current_site.themes.additional_themes, function( index, value ) {
                if ( value.theme_status ) {
                    can_install = true;
                }
            });
        }

        return can_install;
    }

    function fidemo_check_plugins_compatibility( current_site, wpml ){
        var can_install = true;
        if ( site_is_ready == 0 ) {
            return false;
        }
        jQuery.each(current_site.plugins, function( index, value ) {

            if ( typeof  value.title !== 'undefined'  ) {

                if ( ! wpml && ( value.title.indexOf('WPML') !== -1 || value.title.indexOf('Multilingual') !== -1 ) ) {
                    return true;
                }
                if ( typeof existing_plugins[index] === 'undefined' ){
                    can_install = false;
                    return false;
                }
                if ( versionCompare( existing_plugins[index].version, value.version) < 0 ) {
                    can_install = false;
                    return false;
                }
            }

        });
        return can_install;
    }

    function versionCompare(v1, v2, options) {
        var lexicographical = options && options.lexicographical,
            zeroExtend = options && options.zeroExtend,
            v1parts = v1.split('.'),
            v2parts = v2.split('.');

        function isValidPart(x) {
            return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
        }

        if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
            return NaN;
        }

        if (zeroExtend) {
            while (v1parts.length < v2parts.length) v1parts.push("0");
            while (v2parts.length < v1parts.length) v2parts.push("0");
        }

        if (!lexicographical) {
            v1parts = v1parts.map(Number);
            v2parts = v2parts.map(Number);
        }

        for (var i = 0; i < v1parts.length; ++i) {
            if (v2parts.length == i) {
                return 1;
            }

            if (v1parts[i] == v2parts[i]) {
                continue;
            }
            else if (v1parts[i] > v2parts[i]) {
                return 1;
            }
            else {
                return -1;
            }
        }

        if (v1parts.length != v2parts.length) {
            return -1;
        }

        return 0;
    }




};
var fidemo_discover_settings = { 'wpml' : '', 'theme' : '' };

jQuery( document ).ready( function( $ ) {
    fidemo_settings = new FidemoSettings( $ );

    var refsite = fidemo_getUrlParameter('refsite');
    if ( refsite !== '' && ! window.location.hash ) {
        jQuery.each( jQuery( ".js-fidemo-refsite" ), function( i, v ) {
            if ( jQuery(this).data('shortname') == refsite ) {
                jQuery( this ).trigger( "click" );
                return false;
            }
        });
    }

    if (window.location.hash) {
        if ( window.location.hash.indexOf('installsite') === 1 ) {
            var fullhash = window.location.hash,
                parts = fullhash.split('_'),
                origin = parts[parts.length - 1],
                theme = parts[parts.length - 3],
                version = parts[parts.length - 2],
                site_id = parts[parts.length - 4],
                target_verification = fidemo_dialog_texts.verification_string,
                site_screen= 'admin.php?page=manage-refsites';
            if ( typeof fidemo_dialog_texts.current_installed_site !== 'undefined' && fidemo_dialog_texts.current_installed_site != '' ) {
                return;
            }
            if ( origin && target_verification ) {

                var the_origin = jQuery.trim(origin);
                var the_target_verification =  jQuery.trim(target_verification);
                if ( the_target_verification == the_origin ) {

                    jQuery.each( jQuery( ".js-fidemo-refsite" ), function( i, v ) {
                        if ( jQuery(this).data('id') == site_id ) {
                            fidemo_discover_settings.wpml = version;
                            fidemo_discover_settings.theme = theme;
                            fidemo_settings.run_install = true;
                            jQuery( this ).trigger( "click" );
                            jQuery('.js-fidemo-process-button').click();
                            return false;
                        }
                    });
                } else {
                    //Redirect to reference sites screen
                    window.location =	site_screen;
                }
            } else {
                //Redirect to reference sites screen
                window.location =	site_screen;
            }
        }
    }
});

function fidemo_getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};