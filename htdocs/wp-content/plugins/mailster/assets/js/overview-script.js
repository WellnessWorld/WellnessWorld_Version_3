jQuery(document).ready(function ($) {

	"use strict"

	if (typeof wp == 'undefined' || !wp.heartbeat) return;

	var current = [],
		scrolltimeout = false,
		rows = $('tr.type-newsletter');

	$('.column-status')
		.on('click', 'a.live-action', function () {

			if ($(this).hasClass('finish') && !confirm(mailsterL10n.finish_campaign)) {
				return false;
			}

			var row = $(this).parent().parent().parent().addClass('loading');

			$.get($(this).attr('href'), function () {
				setTimeout(function () {
					wp.heartbeat.connectNow();
				}, 500);
			});
			return false;

		});

	$(document)
		.on('scroll', function () {
			clearTimeout(scrolltimeout);
			scrolltimeout = setTimeout(function () {
				wp.heartbeat.connectNow();
			}, 400);
		})
		.on('heartbeat-send', function (e, data) {

			var ids = [],
				id;

			rows.each(function () {
				id = parseInt($(this).find('input').eq(0).val(), 10);
				current[id] = current[id] || {};
				if (isElementInViewport(this))
					ids.push(id);
			});

			data['mailster'] = {
				page: 'overview',
				ids: ids
			};

		})
		.on('heartbeat-tick', function (e, data) {

			var first = false;

			if (data['mailster']) {

				if (!current) {
					current = $.extend(data['mailster'], current);
					first = true;
					//return;
				}

				var change = false,
					i = 0;

				$.each(rows, function (id, row) {

					var rowdata;
					row = $(row);
					id = row.attr('id').replace('post-', '');
					row.removeClass('loading');

					if (!data['mailster'][id]) return;
					rowdata = data['mailster'][id];

					row.find('.column-status')[rowdata.cron ? 'removeClass' : 'addClass']('cron-issue');
					row.find('.campaign-status').html(rowdata.cron ? '' : rowdata.status_title);

					$.each(rowdata, function (key, value) {
						if (!first && current[id][key] == value) return;

						var statuschange = current[id] && current[id].status && (rowdata.status != current[id].status || rowdata.is_active != current[id].is_active);

						switch (key) {
						case 'status':
							if (statuschange) {
								row.removeClass('status-' + current[id].status).addClass('status-' + rowdata.status);
							}
						case 'sent':
						case 'total':
						case 'sent_formatted':
							break;
						case 'column-status':
							if (rowdata.status == 'active' && !statuschange) {
								var progress = row.find('.campaign-progress'),
									p = (rowdata.sent / rowdata.total * 100);
								progress.find('.bar').width(p + '%');
								progress.find('span').eq(1).html(rowdata.sent_formatted);
								progress.find('var').html(Math.round(p) + '%');
							}
							if (!statuschange) break;
						default:
							var el = row.find('.' + key);
							if (!el.is(':visible')) break;
							el.fadeTo(10, 0.01, function () {
								el.html(value).delay(10 * (i++)).fadeTo(200, 1);
							});

						}

						change = true;

					});


				});

				if (change) wp.heartbeat.interval('fast');
				current = $.extend(current, data['mailster']);
			}

		});

	wp.heartbeat.interval('fast');
	if (wp.heartbeat.connectNow) wp.heartbeat.connectNow();

	function isElementInViewport(el, offset) {

		var rect = el.getBoundingClientRect();

		if (!offset) offset = 0;

		//only need top and bottom
		return (
			//rect.left + offset >= 0 &&
			rect.top + offset >= 0 &&
			//rect.right - offset <= (window.innerWidth || document.documentElement.clientWidth) && /*or $(window).width() */
			rect.top - offset <= (window.innerHeight || document.documentElement.clientHeight) /*or $(window).height() */
		);
	}

});