jQuery(document).ready(function ($) {

	"use strict"

	window.Mailster = window.Mailster || {};

	window.Mailster.dialog = window.Mailster.dialog || function (id, callback, cancelcallback) {

		var api,
			current,
			dialog = $('.mailster-' + id);

		if (!dialog.length) return false;

		dialog
			.one('click', function (event) {
				event.stopPropagation();
				_cancel();
			})
			.one('click', '.notification-dialog-dismiss', function (event) {
				event.stopPropagation();
				_cancel();
			})
			.one('click', '.notification-dialog-submit', function (event) {
				event.stopPropagation();
				_submit();
			});

		function _cancel() {
			cancelcallback && cancelcallback.apply(api);
			_close();
		}

		function _submit() {
			callback && callback.apply(api);
			_close();
		}

		function _close() {
			dialog.addClass('hidden');
			$(document).off('keyup.mailster_dialog');
			current = null;
		}

		function _open() {
			current = id;
			dialog.removeClass('hidden');
			$(document)
				.on('keyup.mailster_dialog', function (event) {
					if (event.which == 27) {
						_cancel();
					}
				});
		}

		_open();

		api = {
			'current': current,
			'close': _close,
		}

		return api;

	};

});