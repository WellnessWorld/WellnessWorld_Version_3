jQuery(document).ready(function ($) {

	"use strict"

	var clipboard = new Clipboard('.clipboard');

	clipboard.on('success', function (e) {
		var html = $(e.trigger).html();
		$(e.trigger).html(mailsterClipboardL10.copied);
		setTimeout(function () {
			$(e.trigger).html(html);
			e.clearSelection();
		}, 3000);
	});

	clipboard.on('error', function (e) {});

});