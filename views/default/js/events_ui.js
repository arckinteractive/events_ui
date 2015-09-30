elgg.provide('elgg.events.ui');

/**
 * @param {Number} guid
 * @constructor
 */
elgg.events.ui.Calendar = function (guid) {
	this.guid = guid;
	this.$calendar = $("#js-events-ui-calendar-" + guid);
	var $form = $("#js-events-ui-form-" + guid).find('form');
	this.eventForm = new elgg.events.ui.EventForm($form, this);
	this.initialized = false;
};
/**
 * Calendar prototype
 * @type elgg.events.ui.Calendar
 */
elgg.events.ui.Calendar.prototype = {
	constructor: elgg.events.ui.Calendar,
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
		var fullCalendar = self.$calendar.fullCalendar(params);
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
				elgg.events.ui.dialog.open(response, {
					title: event.title,
                                        position: {
                                            my: 'center',
                                            at: 'center'
                                        }
				});
				var eventObj = new elgg.events.ui.Event(event.id, self);
				eventObj.init();
			},
			complete: function () {
				self.showLoading(false);
			}
		});
	},
	newEvent: function (date) {
		var self = this;
		elgg.events.ui.dialog.open(self.eventForm.$form, {
			title: elgg.echo('events:new'),
			position: {
				my: 'center top',
				at: 'center top',
				of: self.$calendar,
			},
			open: function (e, ui) {
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
			self.$calendar.addClass('elgg-state-loading');
		} else {
			self.$calendar.removeClass('elgg-state-loading');
		}
	}
};

/**
 * @param {Number} guid
 * @constructor
 */
elgg.events.ui.Event = function (guid, Calendar) {
	this.Calendar = Calendar || null;
	this.guid = guid;
	this.$event = $("#elgg-object-" + guid);
	this.$addToCalendarBtn = $('.events-ui-event-action-addtocalendar[data-guid="' + this.guid + '"]');
	this.$editBtn = $('.events-ui-event-action-edit[data-guid="' + this.guid + '"]');
	this.$cancelBtn = $('.events-ui-event-action-cancel[data-guid="' + this.guid + '"]');
	this.$cancelAllBtn = $('.events-ui-event-action-cancel-all[data-guid="' + this.guid + '"]');
};

/**
 * EventForm prototype
 * @type object
 */
elgg.events.ui.Event.prototype = {
	constructor: elgg.events.ui.Event,
	init: function () {
		var self = this;
		// Bind UI events to form elements
		self.bindUIEvents();
		self.initialized = true;
	},
	bindUIEvents: function () {
		var self = this;
                if (self.$addToCalendarBtn.data('calendarCount') > 1) {
                    self.$addToCalendarBtn.die('click').bind('click', self.loadAddToCalendarForm.bind(self));
                }
		self.$editBtn.die('click').bind('click', self.loadEditForm.bind(self));
		if (self.Calendar) {
			self.$cancelBtn.removeClass('elgg-requires-confirmation'); // removes default confirmation dialog
			self.$cancelAllBtn.removeClass('elgg-requires-confirmation');
			self.$cancelBtn.die('click').bind('click', self.cancel.bind(self));
			self.$cancelAllBtn.die('click').bind('click', self.cancelAll.bind(self));
		}
	},
	loadAddToCalendarForm: function (e) {
		var self = this;
		e.preventDefault();
		var guid = self.$addToCalendarBtn.data('guid');
		elgg.ajax('ajax/view/events_ui/ajax/picker', {
			data: {
				guid: guid
			},
			beforeSend: function () {
				elgg.events.ui.dialog.showLoader();
			},
			success: function (result) {
				elgg.events.ui.dialog.setContent($(result));
				self.$addToCalendarForm = $('.elgg-form-calendar-add-event');
				if (self.Calendar) {
					console.log(self.$addToCalendarForm.bind('submit', self.submitAddToCalendarForm.bind(self)));
				}
			},
			complete: function () {
				elgg.events.ui.dialog.hideLoader();
			}
		});
	},
	submitAddToCalendarForm: function (e) {
		e.preventDefault();
		var self = this;
		var $form = self.$addToCalendarForm;
		var data = $form.data();
		data['X-Requested-With'] = 'XMLHttpRequest';
		data['X-PlainText-Response'] = true;
		$form.ajaxSubmit({
			dataType: 'json',
			data: data,
			iframe: ($form.prop('enctype') === 'multipart/form-data'),
			beforeSend: function () {
				$form.find('input[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				elgg.events.ui.dialog.showLoader();
			},
			success: function (response) {
				if (response.status >= 0) {
					self.Calendar.$calendar.fullCalendar('refetchEvents');
					elgg.events.ui.dialog.close();
				}
				if (response.system_messages.success) {
					elgg.system_message(response.system_messages.success);
				}
				if (response.system_messages.error) {
					elgg.register_error(response.system_messages.error);
				}
			},
			complete: function () {
				$form.find('input[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
				elgg.events.ui.dialog.hideLoader();
			}
		});
	},
	loadEditForm: function (e) {
		var self = this;
		e.preventDefault();
		var guid = $(this).data('guid');
		elgg.post(self.$editBtn.attr('href'), {
			beforeSend: function () {
				elgg.events.ui.dialog.showLoader();
			},
			success: function (result) {
				var $form = $(result);
				elgg.events.ui.dialog.open($form, {
					position: {
						my: 'center center',
						at: 'center center',
						of: self.$calendar
					}
				});
				var eventForm = new elgg.events.ui.EventForm($form, self.Calendar);
				eventForm.init();
			},
			complete: function () {
				elgg.events.ui.dialog.hideLoader();
			}
		});
	},
	cancel: function (e) {
		var self = this;
		e.preventDefault();
		var confirmText = self.$cancelBtn.attr('rel') || elgg.echo('question:areyousure');
		if (!confirm(confirmText)) {
			return false;
		}
		elgg.action(self.$cancelBtn.attr('href'), {
			beforeSend: function () {
				self.Calendar.showLoading(true);
			},
			success: function (result) {
				self.Calendar.$calendar.fullCalendar('refetchEvents');
				elgg.events.ui.dialog.close();
			},
			complete: function () {
				self.Calendar.showLoading(false);
			}
		});
	},
	cancelAll: function (e) {
		var self = this;
		e.preventDefault();
		var confirmText = self.$cancelAllBtn.attr('rel') || elgg.echo('question:areyousure');
		if (!confirm(confirmText)) {
			return false;
		}
		elgg.action(self.$cancelAllBtn.attr('href'), {
			beforeSend: function () {
				self.Calendar.showLoading(true);
			},
			success: function (result) {
				self.Calendar.$calendar.fullCalendar('refetchEvents');
				elgg.events.ui.dialog.close();
			},
			complete: function () {
				self.Calendar.showLoading(false);
			}
		});
	}
};

/**
 * @param {Object} elgg.events.ui.Calendar
 * @constructor
 */
elgg.events.ui.EventForm = function ($form, Calendar) {
	this.Calendar = Calendar || null;
	this.$form = $form;
	this.$repeatChkbx = $('input[type="checkbox"][name="repeat"]', this.$form);
	this.$repeatOpts = $('.events-ui-repeat', this.$form);
	this.$remindersChkbx = $('input[type="checkbox"][name="has_reminders"]', this.$form);
	this.$remindersOpts = $('.events-ui-reminders', this.$form);
	this.$remindersAddNew = $('.js-events-ui-reminders-add', this.$form);
	this.$remindersRemove = $('.js-events-ui-reminder-remove', this.$form);
	this.$remindersTmpl = $('.js-events-ui-reminder-tmpl', this.$form);
	this.$remindersList = $('.js-events-ui-reminders-list', this.$form);
	this.$repeatFrequencyInput = $('select[name="repeat_frequency"]', this.$form);
	this.$repeatEndType = $('input[name="repeat_end_type"]', this.$form);
	this.$repeatEndAfter = $('input[name="repeat_end_after"]', this.$form);
	this.$repeatEndOn = $('input[name="repeat_end_on"]', this.$form);
	this.$allDayChkbx = $('input[type="checkbox"][name="all_day"]', this.$form);
	this.$startDateInput = $('input[name="start_date"]', this.$form);
	this.$startTimeInput = $('select[name="start_time"]', this.$form);
	this.$endDateInput = $('input[name="end_date"]', this.$form);
	this.$endTimeInput = $('select[name="end_time"]', this.$form);
	this.$datePickers = $('.events-ui-datepicker', this.$form);
	this.$submitBtn = $('input[type="submit"]', this.$form);
};
/**
 * EventForm prototype
 * @type object
 */
elgg.events.ui.EventForm.prototype = {
	constructor: elgg.events.ui.EventForm,
	init: function () {
		var self = this;
		// Bind UI events to form elements
		self.bindUIEvents();
		// Initialize datepickers
		self.initDatePickers(self.$datePickers);
		// Reset frequency related options
		self.$repeatFrequencyInput.trigger('change');
		self.initialized = true;
	},
	initNew: function (date) {
		var self = this,
				date = moment(date),
				now = moment();

		if (date.isBefore(now, 'day') || date.isAfter(now, 'day')) {
                        date.set('hour', 8).set('minute', 0);
		}
                else {
                    // same day, use an hour from now
                    date = now.clone();
                    date.add(1, 'hours');
                    
                    if (date.isAfter(now, 'day')) {
                        // we rolled over to a new day, but that's inconsistent UI
                        // to click on a day and add for the next
                        // so make it 9am in the past then since we're so close to midnight
                        date.subtract(1, 'hours').set('hour', 8).set('minute', 0);
                    }
                }

		self.init();
		// Reset start and end dates
		self.$startDateInput.val(date.format('YYYY-MM-DD'));
		self.$endDateInput.val(date.add(1, 'hours').format('YYYY-MM-DD'));

		// Reset start and end times
		self.$startTimeInput.val(date.startOf('hour').format('h:mma'));
		self.$endTimeInput.val(date.add(1, 'hours').startOf('hour').format('h:mma'));
	},
	initDatePickers: function ($datepicker) {
		var self = this;
		$datepicker.datepicker({
			dateFormat: 'yy-mm-dd', // ISO-8601
			onSelect: self.onDatePickerChange
		});
	},
	bindUIEvents: function () {
		var self = this;
//		if (self.initialized) {
//			return;
//		}

		if (self.Calendar) {
                    self.$form.bind('submit', self.saveEvent.bind(self));
		}

		self.$repeatChkbx.bind('change', self.onRepeatChange.bind(self));
		self.$remindersChkbx.bind('change', self.onRemindersEnable.bind(self));
		self.$allDayChkbx.bind('change', self.onAllDayChange.bind(self));
		self.$startDateInput.bind('change', self.onStartDateChange.bind(self));
		self.$startTimeInput.bind('change', self.onStartTimeChange.bind(self));
		self.$repeatFrequencyInput.bind('change', self.onFrequencyChange.bind(self));
		self.$repeatEndAfter.bind('focus', self.onRepeatEndAfterFocus.bind(self));
		self.$repeatEndOn.bind('focus', self.onRepeatEndOnFocus.bind(self));

		$('input,select', self.$form).bind('change', self.onChange.bind(self));

		self.$remindersAddNew.bind('click', self.addReminder.bind(self));
		//self.$remindersRemove.bind('click', self.removeReminder);
		$('a.js-events-ui-reminder-remove').bind('click', self.removeReminder);

	},
	/**
	 * Submit event form via AJAX
	 * @param {Object} e Event object
	 * @returns {void}
	 */
	saveEvent: function (e) {

		e.preventDefault();
		var self = this,
		data = self.$form.data();

		data['X-Requested-With'] = 'XMLHttpRequest';
		data['X-PlainText-Response'] = true;
		self.$form.ajaxSubmit({
			dataType: 'json',
			data: data,
			iframe: (self.$form.prop('enctype') === 'multipart/form-data'),
			beforeSend: function () {
				self.$submitBtn.prop('disabled', true).addClass('elgg-state-disabled');
				self.Calendar.showLoading(true);
			},
			success: function (response) {
				if (response.status >= 0) {
					self.Calendar.$calendar.fullCalendar('refetchEvents');
					elgg.events.ui.dialog.close();
				}
				if (response.system_messages.success) {
					elgg.system_message(response.system_messages.success);
				}
				if (response.system_messages.error) {
					elgg.register_error(response.system_messages.error);
				}
				self.$form[0].reset();
			},
			complete: function () {
				self.$submitBtn.prop('disabled', false).removeClass('elgg-state-disabled');
				self.Calendar.showLoading(false);
			}
		});
	},
	getRepeatFrequency: function () {
		return this.$repeatFrequencyInput.val();
	},
	getStartDate: function () {
		return this.$startDateInput.val();
	},
	getEndDate: function () {
		return this.$endDateInput.val();
	},
	getStartTime: function () {
		return this.$startTimeInput.val();
	},
	getEndTime: function () {
		return this.$endTimeInput.val();
	},
	isAllDay: function () {
		return this.$allDayChkbx.is(':checked');
	},
	isRecurring: function () {
		return this.$repeatChkbx.is(':checked');
	},
	hasReminders: function () {
		return this.$remindersChkbx.is(':checked');
	},
	onChange: function (e) {
		var self = this;
		self.changeRepeatLabel();
	},
	onDatePickerChange: function (dateText, instance) {
		if ($(this).is('.elgg-input-timestamp')) {
			// convert to unix timestamp
			var dateParts = dateText.split("-");
			var timestamp = Date.UTC(dateParts[0], dateParts[1] - 1, dateParts[2]);
			timestamp = timestamp / 1000;
			var id = $(this).attr('id');
			$('input[name="' + id + '"]').val(timestamp);
		}
		// trigger change event
		if (dateText !== instance.lastVal) {
			$(this).change();
		}
	},
	onRepeatChange: function (e) {
		var self = this;
		if (self.isRecurring()) {
			self.$repeatOpts.slideDown();
		} else {
			self.$repeatOpts.slideUp();
		}
	},
	onRemindersEnable: function (e) {
		var self = this;
		if (self.hasReminders()) {
			self.$remindersOpts.slideDown();
		} else {
			self.$remindersOpts.slideUp();
		}
	},
	onAllDayChange: function (e) {
		var self = this;
		if (self.isAllDay()) {
			self.$startTimeInput.hide();
			self.$endTimeInput.hide();
		} else {
			self.$startTimeInput.show();
			self.$endTimeInput.show();
		}
	},
	onStartDateChange: function (e) {
		var self = this;
		var startDate = self.getStartDate();
		var endDate = self.getEndDate();
		if (moment(startDate).isAfter(endDate)) {
			self.$endDateInput.val(startDate);
		}
		self.$endDateInput.datepicker('option', 'minDate', startDate);
	},
	onStartTimeChange: function (e) {
		var self = this;
		var startTime = self.getStartTime();
		var endTime = moment(startTime, 'h::mma').add(1, 'hours').format('h:mma');
		self.$endTimeInput.val(endTime);
	},
	onFrequencyChange: function (e) {
		var self = this;
		var frequency = self.getRepeatFrequency();
		var $matches = $('[data-frequency="' + frequency + '"]', self.$form);
		$matches.show();
		$('[data-frequency]').not($matches).hide();
	},
	onRepeatEndAfterFocus: function (e) {
		var self = this;
		var repeatEnd = self.$repeatEndAfter.data('repeatEnd');
		self.$repeatEndType.filter('[value="' + repeatEnd + '"]').prop('checked', true);
	},
	onRepeatEndOnFocus: function (e) {
		var self = this;
		var repeatEnd = self.$repeatEndOn.data('repeatEnd');
		self.$repeatEndType.filter('[value="' + repeatEnd + '"]').prop('checked', true);
	},
	changeRepeatLabel: function () {
		var self = this;
		var text = [];
		var frequency = self.getRepeatFrequency();
		var startDate = self.getStartDate();
		text.push(elgg.echo('events_ui:repeat:' + frequency));
		switch (frequency) {
			case 'monthly':
				var monthly_by = self.$form.find('[name="repeat_monthly_by"]:checked').val();
				var date = moment(startDate).date();
				if (monthly_by === 'day_of_month') {
					// Monthly on the 15th of the month
					text.push(elgg.echo('repeat_ui:repeat_monthly_by:day_of_month:date', [moment(startDate).format('Do')]));
				} else {
					// Monthly on the 2nd Thursday of the month
					var weeknum = Math.ceil(date / 7);
					var weekday = moment(startDate).format('dddd');
                                        var suffix = moment('2015-04-'+weeknum, "YYYY MM DD").format('Do').replace(weeknum, '');
					text.push(elgg.echo('repeat_ui:repeat_monthly_by:day_of_month:weekday', [weeknum+suffix, weekday]));
				}
				break;
			case 'weekly':
				// select at least one weekday
				if (!$('input[name="repeat_weekly_days[]"]:checked', self.$form).length) {
					var startDate = self.$form.find('[name="startDate"]').val();
					var ddd = moment(startDate).format('ddd');
					$('input[name="repeat_weekly_days[]"]', self.$form).prop('checked', false);
					$('input[name="repeat_weekly_days[]"][value="' + ddd + '"]', self.$form).prop('checked', true);
				}

				// Weekly on Monday, Friday
				var weekdays = [];
				$('input[name="repeat_weekly_days[]"]:checked', self.$form).each(function () {
					var weekday = $(this).val();
					weekdays.push(moment(weekday, 'ddd').format('dddd'));
				});
				text.push(elgg.echo('repeat_ui:repeat:weekly:weekday', [weekdays.join(', ')]));
				break;
		}
		$('.events-ui-repeat-text').text(text.join(' '));
	},
	addReminder: function (e) {
		e.preventDefault();
		var self = this;
		var tmpl = self.$remindersTmpl.clone(true, true).html();
		self.$remindersList.append($('<li>').addClass('js-events-ui-reminder').html(tmpl));
	},
	removeReminder: function (e) {
		e.preventDefault();
		$(this).closest('.js-events-ui-reminder').remove();
	}
};
/**
 * @constructor
 */
elgg.events.ui.DialogWindow = function () {
	this.opened = false;
};
/**
 * Dialog prototype
 * @type Object
 */
elgg.events.ui.DialogWindow.prototype = {
	constructor: elgg.events.ui.DialogWindow,
	getDefaults: function () {
		var self = this;
		return {
			width: '500px',
			dialogClass: 'events-dialog-window',
			title: '',
			modal: true,
			close: function (e, ui) {
				self.opened = false;
				$(this).remove();
			}
		};
	},
	getDialogContainer: function () {
		var $dialogContainer = $('#events-ui-dialog');
		if ($dialogContainer.length === 0) {
			$dialogContainer = $('<div id="events-ui-dialog" />');
		}
		return $dialogContainer;
	},
	open: function (content, options) {
		var self = this;
		var options = options || {};
		var params = $.extend({}, self.getDefaults(), options);
		self.opened = true;
		self.$dialog = self.getDialogContainer();
		if (content) {
			self.setContent(content);
		}
		self.$dialog.dialog(params);
	},
	isOpen: function () {
		return this.opened;
	},
	close: function () {
		var self = this;
		self.opened = false;
		self.$dialog.dialog('close');
	},
	setContent: function (content, options) {
		var self = this;
		var content = content || '';
		if (!self.isOpen()) {
			self.open(options);
		}
		self.$dialog.html(content);
	},
	showLoader: function () {
		var self = this;
		if (!self.isOpen()) {
			self.open();
		}
		if (self.$dialog.find('.elgg-ajax-loader').length === 0) {
			self.$dialog.append('<div class="elgg-ajax-loader"></div>');
		}
	},
	hideLoader: function () {
		var self = this;
		self.$dialog.find('.elgg-ajax-loader').remove();
	}
};
/**
 * Dialog windoow
 */
elgg.events.ui.dialog = new elgg.events.ui.DialogWindow();
/**
 * An array of all calendars initializedon the page
 * @type array
 */
elgg.events.ui.calendars = [];
/**
 * Callback function for Elgg's init,system event
 * @returns {void}
 */
elgg.events.ui.init = function () {

	if (typeof $.fullCalendar === 'undefined') {
		return;
	}
	$('.js-events-ui-fullcalendar').each(function () {
		var guid = $(this).data('guid');
		var calendar = new elgg.events.ui.Calendar(guid);
		calendar.init();
	});
	$('.elgg-form-events-edit:not(.events-ui-form)').each(function () {
		var eventForm = new elgg.events.ui.EventForm($(this));
		eventForm.init();
	});
	$('[data-object-event][data-guid]').each(function () {
		var Event = new elgg.events.ui.Event($(this).data('guid'));
		Event.init();
	});

	$('.js-events-ui-ical-modal-trigger').live('click', function(e) {
		e.preventDefault();
		var feed_url = $(this).attr('href');
		elgg.ajax('ajax/view/events_ui/ajax/ical_modal', {
			data: {
				feed_url: feed_url,
			},
			beforeSend: function() {
				elgg.events.ui.dialog.showLoader();
			},
			success: function(response) {
				elgg.events.ui.dialog.setContent(response);
				$('.js-events-autoselect').live('click keydown keyup focus', function(e) {
					$(this).select();
					return false;
				});
			},
			complete: function() {
				elgg.events.ui.dialog.hideLoader();
			}
		});
	});


};
elgg.register_hook_handler('init', 'system', elgg.events.ui.init);