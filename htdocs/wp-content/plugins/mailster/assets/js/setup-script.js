jQuery(document).ready(function ($) {

	"use strict"

	//general vars
	var wpnonce = $('#mailster_nonce').val(),
		steps = $('.mailster-setup-step'),
		currentStep, currentID,
		status = $('.status'),
		spinner = $('.spinner'),
		hash = location.hash.substr(1);


	//general stuff
	if (hash && $('#step_' + hash).length) {
		currentStep = $('#step_' + hash);
	} else {
		currentStep = steps.eq(0);
	}

	currentID = currentStep.attr('id').replace(/^step_/, '');

	steps.hide();
	_step(currentID);

	$('form.mailster-setup-step-form').on('submit', function () {
		$('.next-step:visible').hide();
		return false;
	});

	$('a.external').on('click', function () {
		if (this.href) window.open(this.href);
		return false;
	});

	$('#mailster-setup')
		.on('click', '.validation-skip-step', function () {
			return confirm(mailsterL10n.skip_validation);
		})
		.on('click', '.next-step', function () {

			if ($(this).hasClass('disabled')) return false;

			if (tinymce) tinymce.get('post_content').save();

			var form = $(this).parent().parent().find('form'),
				data = form.serialize();
			_ajax('wizard_save', {
				id: currentID,
				data: data
			}, function (response) {

			});

		})
		.on('click', '.load-language', function () {

			status.html(mailsterL10n.load_language);
			spinner.css('visibility', 'visible');
			_ajax('load_language', function (response) {

				spinner.css('visibility', 'hidden');
				status.html(response.html);
				if (response.success) {
					location.reload();
				}

			});

			return false;


		})
		.on('click', '.quick-install', function () {

			var _this = $(this);

			_install(_this.data('plugin'), _this.data('method'), _this.parent());

		})
		.on('click', '.edit-slug', function () {
			$(this).parent().parent().find('span').hide().filter('.edit-slug-area').show().find('input').focus().select();
		});

	$(document)
		.on('verified.mailster', function () {
			$('.validation-next-step').removeClass('disabled');
			$('.validation-skip-step').addClass('disabled');
		});


	_check_language();

	var deliverynav = $('#deliverynav'),
		deliverytabs = $('.deliverytab');

	deliverynav.on('click', 'a.nav-tab', function () {
		deliverynav.find('a').removeClass('nav-tab-active');
		deliverytabs.hide();
		var hash = $(this).addClass('nav-tab-active').attr('href').substr(1);
		$('#deliverymethod').val(hash);
		$('#deliverytab-' + hash).show();

		if ($('#deliverytab-' + hash).find('.quick-install').length) {
			$('.delivery-next-step').addClass('disabled').html(sprintf(mailsterL10n.enable_first, $(this).html()));
		} else {
			$('.delivery-next-step').removeClass('disabled').html(sprintf(mailsterL10n.use_deliverymethod, $(this).html()));
		}
		return false;
	});

	function _check_language() {

		status.html(mailsterL10n.check_language);
		spinner.css('visibility', 'visible');

		_ajax('check_language', function (response) {

			spinner.css('visibility', 'hidden');
			status.html(response.html);
			if (response.success) {}

		});
	}

	function _step(id) {

		var step = $('#step_' + id);

		if (step.length) {
			currentStep.hide();
			currentStep = step;
			currentStep.show();
			currentID = id;
		}

	}

	$(window).on('hashchange', function () {

		var id = location.hash.substr(1) || 'start',
			current = $('.mailster-setup-steps-nav').find("a[href='#" + id + "']"),
			next, prev;

		if (current.length) {
			_step(id);
			current.parent().parent().find('a').removeClass('next prev current');
			current.parent().prevAll().find('a').addClass('prev');
			current.addClass('current');
			if (tinymce && tinymce.activeEditor) tinymce.activeEditor.theme.resizeTo('100%', 200);
		}

		if ('finish' == id) {
			_ajax('wizard_save', {
				id: id,
				data: null
			});
		}


	}).trigger('hashchange');

	function _install(plugin, method, element, callback) {

		status.html(mailsterL10n.install_addon);
		spinner.css('visibility', 'visible');

		_ajax('quick_install', {
			plugin: plugin,
			method: method,
			step: 'install'
		}, function (response) {

			status.html(mailsterL10n.activate_addon);
			_ajax('quick_install', {
				plugin: plugin,
				method: method,
				step: 'activate'
			}, function (response) {

				status.html(mailsterL10n.receiving_content);
				_ajax('quick_install', {
					plugin: plugin,
					method: method,
					step: 'content'
				}, function (response) {

					status.html('');
					spinner.css('visibility', 'hidden');
					element.html(response.content);
					deliverynav.find('a.nav-tab-active').trigger('click');

				});

			});

		});

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