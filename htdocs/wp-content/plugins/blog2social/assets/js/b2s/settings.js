jQuery.noConflict();
jQuery(window).on("load", function () {

    var showMeridian = true;
    if (jQuery('#b2sUserLang').val() == 'de') {
        showMeridian = false;
    }
    jQuery('.b2s-settings-sched-item-input-time').timepicker({
        minuteStep: 30,
        appendWidgetTo: 'body',
        showSeconds: false,
        showMeridian: showMeridian,
        defaultTime: 'current'
    });
    var b2sShowSection = jQuery('#b2sShowSection').val();
    if (b2sShowSection != "") {
        jQuery("." + b2sShowSection).trigger("click");
    }
    jQuery(".b2s-import-auto-post-type").chosen();

    jQuery('.b2s-network-item-auth-list[data-network-count="true"]').each(function () {
        jQuery('.b2s-network-auth-count-current[data-network-id="' + jQuery(this).attr("data-network-id") + '"').text(jQuery(this).children('li').length);
    });

    var length = jQuery('.b2s-post-type-item-update').filter(':checked').length;
    if (length > 0) {
        jQuery('.b2s-auto-post-own-update-warning').show();
    }

    //TOS Twitter 032018 - none multiple Accounts - User select once
    checkNetworkTos(2);

});

jQuery('.b2sSaveSocialMetaTagsSettings').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery('.b2s-meta-tags-success').hide();
        jQuery('.b2s-meta-tags-danger').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                    if (data.b2s == true) {
                        if (data.yoast == true) {
                            jQuery('.b2s-meta-tags-yoast').show();
                        }
                        if (data.aioseop) {
                            jQuery('.b2s-meta-tags-aioseop').show();
                        }
                        if (data.webdados) {
                            jQuery('.b2s-meta-tags-webdados').show();
                        }
                    }
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});

//TOS Twitter 032018 - none multiple Accounts - User select once
jQuery(document).on('change', '.b2s-network-tos-check', function () {
    var networkId = jQuery(this).attr('data-network-id');
    if (networkId == 2) {
        checkNetworkTos(networkId, false);
    }
    return false;
});

//TOS Twitter 032018 - none multiple Accounts - User select once
function checkNetworkTos(networkId) {
    var len = jQuery('.b2s-network-tos-check[data-network-id="' + networkId + '"]:checked').length;
    if (len > 1) {
        jQuery('.b2s-network-tos-auto-post-import-warning').show();
        jQuery('#b2s-auto-post-import-settings-btn').attr('disabled', 'disabled');
        return false;
    } else {
        jQuery('.b2s-network-tos-auto-post-import-warning').hide();
        jQuery('#b2s-auto-post-import-settings-btn').attr('disabled', false);
        return true;
    }
}

jQuery(document).on('change', '.b2s-post-type-item-update', function () {
    var length = jQuery('.b2s-post-type-item-update').filter(':checked').length;
    if (length == 0) {
        jQuery('.b2s-auto-post-own-update-warning').hide();
    } else {
        jQuery('.b2s-auto-post-own-update-warning').show();
    }
    return false;
});


jQuery(document).on('click', '.b2sClearSocialMetaTags', function () {

    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery('.b2s-clear-meta-tags').hide();
    jQuery(".b2s-loading-area").show();
    jQuery(".b2s-user-settings-area").hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_reset_social_meta_tags',
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery(".b2s-loading-area").hide();
            jQuery(".b2s-user-settings-area").show();
            if (data.result == true) {
                jQuery('.b2s-clear-meta-tags-success').show();
            } else {
                jQuery('.b2s-clear-meta-tags-error').show();
            }
        }
    });
    return false;
});



jQuery(document).on('click', '.b2s-upload-image', function () {
    var targetId = jQuery(this).attr('data-id');
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
        wpMedia = wp.media({
            title: jQuery('#b2s_wp_media_headline').val(),
            button: {
                text: jQuery('#b2s_wp_media_btn').val(),
            },
            multiple: false,
            library: {type: 'image'}
        });
        wpMedia.open();

        wpMedia.on('select', function () {
            var validExtensions = ['jpg', 'jpeg', 'png'];
            var attachment = wpMedia.state().get('selection').first().toJSON();

            jQuery('#' + targetId).val(attachment.url);
        });
    } else {
        jQuery('.b2s-upload-image-no-permission').show();
    }
    return false;
});




jQuery(document).on('click', '.b2s-save-settings-pro-info', function () {
    return false;
});

jQuery(document).on('click', '#b2s-user-network-settings-short-url', function () {
    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery('.b2s-server-connection-fail').hide();

    if (jQuery('#b2s-user-network-shortener-state[data-provider-id="0"]').val() == "0") {
        jQuery('.b2s-shortener-account-connect-btn[data-provider-id="0"]').trigger('click');
    } else {
        jQuery(".b2s-user-settings-area").hide();
        jQuery(".b2s-loading-area").show();

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                'action': 'b2s_user_network_settings',
                'short_url': jQuery('#b2s-user-network-settings-short-url').val(),
            },
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                    jQuery('#b2s-user-network-settings-short-url').val(data.content);
                    if (jQuery("#b2s-user-network-settings-short-url").is(":checked")) {
                        jQuery('#b2s-user-network-settings-short-url').prop('checked', false);
                    } else {
                        jQuery('#b2s-user-network-settings-short-url').prop('checked', true);
                    }
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
    }
    return false;
});

jQuery(document).on('click', '.b2s-shortener-account-delete-btn', function () {

    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery(".b2s-user-settings-area").hide();
    jQuery(".b2s-loading-area").show();

    var provider_id = jQuery(this).attr('data-provider-id');
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_user_network_settings',
            'shortener_account_auth_delete': provider_id,
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery(".b2s-loading-area").hide();
            jQuery(".b2s-user-settings-area").show();
            if (data.result == true) {
                jQuery('.b2s-user-network-shortener-account-detail[data-provider-id="' + provider_id + '"]').hide();
                jQuery('.b2s-shortener-account-connect-btn[data-provider-id="' + provider_id + '"]').css('display', 'inline-block');
                jQuery('#b2s-user-network-settings-short-url').prop('checked', false);
                jQuery('#b2s-user-network-settings-short-url').val("1");
                jQuery('#b2s-user-network-shortener-state[data-provider-id="0"]').val("0");
            } else {
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
    return false;
});


jQuery(document).on('change', '#b2s-user-network-twitter-content', function () {

    if (jQuery('#b2s_user_version').val() == 0) {
        jQuery('#b2s-user-network-twitter-content').val("0");
        jQuery('#b2sPreFeatureModal').modal('show');
        return false;
    } else {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                'action': 'b2s_user_network_settings',
                'content_network_twitter': jQuery('#b2s-user-network-twitter-content').val(),
            },
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
    }
    return false;
});

jQuery('#b2s-user-network-settings-auto-post-own').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});

jQuery('#b2s-user-network-settings-auto-post-imported-own').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery('.b2s-settings-user-error-no-auth-selected').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    if (data.type == 'no-auth-selected') {
                        jQuery('.b2s-settings-user-error-no-auth-selected').show();

                    } else {
                        jQuery('.b2s-settings-user-error').show();
                    }
                }
            }
        });
        return false;
    }
});




jQuery(document).on('click', '.b2s-post-type-select-btn', function () {
    var type = jQuery(this).attr('data-post-type');
    var tempCurText = jQuery(this).text();
    if (jQuery(this).attr('data-select-toogle-state') == "0") { //0=select
        jQuery('.b2s-post-type-item-' + type).prop('checked', true);
        jQuery(this).attr('data-select-toogle-state', '1');
        if (type == 'update') {
            jQuery('.b2s-auto-post-own-update-warning').show();
        }
    } else {
        jQuery('.b2s-post-type-item-' + type).prop('checked', false);
        jQuery(this).attr('data-select-toogle-state', '0');
        if (type == 'update') {
            jQuery('.b2s-auto-post-own-update-warning').hide();
        }
    }
    jQuery(this).text(jQuery(this).attr('data-select-toogle-name'));
    jQuery(this).attr('data-select-toogle-name', tempCurText);
    return false;
});


jQuery(document).on('change', '#b2s-user-time-zone', function () {
    var curUserTime = calcCurrentExternTimeByOffset(jQuery('option:selected', this).attr('data-offset'), jQuery('#b2sUserLang').val());
    jQuery('#b2s-user-time').text(curUserTime);

    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery(".b2s-loading-area").show();
    jQuery(".b2s-user-settings-area").hide();
    jQuery('.b2s-server-connection-fail').hide();

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_user_network_settings',
            'user_time_zone': jQuery(this).val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery(".b2s-loading-area").hide();
            jQuery(".b2s-user-settings-area").show();
            if (data.result == true) {
                jQuery('.b2s-settings-user-success').show();
            } else {
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
    return false;
});
jQuery(document).on('click', '#b2s-user-network-settings-allow-shortcode', function () {
    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery(".b2s-loading-area").show();
    jQuery(".b2s-user-settings-area").hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_user_network_settings',
            'allow_shortcode': jQuery('#b2s-user-network-settings-allow-shortcode').val(),
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery(".b2s-loading-area").hide();
            jQuery(".b2s-user-settings-area").show();
            if (data.result == true) {
                jQuery('.b2s-settings-user-success').show();
                jQuery('#b2s-user-network-settings-allow-shortcode').val(data.content);
                if (jQuery("#b2s-user-network-settings-allow-shortcode").is(":checked")) {
                    jQuery('#b2s-user-network-settings-allow-shortcode').prop('checked', false);
                } else {
                    jQuery('#b2s-user-network-settings-allow-shortcode').prop('checked', true);
                }
            } else {
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
    return false;
});

jQuery(document).on('click', '#b2s-user-network-settings-allow-hashtag', function () {
    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery(".b2s-loading-area").show();
    jQuery(".b2s-user-settings-area").hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_user_network_settings',
            'allow_hashtag': jQuery('#b2s-user-network-settings-allow-hashtag').val(),
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                window.location.href = window.location.pathname + "?page=blog2social-settings&b2s-settings-user-success=true";
            } else {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
    return false;
});


jQuery(document).on('click', '#b2s-general-settings-legacy-mode', function () {
    jQuery('.b2s-settings-user-success').hide();
    jQuery('.b2s-settings-user-error').hide();
    jQuery(".b2s-loading-area").show();
    jQuery(".b2s-user-settings-area").hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_user_network_settings',
            'legacy_mode': jQuery('#b2s-general-settings-legacy-mode').val(),
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery(".b2s-loading-area").hide();
            jQuery(".b2s-user-settings-area").show();
            if (data.result == true) {
                jQuery('.b2s-settings-user-success').show();
                jQuery('#b2s-general-settings-legacy-mode').val(data.content);
                if (jQuery("#b2s-general-settings-legacy-mode").is(":checked")) {
                    jQuery('#b2s-general-settings-legacy-mode').prop('checked', false);
                } else {
                    jQuery('#b2s-general-settings-legacy-mode').prop('checked', true);
                }
            } else {
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
    return false;
});

jQuery('.b2sSaveUserSettingsPostFormatFb').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});


jQuery('.b2sSaveUserSettingsPostFormatTw').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});


jQuery('.b2sSaveUserSettingsPostFormatGp').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});


jQuery('.b2sSaveUserSettingsPostFormatIn').validate({
    ignore: "",
    errorPlacement: function () {
        return false;
    },
    submitHandler: function (form) {
        jQuery('.b2s-settings-user-success').hide();
        jQuery('.b2s-settings-user-error').hide();
        jQuery(".b2s-loading-area").show();
        jQuery(".b2s-user-settings-area").hide();
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            processData: false,
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: jQuery(form).serialize(),
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery(".b2s-loading-area").hide();
                jQuery(".b2s-user-settings-area").show();
                if (data.result == true) {
                    jQuery('.b2s-settings-user-success').show();
                } else {
                    jQuery('.b2s-settings-user-error').show();
                }
            }
        });
        return false;
    }
});



/*jQuery(document).on('change', '.b2s-user-network-settings-post-format', function () {
 jQuery('.b2s-settings-user-success').hide();
 jQuery('.b2s-settings-user-error').hide();
 jQuery('.b2s-server-connection-fail').hide();
 jQuery(".b2s-loading-area").show();
 jQuery(".b2s-user-settings-area").hide();
 
 var networkId = jQuery(this).attr("data-network-id");
 jQuery('.b2s-user-network-settings-post-format[data-network-id="' + networkId + '"]').removeClass('b2s-settings-checked');
 jQuery(this).addClass('b2s-settings-checked');
 
 jQuery.ajax({
 url: ajaxurl,
 type: "POST",
 dataType: "json",
 cache: false,
 data: {
 'action': 'b2s_user_network_settings',
 'post_format': jQuery(this).val(),
 'network_id': networkId
 },
 error: function () {
 jQuery('.b2s-server-connection-fail').show();
 return false;
 },
 success: function (data) {
 jQuery(".b2s-loading-area").hide();
 jQuery(".b2s-user-settings-area").show();
 if (data.result == true) {
 jQuery('.b2s-settings-user-success').show();
 } else {
 jQuery('.b2s-settings-user-error').show();
 }
 }
 });
 return false;
 });*/

function padDate(n) {
    return ("0" + n).slice(-2);
}

function calcCurrentExternTimeByOffset(offset, lang) {

    var UTCstring = (new Date()).getTime() / 1000;
    var neuerTimestamp = UTCstring + (offset * 3600);
    neuerTimestamp = parseInt(neuerTimestamp);
    var newDate = new Date(neuerTimestamp * 1000);
    var year = newDate.getUTCFullYear();
    var month = newDate.getUTCMonth() + 1;
    if (month < 10) {
        month = "0" + month;
    }

    var day = newDate.getUTCDate();
    if (day < 10) {
        day = "0" + day;
    }

    var mins = newDate.getUTCMinutes();
    if (mins < 10) {
        mins = "0" + mins;
    }

    var hours = newDate.getUTCHours();
    if (lang == "de") {
        if (hours < 10) {
            hours = "0" + hours;
        }
        return  day + "." + month + "." + year + " " + hours + ":" + mins;
    }
    var am_pm = "";
    if (hours >= 12) {
        am_pm = "PM";
    } else {
        am_pm = "AM";
    }

    if (hours == 0) {
        hours = 12;
    }

    if (hours > 12) {
        var newHour = hours - 12;
        if (newHour < 10) {
            newHour = "0" + newHour;
        }
    } else {
        var newHour = hours;
    }
    return year + "/" + month + "/" + day + " " + newHour + ":" + mins + " " + am_pm;
}


function wopShortener(url, name) {
    var location = encodeURI(window.location.protocol + '//' + window.location.hostname);
    window.open(url + '&location=' + location, name, "width=900,height=600,scrollbars=yes,toolbar=no,status=no,resizable=no,menubar=no,location=no,directories=no,top=20,left=20");
}

window.addEventListener('message', function (e) {
    if (e.origin == jQuery('#b2sServerUrl').val()) {
        var data = JSON.parse(e.data);
        loginSuccessShortener(data.providerId, data.displayName);
    }
});

function loginSuccessShortener(providerId, displayName) {
    jQuery('.b2s-user-network-shortener-account-detail[data-provider-id="' + providerId + '"]').css('display', 'inline-block');
    jQuery('#b2s-shortener-account-display-name[data-provider-id="' + providerId + '"]').html(displayName);
    jQuery('.b2s-shortener-account-connect-btn[data-provider-id="' + providerId + '"]').hide();
    jQuery('#b2s-user-network-settings-short-url').prop("checked", true);
    jQuery('#b2s-user-network-settings-short-url').val("0");
    jQuery('#b2s-user-network-shortener-state[data-provider-id="0"]').val("1");
}
