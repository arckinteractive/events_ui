define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');

	var Calendar = require('components/calendar/Calendar');
	var Event = require('components/calendar/Event');
	var EventForm = require('components/calendar/EventForm');

	$('.js-events-ui-fullcalendar').each(function () {
		var guid = $(this).data('guid');
		var calendarInst = new Calendar(guid);
		calendarInst.init();
	});
	$('.elgg-form-events-edit:not(.events-ui-form)').each(function () {
		var eventFormInst = new EventForm($(this));
		eventFormInst.init();
	});
	$('[data-object-event][data-guid]').each(function () {
		var eventInst = new Event($(this).data('guid'));
		eventInst.init();
	});

	$(document).on('click keydown keyup focus', '.js-events-autoselect', function (e) {
		$(this).select();
		return false;
	});

	$(document).on('click', '.js-events-ui-ical-modal-trigger', function (e) {
		e.preventDefault();
		var feed_url = $(this).attr('href');
		elgg.ajax('ajax/view/events_ui/ajax/ical_modal', {
			data: {
				feed_url: feed_url,
			},
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function (response) {
				lightbox.open({
					html: response
				});
			}
		});
	});
	
});
