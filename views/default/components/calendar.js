define(function (require) {

	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');

	var Calendar = require('components/calendar/Calendar');
	var Event = require('components/calendar/CalendarEvent');
	var EventForm = require('components/calendar/CalendarEventForm');

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

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
		ajax.view('events_ui/ajax/ical_modal', {
			data: {
				feed_url: this.href
			}
		}).done(function (output, statusText, jqXHR) {
			if (jqXHR.AjaxData.status === -1) {
				return;
			}
			lightbox.open({
				html: output
			});
		});
	});

});
