jQuery(document).ready(function ($) {

	"use strict"

	var wpnonce = $('#mailster_nonce').val(),
		isMobile = $(document.body).hasClass('mobile'),
		isWPDashboard = $(document.body).hasClass('index-php'),
		$handleButtons = $('.postbox .handlediv'),
		subscribers = $('.mailster-mb-subscribers'),
		subscriberselect = $('#mailster-subscriber-range'),
		chartelement = $('#subscriber-chart-wrap'),
		canvas = $('#subscriber-chart'),
		chart,
		ctx,

		chartoptions = {
			responsive: true,
			legend: false,
			animationEasing: "easeOutExpo",
			maintainAspectRatio: false,
			tooltips: {
				backgroundColor: 'rgba(56,56,56,0.9)',
				displayColors: false,
				cornerRadius: 2,
				caretSize: 8,
				callbacks: {
					label: function (a, b) {
						return sprintf(mailsterdashboardL10n.subscribers, _number_format(a.yLabel));
					}
				}
			},
			scales: {
				xAxes: [{
					ticks: {
						maxTicksLimit: 20
					},
					_type: "time",
					_time: {
						format: 'MM/DD/YYYY HH:mm',
						round: 'day',
						tooltipFormat: 'll HH:mm'
					}
				}],
				yAxes: [{
					ticks: {
						callback: function (value) {
							return _format(value, true);
						},
					}
				}]
			}
		};

	if (canvas.length) {
		ctx = canvas[0].getContext("2d");
	}

	subscriberselect.on('change', function () {
		drawChart();
	}).trigger('change');

	$('a.external').on('click', function () {
		if (this.href) window.open(this.href);
		return false;
	});

	if (!isWPDashboard) {
		$('.meta-box-sortables').sortable({
			placeholder: 'sortable-placeholder',
			connectWith: '.meta-box-sortables',
			items: '.postbox',
			handle: '.hndle',
			cursor: 'move',
			delay: (isMobile ? 200 : 0),
			distance: 2,
			tolerance: 'pointer',
			forcePlaceholderSize: true,
			helper: function (event, element) {
				return element.clone()
					.find(':input')
					.attr('name', function (i, currentName) {
						return 'sort_' + parseInt(Math.random() * 100000, 10).toString() + '_' + currentName;
					})
					.end();
			},
			opacity: 0.65,
			update: function (e, ui) {
				orderMetaBoxes();
			}
		});

		$('.postbox .handlediv')
			.each(function () {
				var $el = $(this);
				$el.attr('aria-expanded', !$el.parent('.postbox').hasClass('closed'));
			})
			.on('click', function () {
				var $el = $(this);
				$el.parent('.postbox').toggleClass('closed');
				$el.attr('aria-expanded', !$el.parent('.postbox').hasClass('closed'));
			});
	}


	$(document)
		.on('verified.mailster', function (event, purchasecode, username, email) {

			$('.mailster-purchasecode').html(purchasecode);
			$('.mailster-username').html(username);
			$('.mailster-email').html(email);

			$('#mailster-mb-mailster').addClass('verified');

			$('#welcome-panel').delay(2500).fadeTo(400, 0, function () {
				$('#welcome-panel').slideUp(400);
			})
		})
		.on('click', '.toggle-indicator', toggleMetaBoxes)
		.on('click', '.hide-postbox-tog', function () {

			$('#' + $(this).val())[$(this).is(':checked') ? 'show' : 'hide']().removeClass('closed');
			toggleMetaBoxes();

		})
		.on('click', '.locked', function () {
			$('.purchasecode').focus().select();
		})
		.on('click', '.check-for-update', function () {
			var _this = $(this);
			_this.html(mailsterdashboardL10n.checking);
			_ajax('check_for_update', function (response) {
				_this.html(mailsterdashboardL10n.check_again);
				if (response.success) {
					_this.closest('.postbox')[response.update ? 'addClass' : 'removeClass']('has-update');
					$('.update-version').html(response.version);
					$('.update-last-check').html(response.last_update);

				}
			});
			return false;
		})
		.on('click', '.reset-license', function () {

			if (!confirm(mailsterdashboardL10n.reset_license)) {
				return false;
			}
		});

	var metabox = (function (type) {

		if (!type) {
			return;
		}

		var current,
			box = $('.mailster-mb-' + type),
			dropdown = box.find('.mailster-mb-select'),
			label = box.find('.mailster-mb-label'),
			link = box.find('.mailster-mb-link'),
			linktmpl = link.attr('href');

		if (!dropdown.length) {
			return;
		}

		dropdown
			.on('change', function () {
				loadEntry($(this).val());
			})
			.trigger('change');

		$(document)
			.on('heartbeat-tick', function (e, data) {
				if (current) {
					loadEntry(current, true);
				}
			});

		box.find('.piechart').easyPieChart({
			animate: 1000,
			rotate: 180,
			barColor: '#2BB3E7',
			trackColor: '#f3f3f3',
			lineWidth: 9,
			size: 75,
			lineCap: 'butt',
			onStep: function (value) {
				this.$el.find('span').text(Math.round(value));
			},
			onStop: function (value) {
				this.$el.find('span').text(Math.round(value));
			}
		});

		function loadEntry(ID, silent) {

			if (!silent) {
				box.addClass('mailster-loading');
			}

			_ajax('get_dashboard_data', {
				type: type,
				id: ID
			}, function (response) {

				var data = response.data;

				link
					.html(data.name)
					.removeAttr('class')
					.addClass('mailster-mb-link')
					.attr('href', linktmpl.replace('%d', data.ID));
				if (data.status) {
					link.addClass('status-' + data.status);
				}

				box.find('.stats-total').html(data.sent_formatted);
				box.find('.stats-open').data('easyPieChart').update(data.openrate * 100);
				box.find('.stats-clicks').data('easyPieChart').update(data.clickrate * 100);
				box.find('.stats-unsubscribes').data('easyPieChart').update(data.unsubscriberate * 100);
				box.find('.stats-bounces').data('easyPieChart').update(data.bouncerate * 100);

				current = data.ID;
				box.removeClass('mailster-loading');

			});
		}

	});

	var campaignmetabox = new metabox('campaigns');
	var listmetabox = new metabox('lists');

	function drawChart(sets, scale, limit, offset) {

		subscribers.addClass('mailster-loading');

		_ajax('get_dashboard_chart', {
			range: subscriberselect.val()
		}, function (response) {

			resetChart();
			subscribers.removeClass('mailster-loading');

			if (!chart) {
				chart = new Chart(ctx, {
					type: 'line',
					data: response.chart,
					options: chartoptions
				});
			}

		});

	}

	function resetChart() {
		chart = null;
		if (canvas) {
			canvas.remove();
		}
		canvas = $('<canvas>').prependTo(chartelement);
		ctx = canvas[0].getContext("2d");
		canvas.attr({
			'width': chartelement.width(),
			'height': chartelement.height()
		});

	}

	function updateMetaBoxes() {
		orderMetaBoxes();
		toggleMetaBoxes();
	};

	function orderMetaBoxes() {

		var order = {};

		$.each($('.postbox-container'), function () {
			var col = $(this).data('id');

			$.each($(this).find('.postbox'), function () {
				if (!order[col]) {
					order[col] = [];
				}
				order[col].push(this.id);
			});

			if (order[col]) {
				order[col] = order[col].join(',');
			}

		});

		var data = {
			action: 'meta-box-order',
			_ajax_nonce: $('#meta-box-order-nonce').val(),
			page: 'newsletter_page_mailster_dashboard',
			order: order
		};

		$.post(ajaxurl, data);

	}

	function toggleMetaBoxes() {

		var hidden = $('.postbox:hidden').map(function () {
				return this.id;
			}).toArray(),
			closed = $('.postbox.closed').map(function () {
				return this.id;
			}).toArray();

		var data = {
			action: 'closed-postboxes',
			closedpostboxesnonce: $('#closedpostboxesnonce').val(),
			closed: closed.length ? closed.join(',') : '',
			hidden: hidden.length ? hidden.join(',') : '',
			page: 'newsletter_page_mailster_dashboard'
		};

		$.post(ajaxurl, data);

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

	function _format(value, konly) {

		if (value >= 1000000) {
			return (value / 1000).toFixed(1) + 'M';
		} else if (value >= 1000 && !konly) {
			return (value / 1000).toFixed(1) + 'K';
		}

		return !(value % 1) ? _number_format(value) : '';
	}

	function _number_format(number, decimals, decPoint, thousandsSep) {

		number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
		var n = !isFinite(+number) ? 0 : +number
		var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
		var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
		var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
		var s = ''
		var toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec)
			return '' + (Math.round(n * k) / k)
				.toFixed(prec)
		}
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || ''
			s[1] += new Array(prec - s[1].length + 1).join('0')
		}
		return s.join(dec)
	}

	function _ajax(action, data, callback, errorCallback, dataType) {

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
				if (textStatus == 'error' && !errorThrown) {
					return;
				}
				if (console) {
					console.error($.trim(jqXHR.responseText));
				}
				errorCallback && errorCallback.call(this, jqXHR, textStatus, errorThrown);

			},
			dataType: dataType ? dataType : "JSON"
		});
	}

});