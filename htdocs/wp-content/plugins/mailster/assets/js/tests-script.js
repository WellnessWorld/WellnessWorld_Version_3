jQuery(document).ready(function ($) {

	"use strict"

	var wpnonce = $('#mailster_nonce').val(),
		start_button = $('.start-test'),
		output = $('.tests-output'),
		textoutput = $('.tests-textoutput'),
		tests = $('.tests-wrap'),
		testinfo = $('.test-info'),
		progress = $('#progress'),
		progressbar = progress.find('.bar'),
		outputnav = $('#outputnav'),
		outputtabs = $('.subtab'),
		errors, tests_run;

	$('a.external').on('click', function () {
		if (this.href) window.open(this.href);
		return false;
	});

	start_button.on('click', function () {
		start_button.hide();
		progress.show();
		output.empty();
		textoutput.val(textoutput.data('pretext'));
		tests_run = 1;
		test();
		errors = {
			'error': 0,
			'warning': 0,
			'notice': 0,
			'success': 0,
		};
		return false;
	});

	output.on('click', 'a', function () {
		if (this.href) window.open(this.href);
		return false;
	});

	tests
		.on('change', 'input', function () {
			($(this).is(':checked')) ? tests.removeClass('no-' + $(this).data('type')): tests.addClass('no-' + $(this).data('type'));
		});

	outputnav.on('click', 'a.nav-tab', function () {
		outputnav.find('a').removeClass('nav-tab-active');
		outputtabs.hide();
		var hash = $(this).addClass('nav-tab-active').attr('href');
		location.hash = hash;
		$('#subtab-' + hash.substr(1)).show();
		if (hash == '#systeminfo') {
			var textarea = $('#system_info_content');
			if ($.trim(textarea.val())) return;
			textarea.val('...');
			_ajax('get_system_info', function (response) {

				if (response.log && console)
					console.log(response.log);
				textarea.val(response.msg);
			});
		}
		return false;
	});


	if (/autostart/.test(location.search)) {
		start_button.trigger('click');
	} else {
		(location.hash && outputnav.find('a[href="' + location.hash + '"]').length) ?
		outputnav.find('a[href="' + location.hash + '"]').trigger('click'): outputnav.find('a').eq(0).trigger('click');
	}

	function test(test_id) {

		_ajax('test', {
			'test_id': test_id,
		}, function (response) {

			errors['error'] += response.errors.error;
			errors['warning'] += response.errors.warning;
			errors['notice'] += response.errors.notice;
			errors['success'] += response.errors.success;

			$(response.message.html).appendTo(output);
			textoutput.val(textoutput.val() + response.message.text);

			if (response.nexttest) {
				progressbar.width(((++tests_run) / response.total * 100) + '%');
				testinfo.html(sprintf(mailsterL10n.running_test, tests_run, response.total, response.current));
			} else {
				progressbar.width('100%');
				setTimeout(function () {
					start_button.html(mailsterL10n.restart_test).show();
					progress.hide();
					progressbar.width(0);
					testinfo.html(sprintf(mailsterL10n.tests_finished, errors.error, errors.warning, errors.notice));
				}, 500);
			}

			if (response.nexttest) {
				setTimeout(function () {
					test(response.nexttest);
				}, 100);
			} else {}

		}, function (jqXHR, textStatus, errorThrown) {});
	}

	function sprintf() {
		var a = Array.prototype.slice.call(arguments),
			str = a.shift(),
			total = a.length,
			reg;
		for (var i = 0; i < total; i++) {
			reg = new RegExp('%(' + (i + 1) + '\\$)?(s|d|f)');
			str = str.replace(reg, a[i]);
		}
		return str;
	}

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
				action: 'mailster_' + action,
				_wpnonce: wpnonce
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