define(function (require) {
	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');

	$(document).off('click', '.events-widget-nav').on('click', '.events-widget-nav', function (e) {
		e.preventDefault();
		var $elem = $(this);
		elgg.get('ajax/view/components/calendar', {
			data: $elem.data('opts'),
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function (result) {
				$elem.closest('.events-ui-calendar-component').replaceWith($(result));
			}
		});
	});
});

