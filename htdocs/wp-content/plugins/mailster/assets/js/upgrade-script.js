jQuery(document).ready(function ($) {

	"use strict"

	if (typeof mailster_updates == 'undefined') {
		return;
	}

	$(document).ajaxError(function () {
		_error.append('script paused...continues in 5 seconds<br>');
		setTimeout(function () {
			_error.empty();
			run(current_i, true);
		}, 5000);
	});
	var _output = $('#output'),
		_error = $('#error-list'),
		wpnonce = $('#mailster_nonce').val(),
		finished = [],
		current, current_i,
		skip = $('<span>&nbsp;</span><a class="skipbutton button button-small" href title="skip this step">skip</a>'),
		skipit = false,
		performance = mailster_updates_performance[0] || 1,
		keys = $.map(mailster_updates, function (element, index) {
			return index
		});

	function _init() {

		_output.on('click', '.skipbutton', function () {
			skipit = true;
			return false;
		});

	}

	console.log(mailster_updates_options.autostart);

	if (mailster_updates_options.autostart) {
		$('#mailster-update-process').show();
		run(0);
	} else {
		$('#mailster-update-info').show();
		$('#mailster-start-upgrade')
			.on('click', function () {
				$('#mailster-update-process').slideDown(200);
				$('#mailster-update-info').slideUp(200);
				run(0);
			});
	}


	function run(i, nooutput) {

		if (!i) {
			window.onbeforeunload = function () {
				return 'You have to finish the update before you can use Mailster!';
			};
		}

		var id = keys[i];

		current_i = i;

		if (!(current = mailster_updates[id])) {
			finish();
			return
		}

		if (!nooutput) output(id, '<strong>' + current + '</strong> ...', true);

		do_update(id, function () {
			setTimeout(function () {
				run(++i);
			}, 1000);
		}, function () {
			error();
		}, 1);

	}

	function do_update(id, onsuccess, onerror, round) {

		_ajax('batch_update', {
			id: id,
			performance: performance
		}, function (response) {

			if (response && response.success) {

				if (response.output) textoutput(response.output);

				if (skipit) {
					output(id, ' &otimes;', false);
					skipit = false;
					onsuccess && onsuccess();
				} else if (response[id]) {
					output(id, ' &#10004;', false);
					onsuccess && onsuccess();
				} else {
					output(id, '.', false, round);
					setTimeout(function () {
						do_update(id, onsuccess, onerror, ++round)
					}, 5);
				}

			} else {
				onerror && onerror();
			}

		}, function (jqXHR, textStatus, errorThrown) {

			textoutput(jqXHR.responseText);
			alert('There was an error while doing the update! Please check the textarea on the right for more info!');
			error();

		});


	}

	function error() {

		window.onbeforeunload = null;

		output('error', 'error', true);

	}

	function finish() {

		window.onbeforeunload = null;

		output('finished', '<strong>Alright, all updates have been finished!</strong>', true, 0, true);
		output('finished_button', '<a href="admin.php?page=mailster_welcome" class="button button-primary">Ok, fine!</a>', true, 0, true);

		$('#mailster-post-upgrade').show();

	}

	function output(id, content, newline, round, nobox) {

		var el = $('#output_' + id).length ?
			$('#output_' + id) :
			$('<div id="output_' + id + '" class="' + (nobox ? '' : 'updated inline') + '" style="padding: 0.5em 6px;word-wrap: break-word;"></div>').appendTo(_output);


		el.append(content);
		round > 20 ? el.append(skip.show()) : skip.hide();

	}

	function textoutput(content) {

		var curr_content = $('#textoutput').val();

		content = content + "\n\n" + curr_content;

		$('#textoutput').val($.trim(content));

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

	_init();

});