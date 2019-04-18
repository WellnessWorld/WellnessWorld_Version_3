jQuery(document).ready(function ($) {

	"use strict"

	var _win = $(window),
		saverequired = false,
		useitnav = $('.useit-nav'),
		useittabs = $('.useit-tab'),
		ID = $('#ID').val(),
		form = $('#form_form'),
		form_builder = $('#form-builder'),
		form_structure = $('#form-structure'),
		form_fields = $('#form-fields');

	form.on('submit', function () {
		window.onbeforeunload = null;
	});


	$('#use-it')
		.on('click', function () {
			tb_show(mailsterL10n.useit, '#TB_inline?&width=' + (Math.min(1200, _win.width() - 50)) + '&height=' + (_win.height() - 100) + '&inlineId=useitbox', null);
			if (saverequired) alert(mailsterL10n.not_saved);
			return false;
		});

	useitnav.on('click', 'a', function () {
		useitnav.find('li').removeClass('active');
		useittabs.hide();
		var hash = $(this).parent().addClass('active').find('a').attr('href');
		$('#tab-' + hash.substr(1)).show();
		return false;
	});
	useittabs.hide().eq(0).show();

	form_builder
		.find(".sortable").sortable({
			containment: $('#form_form'),
			connectWith: '.sortable',
			placeholder: "ui-state-highlight",
			start: function (event, ui) {
				form_builder.addClass('dragging');
			},
			stop: function (event, ui) {
				form_builder.removeClass('dragging');
			},
			receive: function (event, ui) {
				form_structure.find('input').each(function () {
					$(this).attr('name', $(this).data('name'));
				});
				form_fields.find('input').each(function () {
					$(this).removeAttr('name');
				});
			},
			update: function (event, ui) {
				requireSave();
			},
			remove: function (event, ui) {}
		});

	form_structure
		.on('click', '.submitbutton input', function (event) {
			event.preventDefault();
			$(this).focus().select();
		})
		.on('click', '.field-remove', function (event) {
			event.preventDefault();
			var field = $(this).parent().parent().parent();
			field.appendTo(form_fields.find('ul'));
			field.find('input').removeAttr('name');


		});

	function requireSave() {
		saverequired = true;
		window.onbeforeunload = function () {
			return mailsterL10n.require_save;
		};
	}

	var style = '',
		_style,
		styleinput = $('input#style'),
		addcustomstyle = $('.add-custom-style'),
		customstyle = $('#custom-style'),
		customstyleprefix = $('#custom-style-prefix'),
		customstylesamples = $('#custom-style-samples'),
		iframe = $('#form-design-iframe'),
		timeout,
		designnav = $('.designnav'),
		designtabs = $('.designtab'),
		color_fields = $('.color-field'),
		style_fields = $('[data-selector]');

	iframe.on('load', function () {
		_style = $('<style id="mailster_form_preview_css"></style>').appendTo(iframe.contents().find('head'));
		updateStyle();


		iframe.contents().find('style.mailster-custom-form-css').remove();

	});

	addcustomstyle.on('click', function () {
		var el = $(this).parent().find('.color-field'),
			style = '.mailster-form-' + ID + ' ' + el.data('selector') + '{\n    \n}\n';
		customstyle.val(customstyle.val() + customstyleprefix.val() + style);
	});

	designnav.on('click', 'a.nav', function () {
			designnav.find('a').removeClass('nav-tab-active');
			designtabs.hide();
			var hash = $(this).addClass('nav-tab-active').attr('href');
			$('#' + hash.substr(1)).show();
			return false;
		})
		.find('a.nav').eq(0).trigger('click');


	color_fields.wpColorPicker({
		clear: function (event, ui) {
			updateStyle();
		},
		change: function (event, ui) {

			clearTimeout(timeout);
			timeout = setTimeout(function () {
				updateStyle();
				requireSave();
			}, 20);
		}
	});

	$('#themestyle').on('change', function () {
		iframe.attr('src', iframe.attr('src').replace(/&s=(1|0)/, '&s=' + ($(this).prop('checked') ? 1 : 0)));
	})

	customstyle.on('change', function () {
		updateStyle();
		requireSave();
	})
	customstylesamples.find('option').on('click', function () {
		customstyle.val(customstyle.val() + customstyleprefix.val() + '.mailster-form-' + ID + $(this).val() + "{\n    \n}\n");
		customstyle[0].scrollTop = customstyle[0].scrollHeight;
	});


	$('#title').on('change', requireSave);
	$('#form-builder').on('change', 'input', requireSave);
	$('#form-options')
		.on('change', 'input', requireSave)
		.on('change', '.mailster_userschoice', function () {

			var checked = $(this).is(':checked');
			$(this).parent().parent().parent().parent().find('legend.mailster_userschoice_td').hide().eq(checked ? 1 : 0).show();
			$(this).parent().parent().find('.mailster_dropdown').prop('disabled', !checked);
			$(this).parent().parent().parent().parent().find('.mailster_precheck').prop('disabled', (checked ? 0 : 1));

		})
		.on('change', '#redirect-cb', function () {
			$('#redirect-tf').prop('disabled', !$(this).is(':checked'));
		})
		.on('change', '.double-opt-in', function () {
			(!parseInt($(this).val(), 10)) ?
			$('#double-opt-in-field').slideUp(200): $('#double-opt-in-field').slideDown(200);
		})
		.on('change', '.vcard', function () {
			($(this).is(':checked')) ?
			$('#vcard-field').slideDown(200): $('#vcard-field').slideUp(200);
		});

	function updateStyle() {

		style = '';

		var selectors = {};

		$.each(style_fields, function () {
			var _this = $(this),
				selector = _this.data('selector'),
				property = _this.data('property'),
				postfix = _this.data('postfix') || '',
				value = _this.val();

			if (!value) return;

			selectors[selector] = selectors[selector] || {};

			selectors[selector][property] = value + postfix;

		});

		$.each(selectors, function (s, p) {

			style += 'form.mailster-form.mailster-form-' + ID + ' ' + s + '{\n';
			$.each(p, function (k, v) {
				style += '\t' + k + ': ' + v + ';\n';
			});
			style += '}\n';

		});

		style += customstyle.val().replace(/(<([^>]+)>)/ig, "");
		_style.html(style);
		styleinput.val(escape(JSON.stringify(selectors)));
		//console.log(selectors);
		iframe[0].height = iframe[0].contentWindow.document.body.scrollHeight + "px";

	}

	var buttonoptions = $('.button-options-wrap').find('input'),
		buttonpreview = $('.button-preview'),
		buttoncode = $('.code-preview').find('textarea'),
		shortcode = $('.shortcode-preview').find('input');


	buttonoptions.on('change', updateButton);
	$('.subscriber-button-style').on('change', 'input', updateButton)
	buttoncode.on('click', function () {
		$(this).select()
	});
	shortcode.on('click', function () {
		$(this).select()
	});

	$('.embed-form-input')
		.on('change', function () {
			var parent = $(this).parent().parent().parent(),
				inputs = parent.find('.embed-form-input'),
				output = parent.find('.embed-form-output');

			output.val(sprintf(output.data('embedcode'), inputs.eq(0).val(), inputs.eq(1).val(), (inputs.eq(2).is(':checked') ? '&style=1' : '')));

		}).eq(0).trigger('change');

	$('.embed-form-output')
		.on('focus', function () {
			$(this).select();
		});

	$('.form-output')
		.on('focus', function () {
			$(this).select();
		});

	$('.nav-subscriber-button')
		.on('click', function () {
			updateButton();
		});

	function updateButton() {

		var code = window.mailsterdata.embedcode,
			id = $('#ID').val(),
			width = $('#buttonwidth').val() || 480,
			scode = '[newsletter_button id=' + id,
			showcount = $('#showcount').prop('checked'),
			design = $('input[name=subscriber-button-style]:checked').val(),
			ontop = $('#ontop').prop('checked'),
			customlabel = $('input[name=buttonlabel]:checked').val() == 'custom',
			label = customlabel ? $('#buttonlabel').val() : $('#buttonlabel').attr('placeholder');

		label = label ? label.replace(/(<([^>]+)>)/ig, "") : '';
		design = design + (ontop ? ' ontop' : '');

		if (window.MailsterSubscribe && window.MailsterSubscribe.loaded) window.MailsterSubscribe.destroy();
		buttonpreview.html('');

		code = code
			.replace('%ID%', id)
			.replace('%SHOWCOUNT%', showcount ? ' data-showcount="1"' : '')
			.replace('%WIDTH%', width != 480 ? ' data-width="' + width + '"' : '')
			.replace('%DESIGN%', design != 'default' ? ' data-design="' + design + '"' : '')
			.replace('%LABEL%', label)

		buttonpreview.html(code);

		buttoncode.val(code);

		if (design && design != 'default') scode += ' design="' + design + '"';
		if (customlabel) scode += ' label="' + label + '"';
		if (showcount) scode += ' showcount="' + showcount + '"';
		if (width != 480) scode += ' width="' + width + '"';
		scode += ']';

		shortcode.val(scode);

	};

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

	window.tb_position = function () {
		if (!window.TB_WIDTH || !window.TB_HEIGHT) return;
		jQuery("#TB_window").css({
			marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
			marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
			width: TB_WIDTH + 'px'
		});
	}


});