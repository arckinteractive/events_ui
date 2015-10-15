define(function (require) {
	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');

	$(document).off('click', '.events-widget-nav').on('click', '.events-widget-nav', function (e) {
		var guid = $(this).data('guid');
		var start = $(this).data('start');

		elgg.get('ajax/view/widgets/events/content', {
			data: {
				guid: guid,
				event_widget_start: start
			},
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function (result) {
				$('#elgg-widget-content-' + guid).html(result);
			}
		});
	});
});

