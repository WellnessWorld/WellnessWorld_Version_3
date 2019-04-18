jQuery(document).ready(function ($) {

	"use strict"

	var notices = $('.mailster-notice');

	notices
		.on('click', '.notice-dismiss, .dismiss', function (event) {

			event.preventDefault();

			var el = $(this).closest('.mailster-notice'),
				id = el.data('id'),
				type = !event.altKey ? 'notice_dismiss' : 'notice_dismiss_all';

			if (event.altKey) el = notices;

			if (id) {
				_ajax(type, {
					id: id
				});
				el.fadeTo(100, 0, function () {
					el.slideUp(100, function () {
						el.remove();
					});
				})
			}
		});



	function _ajax(action, data, callback, errorCallback) {

		if ($.isFunction(data)) {
			if ($.isFunction(callback)) {
				errorCallback = callback;
			}
			callback = data;
			data = {};
		}
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: $.extend({
				action: 'mailster_' + action
			}, data),
			success: function (data, textStatus, jqXHR) {
				callback && callback.call(this, data, textStatus, jqXHR);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (textStatus == 'error' && !errorThrown) return;
				if (console) console.error($.trim(jqXHR.responseText));
				errorCallback && errorCallback.call(this, jqXHR, textStatus, errorThrown);
			},
			dataType: "JSON"
		});
	}


});