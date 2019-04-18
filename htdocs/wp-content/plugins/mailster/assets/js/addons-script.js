jQuery(document).ready(function ($) {

	"use strict"

	$('a.external').on('click', function () {
		if (this.href) window.open(this.href);
		return false;
	});

});