jQuery(document).ready(function ($) {

	"use strict"

	var bulk_update_info, form_submitted = false,
		count, per_page;

	$('#subscribers-overview-form')
		.on('change', '#cb-select-all-1, #cb-select-all-2', function () {

			var $input = $('#all_subscribers'),
				label = $input.data('label'),
				subscriber_cb = $('.subscriber_cb');

			count = $input.data('count');
			per_page = subscriber_cb.length;
			if ($(this).is(':checked') && count > $('#the-list').find('tr').length && confirm(label)) {
				subscriber_cb.prop('disabled', true);
				$input.val(1);
			} else {
				$input.val(0);
				subscriber_cb.prop('disabled', false);
			}
		})
		.on('submit', function (event) {
			var $this = $(this),
				$input = $('#all_subscribers');

			if (1 == $input.val()) {
				event.preventDefault();

				if (form_submitted) return;
				form_submitted = true;

				window.onbeforeunload = function () {
					return mailsterL10n.onbeforeunload;
				};

				bulk_update_info = $('<div class="alignright bulk-update-info spinner">' + mailsterL10n.initprogess + '</div>').prependTo('.bulkactions');

				do_batch($this.serialize(), 0, function () {
					bulk_update_info.removeClass('spinner');
					window.onbeforeunload = null;
					setTimeout(function () {
						location.reload();
					}, 1000);
				});


			}

		});

	function do_batch(data, page, cb) {
		if (!page) page = 0;

		$.post(location.href, {
			'all_subscribers': true,
			'post_data': data,
			'page': page,
			'per_page': per_page,
			'count': count,
		}, function (response) {

			bulk_update_info.html(response.message);
			if (response.success_message) console.log(response.success_message);
			if (response.error_message) console.error(response.error_message);

			if (!response.finished) {
				setTimeout(function () {
					do_batch(data, response.page, cb);
				}, response.delay ? response.delay : 300);
			} else {
				cb && cb();
			}

		});

	}

});