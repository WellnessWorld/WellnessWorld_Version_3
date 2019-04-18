jQuery.noConflict();

if (typeof wp.heartbeat !== "undefined") {
    jQuery(document).on('heartbeat-send', function (e, data) {
        data['b2s_heartbeat'] = 'b2s_listener';
    });
    wp.heartbeat.connectNow();
}

jQuery(document).on('click', '.b2s-btn-curation-continue', function () {
    jQuery('#b2s-curation-input-url-help').hide();
    var re = new RegExp(/^(https?:\/\/)+[a-zA-Z0-99ÄÖÜöäü-]+(?:\.[a-zA-Z0-99ÄÖÜöäü-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=%.]+$/);
    var url = jQuery('#b2s-curation-input-url').val();
    if (re.test(url)) {
        jQuery('#b2s-curation-input-url').removeClass('error');
        jQuery('.b2s-loading-area').show();
        jQuery('.b2s-curation-result-area').show();
        scrapeDetails(url);
    } else {
        jQuery('#b2s-curation-input-url').addClass('error');
        jQuery('#b2s-curation-input-url-help').show();
    }
    return false;
});

jQuery(document).on("keyup", "#b2s-curation-input-url", function () {
    var url = jQuery(this).val();
    jQuery(this).removeClass("error");
    jQuery('#b2s-curation-input-url-help').hide();
    if (url.length != "0") {
        if (url.indexOf("http://") == -1 && url.indexOf("https://") == -1) {
            url = "https://" + url;
            jQuery(this).val(url);
        }
    }
    return false;
});

jQuery(document).on('click', '.b2s-btn-change-url-preview', function () {
    jQuery('.b2s-curation-input-area').show();
    jQuery('.b2s-btn-curation-continue').prop("disabled", false);
    jQuery('.b2s-curation-settings-area').hide();
    jQuery('.b2s-curation-preview-area').hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery('#b2s-curation-no-auth-info').hide();
    jQuery('#b2s-curation-no-review-info').hide();
    jQuery('#b2s-curation-no-data-info').hide();
    return false;
});

jQuery(document).on('change', '#b2s-post-curation-ship-type', function () {
    if (jQuery(this).val() == 1) {
        if (jQuery(this).attr('data-user-version') == 0) {
            jQuery('#b2s-sched-post-modal').modal('show');
            jQuery(this).val('0');
            return false;
        }
    }

    if (jQuery(this).val() == 1) {
        jQuery('.b2s-post-curation-ship-date-area').show();
        jQuery('#b2s-post-curation-ship-date').prop("disabled", false);

        var today = new Date();

        if (jQuery('#b2sSelSchedDate').val() != "") {
            today.setTime(jQuery('#b2sSelSchedDate').val());
        }
        if (today.getMinutes() >= 30) {
            today.setHours(today.getHours() + 1);
            today.setMinutes(0);
        } else {
            today.setMinutes(30);
        }

        var setTodayDate = today.getFullYear() + '-' + (padDate(today.getMonth() + 1)) + '-' + padDate(today.getDate()) + ' ' + formatAMPM(today);
        if (jQuery('#b2s-post-curation-ship-date').attr('data-language') == 'de') {
            setTodayDate = padDate(today.getDate()) + '.' + (padDate(today.getMonth() + 1)) + '.' + today.getFullYear() + ' ' + padDate(today.getHours()) + ':' + padDate(today.getMinutes());
        }

        jQuery('#b2s-post-curation-ship-date').val(setTodayDate);
        jQuery('#b2s-post-curation-ship-date').b2sdatepicker({'autoClose': true, 'toggleSelected': false, 'minutesStep': 15, 'minDate':  new Date(), 'startDate': today, 'todayButton':  new Date(), 'position': 'top left'});

    } else {
        jQuery('.b2s-post-curation-ship-date-area').hide();
        jQuery('#b2s-post-curation-ship-date').prop("disabled", true);
    }
});

function scrapeDetails(url) {
    var loadSettings = true;
    if (!jQuery('.b2s-curation-settings-area').is(':empty')) {
        loadSettings = false;
    }
    jQuery('.b2s-curation-input-area').hide();
    jQuery('.b2s-curation-settings-area').hide();
    jQuery('.b2s-curation-preview-area').hide();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery('#b2s-curation-no-auth-info').hide();
    jQuery('#b2s-curation-no-review-info').hide();
    jQuery('#b2s-curation-no-data-info').hide();


    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        async: true,
        cache: true,
        data: {
            'url': url,
            'action': 'b2s_scrape_url',
            'loadSettings': loadSettings,
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            jQuery('.b2s-loading-area').hide();
            jQuery('.b2s-curation-settings-area').hide();
            jQuery('.b2s-curation-preview-area').hide();
            jQuery('#b2s-btn-curation-customize').prop("disabled", true);
            jQuery('#b2s-btn-curation-share').prop("disabled", true);
            return false;
        },
        success: function (data) {
            jQuery('.b2s-loading-area').hide();
            if (data.result == true) {
                if (loadSettings) {
                    jQuery('.b2s-curation-settings-area').html(data.settings);
                    jQuery('#b2s-post-curation-profile-select [value="0"]').prop('selected', true).trigger('change');
                }
                jQuery('.b2s-curation-settings-area').show();
                jQuery('.b2s-curation-preview-area').html(data.preview);
                jQuery('.b2s-curation-preview-area').show();
                jQuery('#b2s-btn-curation-customize').prop("disabled", false);
                jQuery('#b2s-btn-curation-share').prop("disabled", false);

                //set date + select schedulding
                if (jQuery('#b2sSelSchedDate').val() != "") {
                    jQuery('#b2s-post-curation-ship-type').val('1').trigger('change');
                }


            } else {
                if (data.preview != "") {
                    jQuery('.b2s-curation-preview-area').html(data.preview);
                    jQuery('.b2s-curation-preview-area').show();
                }
                if (data.error == "NO_PREVIEW") {
                    jQuery('.b2s-curation-input-area').show();
                    jQuery('.b2s-curation-settings-area').hide();
                    jQuery('.b2s-curation-preview-area').hide();
                    jQuery('#b2s-curation-no-review-info').show();
                    jQuery('#b2s-curation-no-auth-info').hide();
                    jQuery('#b2s-curation-no-data-info').hide();
                }
                if (data.error == "NO_AUTH") {
                    jQuery('.b2s-curation-input-area').show();
                    jQuery('.b2s-curation-settings-area').hide();
                    jQuery('.b2s-curation-preview-area').hide();
                    jQuery('#b2s-curation-no-auth-info').show();
                    jQuery('#b2s-curation-no-review-info').hide();
                    jQuery('#b2s-curation-no-data-info').hide();
                }
                jQuery('#b2s-btn-curation-customize').prop("disabled", true);
                jQuery('#b2s-btn-curation-share').prop("disabled", true);
            }
        }
    });
    return false;

}

jQuery(document).on("keyup", "#b2s-post-curation-comment", function () {
    jQuery(this).removeClass('error');
    if (jQuery(this).val().length === 0) {
        jQuery(this).addClass('error');
    }
    return false;
});

jQuery(document).on('click', '#b2s-btn-curation-share', function () {
    jQuery('#b2s-post-curation-action').val('b2s_curation_share');
    jQuery('#b2s-curation-no-data-info').hide();
    jQuery('#b2s-curation-no-auth-info').hide();

    if (jQuery('#b2s-post-curation-comment').val().length === 0) {
        jQuery('#b2s-post-curation-comment').addClass('error');
        return false;
    }
    jQuery('.b2s-curation-post-list-area').html("").hide();
    jQuery('.b2s-loading-area').show();
    jQuery('.b2s-curation-settings-area').hide();
    jQuery('.b2s-curation-preview-area').hide();

    jQuery.ajax({
        processData: false,
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: jQuery("#b2s-curation-post-form").serialize(),
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-loading-area').hide();
                jQuery('.b2s-curation-post-list-area').show();
                jQuery('.b2s-curation-post-list-area').html(data.content);
            } else {
                jQuery('.b2s-loading-area').hide();
                jQuery('.b2s-curation-post-list-area').hide();
                jQuery('.b2s-curation-settings-area').show();
                jQuery('.b2s-curation-preview-area').show();

                if (data.error == 'NO_AUTH') {
                    jQuery('#b2s-curation-no-auth-info').show();
                } else {
                    jQuery('#b2s-curation-no-data-info').show();
                }
            }
            wp.heartbeat.connectNow();
        }
    });
    return false;
});

window.addEventListener('message', function (e) {
    if (e.origin == jQuery('#b2sServerUrl').val()) {
        var data = JSON.parse(e.data);
        if (typeof data.action !== typeof undefined && data.action == 'approve') {
            jQuery('.b2s-post-item-details-message-result[data-network-auth-id="' + data.networkAuthId + '"]').html("<br><span class=\"text-success\"><i class=\"glyphicon glyphicon-ok-circle\"></i> " + jQuery("#b2sJsTextPublish").val() + " </span>");
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                cache: false,
                async: false,
                data: {
                    'action': 'b2s_update_approve_post',
                    'post_id': data.post_id,
                    'publish_link': data.publish_link,
                    'publish_error_code': data.publish_error_code,
                },
                success: function (data) {
                }
            });
        }
    }
});

function wopApprove(networkAuthId, postId, url, name) {
    var location = encodeURI(window.location.protocol + '//' + window.location.hostname);
    var win = window.open(url + '&location=' + location, name, "width=650,height=900,scrollbars=yes,toolbar=no,status=no,resizable=no,menubar=no,location=no,directories=no,top=20,left=20");
    if (postId > 0) {
        function checkIfWinClosed(intervalID) {
            if (win.closed) {
                clearInterval(intervalID);
                //Show Modal
                jQuery('.b2s-publish-approve-modal').modal('show');
                jQuery('#b2s-approve-post-id').val(postId);
                jQuery('#b2s-approve-network-auth-id').val(networkAuthId);
            }
        }
        var interval = setInterval(function () {
            checkIfWinClosed(interval);
        }, 500);
    }
}

jQuery(document).on('click', '.b2s-approve-publish-confirm-btn', function () {
    var postId = jQuery('#b2s-approve-post-id').val();
    var networkAuthId = jQuery('#b2s-approve-network-auth-id').val();
    if (postId > 0) {
        jQuery('.b2s-post-item-details-message-result[data-network-auth-id="' + networkAuthId + '"]').html("<br><span class=\"text-success\"><i class=\"glyphicon glyphicon-ok-circle\"></i> " + jQuery("#b2sJsTextPublish").val() + " </span>");
        jQuery('.b2s-publish-approve-modal').modal('hide');
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            cache: false,
            async: false,
            data: {
                'action': 'b2s_update_approve_post',
                'post_id': postId,
                'publish_link': "",
                'publish_error_code': "",
            },
            success: function (data) {
            }
        });
    }
});


jQuery(document).on('click', '#b2s-btn-curation-customize', function () {
    jQuery('#b2s-curation-no-data-info').hide();
    jQuery('#b2s-curation-no-auth-info').hide();
    jQuery('#b2s-post-curation-action').val('b2s_curation_customize');
    jQuery('.b2s-loading-area').show();
    jQuery('.b2s-curation-settings-area').hide();
    jQuery('.b2s-curation-preview-area').hide();
    jQuery.ajax({
        processData: false,
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: jQuery("#b2s-curation-post-form").serialize(),
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                window.location.href = data.redirect;
                return false;
            } else {
                jQuery('.b2s-loading-area').hide();
                jQuery('#b2s-curation-no-data-info').show();
                jQuery('.b2s-curation-settings-area').show();
                jQuery('.b2s-curation-preview-area').show();
            }

        }
    });
    return false;
});

jQuery(document).on('change', '#b2s-post-curation-profile-select', function () {
    var tos = false;
    if (jQuery('#b2s-post-curation-profile-data' + jQuery(this).val()).val() == "") {
        jQuery('#b2s-curation-no-auth-info').show();
        tos = true;
    } else {
        jQuery('#b2s-curation-no-auth-info').hide();
        //TOS Twitter Check
        var len = jQuery('#b2s-post-curation-twitter-select').children('option[data-mandant-id="' + jQuery(this).val() + '"]').length;
        if (len >= 1) {
            jQuery('.b2s-curation-twitter-area').show();
            jQuery('#b2s-post-curation-twitter-select').prop('disabled', false);
            jQuery('#b2s-post-curation-twitter-select').show();
            jQuery('#b2s-post-curation-twitter-select option').attr("disabled", "disabled");
            jQuery('#b2s-post-curation-twitter-select option[data-mandant-id="' + jQuery(this).val() + '"]').attr("disabled", false);
            jQuery('#b2s-post-curation-twitter-select option[data-mandant-id="' + jQuery(this).val() + '"]:first').attr("selected", "selected");
        } else {
            tos = true;
        }

    }
    //TOS Twitter 032018
    if (tos) {
        jQuery('.b2s-curation-twitter-area').hide();
        jQuery('#b2s-post-curation-twitter-select').prop('disabled', 'disabled');
        jQuery('#b2s-post-curation-twitter-select').hide();
    }
});



function padDate(n) {
    return ("0" + n).slice(-2);
}


function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}


