jQuery(document).ready(function ($) {

	"use strict"

	$.each($('.mailster-conditions'), function () {

		var _self = $(this),
			conditions = _self.find('.mailster-conditions-wrap'),
			groups = _self.find('.mailster-condition-group'),
			cond = _self.find('.mailster-condition');

		groups.eq(0).appendTo(_self.find('.mailster-condition-container'));
		datepicker();

		_self
		// .on('change', '.mailster-list-operator', function () {
		// 	conditions.removeClass('mailster-condition-operator-is-and mailster-condition-operator-is-or').addClass('mailster-condition-operator-is-' + $(this).val().toLowerCase());
		// 	_trigger('updateCount');
		// })
			.on('click', '.add-condition', function () {
				var id = groups.length,
					clone = groups.eq(0).clone();

				clone.removeAttr('id').appendTo(conditions).data('id', id).show();
				$.each(clone.find('input, select'), function () {
					var _this = $(this),
						name = _this.attr('name');
					_this.attr('name', name.replace(/\[\d+\]/, '[' + id + ']')).prop('disabled', false);
				});
				clone.find('.condition-field').val('').focus();
				//clone.find('select.select2').select2();
				datepicker();
				groups = _self.find('.mailster-condition-group');
				cond = _self.find('.mailster-condition');
			})
			.on('click', '.add-or-condition', function () {
				var cont = $(this).parent(),
					id = cont.find('.mailster-condition').last().data('id'),
					clone = cond.eq(0).clone();

				clone.removeAttr('id').appendTo(cont).data('id', ++id);
				$.each(clone.find('input, select'), function () {
					var _this = $(this),
						name = _this.attr('name');
					_this.attr('name', name.replace(/\[\d+\]\[\d+\]/, '[' + cont.data('id') + '][' + id + ']')).prop('disabled', false);
				});
				clone.find('.condition-field').val('').focus();
				//clone.find('select.select2').select2();
				datepicker();
				cond = _self.find('.mailster-condition');
			});

		conditions
			.on('click', '.remove-condition', function () {
				var c = $(this).parent();
				if (c.parent().find('.mailster-condition').length == 1) {
					c = c.parent();
				}
				c.slideUp(100, function () {
					$(this).remove();
					_trigger('updateCount');
				});
			})
			.on('change', '.condition-field', function () {

				var condition = $(this).closest('.mailster-condition'),
					field = $(this).val(),
					operator_field, value_field;

				condition.find('div.mailster-conditions-value-field').removeClass('active').find('.condition-value').prop('disabled', true);
				condition.find('div.mailster-conditions-operator-field').removeClass('active').find('.condition-operator').prop('disabled', true);

				value_field = condition.find('div.mailster-conditions-value-field[data-fields*=",' + field + ',"]').addClass('active').find('.condition-value').prop('disabled', false);
				operator_field = condition.find('div.mailster-conditions-operator-field[data-fields*=",' + field + ',"]').addClass('active').find('.condition-operator').prop('disabled', false);

				if (!value_field.length) {
					value_field = condition.find('div.mailster-conditions-value-field-default').addClass('active').find('.condition-value').prop('disabled', false);
				}
				if (!operator_field.length) {
					operator_field = condition.find('div.mailster-conditions-operator-field-default').addClass('active').find('.condition-operator').prop('disabled', false);
				}

				if (!value_field.val()) {
					if (value_field.is('.hasDatepicker')) {
						value_field.datepicker("setDate", "yy-mm-dd");;
					}
				}

				_trigger('updateCount');

			})
			.on('change', '.condition-operator', function () {
				_trigger('updateCount');
			})
			.on('change', '.condition-value', function () {
				_trigger('updateCount');
			})
			.on('click', '.mailster-condition-add-multiselect', function () {
				$(this).parent().clone().insertAfter($(this).parent()).find('.condition-value').select().focus();
				return false;
			})
			.on('click', '.mailster-condition-remove-multiselect', function () {
				$(this).parent().remove();
				_trigger('updateCount');
				return false;
			})
			.on('change', '.mailster-conditions-value-field-multiselect > .condition-value', function () {
				if (0 == $(this).val() && $(this).parent().parent().find('.condition-value').size() > 1) $(this).parent().remove();
			})
			.on('click', '.mailster-rating > span', function (event) {
				var _this = $(this),
					_prev = _this.prevAll(),
					_all = _this.siblings();
				_all.removeClass('enabled');
				_prev.add(_this).addClass('enabled');
				_this.parent().parent().find('.condition-value').val((_prev.length + 1) / 5).trigger('change');
			})
			.find('.condition-field').prop('disabled', false).trigger('change');

		_trigger('updateCount');

		function datepicker() {
			conditions.find('.datepicker').datepicker({
				dateFormat: 'yy-mm-dd',
				firstDay: mailsterL10n.start_of_week,
				showWeek: true,
				dayNames: mailsterL10n.day_names,
				dayNamesMin: mailsterL10n.day_names_min,
				monthNames: mailsterL10n.month_names,
				prevText: mailsterL10n.prev,
				nextText: mailsterL10n.next,
				showAnim: 'fadeIn',
			});
		}

	});

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

	function _trigger() {
		if (!window.Mailster) return;
		var args = jQuery.makeArray(arguments);
		var triggerevent = args.shift();
		window.Mailster.trigger(triggerevent, args);
	}


});