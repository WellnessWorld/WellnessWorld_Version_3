jQuery.noConflict();


jQuery(document).ready(function () {
    getWidgetFaq();

});

function getWidgetFaq() {
    jQuery('.b2s-faq-area').show();
    if (typeof wp.heartbeat == "undefined") {
        jQuery('#b2s-heartbeat-fail').show();
    }
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_faq_entries'
        },
        error: function () {
            jQuery('.b2s-faq-area').hide();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-loading-area-faq').hide();
                jQuery('.b2s-faq-content').html(data.content);
            } else {
                jQuery('.b2s-faq-area').hide();
            }
        }
    });
}