define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var moment = require('moment');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');
	
	/**
	 * @param {Object} Calendar
	 * @constructor
	 */
	var EventForm = function ($form, Calendar) {
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
	EventForm.prototype = {
		constructor: EventForm,
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

			elgg.action(self.$form.attr('action'), {
				data: self.$form.serialize(),
				beforeSend: function () {
					self.$submitBtn.prop('disabled', true).addClass('elgg-state-disabled');
					spinner.start();
				},
				complete: function () {
					self.$submitBtn.prop('disabled', false).removeClass('elgg-state-disabled');
					spinner.stop();
				},
				success: function (response) {
					if (response.status >= 0) {
						self.Calendar.$calendar.fullCalendar('refetchEvents');
						lightbox.close();
					}
					self.$form[0].reset();
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
						var suffix = moment('2015-04-' + weeknum, "YYYY MM DD").format('Do').replace(weeknum, '');
						text.push(elgg.echo('repeat_ui:repeat_monthly_by:day_of_month:weekday', [weeknum + suffix, weekday]));
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

	return EventForm;
	
});