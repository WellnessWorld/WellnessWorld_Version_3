jQuery(document).ready(function ($) {

	"use strict"

	var html = $('html'),
		body = $('body'),
		origin = decodeURIComponent(location.search.match(/origin=(.+)&/)[1]);

	$('.mailster-form-wrap')
		.on('click tap touchstart', function (event) {
			event.stopPropagation();
		});

	body
		.on('click tap touchstart', function (event) {
			event.stopPropagation();
			html.addClass('unload');
			setTimeout(function () {
				window.parent.postMessage('mailster|c', origin)
			}, 150);
		});

	$(window).on('load', function () {
		html.addClass('loaded');
		$('.mailster-wrapper').eq(0).find('input').focus().select();
	});

	$(document).keydown(function (e) {
		if (e.keyCode == 27) {
			setTimeout(function () {
				window.parent.postMessage('mailster|c', origin)
			}, 150);
			return false;
		}
	});

});