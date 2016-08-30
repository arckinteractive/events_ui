define(function (require) {
	var $ = require('jquery');

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	$(document).off('click', '.events-widget-nav').on('click', '.events-widget-nav', function (e) {
		e.preventDefault();
		var $elem = $(this);
		ajax.path('ajax/view/components/calendar', {
			data: $elem.data('opts'),
		}).done(function (output, statusText, jqXHR) {
			if (jqXHR.AjaxData.status === -1) {
				return;
			}
			$elem.closest('.events-ui-calendar-component').replaceWith($(output));
		});
	});
});

