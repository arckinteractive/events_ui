define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');
	var EventForm = require('components/calendar/EventForm');
	require('fullcalendar');
	
	/**
	 * @param {Number} guid
	 * @constructor
	 */
	var Calendar = function (guid) {
		this.guid = guid;
		this.$calendar = $("#js-events-ui-calendar-" + guid);
		var $form = $("#js-events-ui-form-" + guid).find('form');
		this.eventForm = new EventForm($form, this);
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
			elgg.ajax('events/view/' + event.id, {
				data: {
					ts: event.start_timestamp,
					calendar: self.guid
				},
				beforeSend: function () {
					self.showLoading(true);
				},
				success: function (response) {
					lightbox.open({
						html: response,
						title: event.title,
					});
					var eventObj = new Event(event.id, self);
					eventObj.init();
				},
				complete: function () {
					self.showLoading(false);
				}
			});
		},
		newEvent: function (date) {
			var self = this;
			lightbox.open({
				html: self.eventForm.$form,
				title: elgg.echo('events:new'),
				onComplete: function () {
					self.eventForm.initNew(date);
				}
			});
		},
		moveEvent: function (event, dayDelta, minuteDelta, allDay, revertFunc) {
			// attempt to move the event
			elgg.action('events/move', {
				data: {
					guid: event.id,
					day_delta: dayDelta,
					minute_delta: minuteDelta,
					all_day: allDay ? 1 : 0
				},
				success: function (response) {
					if (response.status != 0) {
						// some error has occurred
						revertFunc();
					}
				},
				error: function (response) {
					revertFunc();
				}
			});
		},
		resizeEvent: function (event, dayDelta, minuteDelta, revertFunc) {
			// attempt to move the event
			elgg.action('events/resize', {
				data: {
					guid: event.id,
					day_delta: dayDelta,
					minute_delta: minuteDelta
				},
				success: function (response) {
					if (response.status != 0) {
						// some error has occurred
						revertFunc();
					}
				},
				error: function (response) {
					revertFunc();
				}
			});
		},
		showLoading: function (isLoading, view) {
			var self = this;
			if (isLoading) {
				spinner.start();
			} else {
				spinner.stop();
			}
		}
	};

	return Calendar;

});


