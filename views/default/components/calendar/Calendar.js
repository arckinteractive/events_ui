define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');
	var CalendarEvent = require('components/calendar/CalendarEvent');
	var CalendarEventForm = require('components/calendar/CalendarEventForm');
	require('fullcalendar');

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	/**
	 * @param {Number} guid
	 * @constructor
	 */
	var Calendar = function (guid) {
		this.guid = guid;
		this.$calendar = $("#js-events-ui-calendar-" + guid);
		this.initialized = false;
	};
	/**
	 * Calendar prototype
	 * @type Calendar
	 */
	Calendar.prototype = {
		constructor: Calendar,
		getDataSrc: function () {
			var self = this;
			return elgg.normalize_url('calendar/feed/' + self.guid + '?view=json&consumer=fullcalendar');
		},
		isEditable: function () {
			var self = this;
			return self.editable || (parseInt(self.$calendar.data('editable')) === 1);
		},
		getDefaultOptions: function () {
			var self = this;
			return {
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				editable: self.isEditable(),
				fixedWeekCount: false,
				events: {
					startParam: 'start_iso',
					endParam: 'end_iso',
					url: self.getDataSrc(),
					currentTimezone: elgg.config.timezone
				},
				ignoreTimezone: false,
				eventLimit: 3,
				loading: self.showLoading.bind(self),
				dayClick: self.dayClick.bind(self),
				eventClick: self.showEventDetails.bind(self),
				eventDrop: self.moveEvent.bind(self),
				eventResize: self.resizeEvent.bind(self),
			};
		},
		init: function (options) {
			var self = this;
			if (self.initialized) {
				return self.$calendar;
			}

			var options = options || {};
			// Merge all full calendar options
			// Data attributes on fullcalendar div take precedence over options and defaults
			var params = $.extend({}, self.getDefaultOptions(), options, self.$calendar.data());
			var fullCalendar = self.$calendar.eq(0).fullCalendar(params);
			self.fullCalendar = fullCalendar;
			self.bindUIEvents();
			self.initialized = true;
			return self.$calendar;
		},
		bindUIEvents: function () {
			var self = this;
			if (self.initialized) {
				return;
			}
		},
		dayClick: function (date, allDay, jsEvent, view) {
			var self = this;
			// if we can edit the calendar create a new event
			if (self.isEditable()) {
				self.newEvent(date);
			} else {
				self.$calendar.fullCalendar('gotoDate', date);
				self.$calendar.fullCalendar('changeView', 'agendaDay');
			}
		},
		showEventDetails: function (event, jsEvent, view) {
			var self = this;
			jsEvent.preventDefault();
			ajax.path('events/view/' + event.id, {
				data: {
					ts: event.start_timestamp,
					calendar: self.guid
				}
			}).done(function (output, statusText, jqXHR) {
				if (jqXHR.AjaxData.status === -1) {
					return;
				}
				var $output = $(output);
				lightbox.open({
					html: $output,
					width: 600,
					onComplete: function () {
						var eventObj = new CalendarEvent(event.id, self);
						eventObj.init();
					}
				});
			});
		},
		newEvent: function (date) {
			var self = this;
			ajax.path(self.$calendar.data('eventForm'))
					.done(function (output, statusText, jqXHR) {
						if (jqXHR.AjaxData.status === -1) {
							return;
						}
						var $output = $(output);
						lightbox.open({
							html: $output,
							width: 600,
							onComplete: function () {
								var $form = $output.find('form');
								var eventForm = new CalendarEventForm($form, self);
								eventForm.initNew(date);
							}
						});
					});
		},
		moveEvent: function (event, dayDelta, minuteDelta, allDay, revertFunc) {
			// attempt to move the event
			ajax.action('events/move', {
				data: {
					guid: event.id,
					day_delta: dayDelta,
					minute_delta: minuteDelta,
					all_day: allDay ? 1 : 0
				},
			}).done(function (output, statusText, jqXHR) {
				if (jqXHR.AjaxData.status === -1) {
					revertFunc();
				}
			}).fail(revertFunc);
		},
		resizeEvent: function (event, dayDelta, minuteDelta, revertFunc) {
			ajax.action('events/resize', {
				data: {
					guid: event.id,
					day_delta: dayDelta,
					minute_delta: minuteDelta
				}
			}).done(function (output, statusText, jqXHR) {
				if (jqXHR.AjaxData.status === -1) {
					revertFunc();
				}
			}).fail(revertFunc);
		},
		showLoading: function (isLoading, view) {
			if (isLoading) {
				spinner.start();
			} else {
				spinner.stop();
			}
		}
	};
	return Calendar;
});


