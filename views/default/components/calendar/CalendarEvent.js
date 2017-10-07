define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');
	var CalendarEventForm = require('components/calendar/CalendarEventForm');

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	/**
	 * @param {Number} guid
	 * @constructor
	 */
	var CalendarEvent = function (guid, Calendar) {
		this.Calendar = Calendar || null;
		this.guid = guid;
		this.$event = $("#elgg-object-" + guid);
		this.$addToCalendarBtn = $('.events-ui-event-action-addtocalendar[data-guid="' + this.guid + '"]');
		this.$editBtn = $('.events-ui-event-action-edit[data-guid="' + this.guid + '"]');
		this.$cancelBtn = $('.events-ui-event-action-cancel[data-guid="' + this.guid + '"]');
		this.$cancelAllBtn = $('.events-ui-event-action-cancel-all[data-guid="' + this.guid + '"]');
	};

	/**
	 * CalendarEvent prototype
	 * @type object
	 */
	CalendarEvent.prototype = {
		constructor: CalendarEvent,
		init: function () {
			var self = this;
			// Bind UI events to form elements
			self.bindUIEvents();
			self.initialized = true;
		},
		bindUIEvents: function () {
			var self = this;
			if (self.$addToCalendarBtn.data('calendarCount') > 1) {
				self.$addToCalendarBtn.off('click').bind('click', self.loadAddToCalendarForm.bind(self));
			}
			self.$editBtn.off('click').bind('click', self.loadEditForm.bind(self));
			if (self.Calendar) {
				self.$cancelBtn.removeClass('elgg-requires-confirmation'); // removes default confirmation dialog
				self.$cancelAllBtn.removeClass('elgg-requires-confirmation');
				self.$cancelBtn.off('click').bind('click', self.cancel.bind(self));
				self.$cancelAllBtn.off('click').bind('click', self.cancelAll.bind(self));
			}
		},
		loadAddToCalendarForm: function (e) {
			var self = this;
			e.preventDefault();
			var guid = self.$addToCalendarBtn.data('guid');
			ajax.path('ajax/view/events_ui/ajax/picker', {
				data: {
					guid: guid
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
						self.$addToCalendarForm = $('.elgg-form-calendar-add-event');
						if (self.Calendar) {
							self.$addToCalendarForm.bind('submit', self.submitAddToCalendarForm.bind(self));
						}
					}
				});
			});
		},
		submitAddToCalendarForm: function (e) {
			e.preventDefault();
			var self = this;
			var $form = self.$addToCalendarForm;

			ajax.action($form.attr('action'), {
				data: ajax.objectify($form),
				beforeSend: function () {
					$form.find('input[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				},
				complete: function () {
					$form.find('input[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
				}
			}).done(function (output, statusText, jqXHR) {
				if (jqXHR.AjaxData.status === -1) {
					return;
				}
				self.Calendar.$calendar.fullCalendar('refetchEvents');
				lightbox.close();
			});
		},
		loadEditForm: function (e) {
			var self = this;
			e.preventDefault();
			ajax.path(self.$editBtn.attr('href'))
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
								var eventForm = new CalendarEventForm($form, self.Calendar);
								eventForm.init();
							}
						});
					});
		},
		cancel: function (e) {
			var self = this;
			e.preventDefault();
			var confirmText = self.$cancelBtn.attr('rel') || elgg.echo('question:areyousure');
			if (!confirm(confirmText)) {
				return false;
			}
			ajax.action(self.$cancelBtn.attr('href'))
					.done(function (output, statusText, jqXHR) {
						if (jqXHR.AjaxData.status === -1) {
							return;
						}
						self.Calendar.$calendar.fullCalendar('refetchEvents');
						lightbox.close();
					});
		},
		cancelAll: function (e) {
			var self = this;
			e.preventDefault();
			var confirmText = self.$cancelAllBtn.attr('rel') || elgg.echo('question:areyousure');
			if (!confirm(confirmText)) {
				return false;
			}
			ajax.action(self.$cancelAllBtn.attr('href'))
					.done(function (output, statusText, jqXHR) {
						if (jqXHR.AjaxData.status === -1) {
							return;
						}
						self.Calendar.$calendar.fullCalendar('refetchEvents');
						lightbox.close();
					});
		}
	};

	return CalendarEvent;
});


