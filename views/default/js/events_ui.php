//<script>

	elgg.provide('elgg.events_ui');

	elgg.events_ui.init = function () {

		var guid = $('#events-ui-calendar').attr('data-guid');
		var $cal = $('#events-ui-calendar');

		if ($cal.length) {
			$cal.fullCalendar({
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				editable: (parseInt($cal.attr('data-editable')) === 1),
				fixedWeekCount: false,
				events: elgg.get_site_url() + 'calendar/feed/' + guid + '?view=json',
				eventLimit: 3,
				loading: function (isLoading, view) {
					if (isLoading) {
						elgg.events_ui.showLoader();
					}
					else {
						elgg.events_ui.dialogClose();
					}
				},
				dayClick: function (date, allDay, jsEvent, view) {
					// if we can edit the calendar create a new event
					if (parseInt($('#events-ui-calendar').attr('data-editable')) == 1) {
						elgg.events_ui.newEvent(date);
					}
					else {
						$cal.fullCalendar('gotoDate', date);
						$cal.fullCalendar('changeView', 'agendaDay');
					}
				},
				eventClick: function (event, jsEvent, view) {
					// just letting them go to their url for now
				},
				eventDrop: function (event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
					elgg.events_ui.moveEvent(event, dayDelta, minuteDelta, allDay, revertFunc);
				},
				eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
					elgg.events_ui.resizeEvent(event, dayDelta, minuteDelta, revertFunc);
				}
			});
		}

		$('input[type="checkbox"][name="repeat"]').live('change', function (e) {
			if ($(this).is(':checked')) {
				//$('.events-ui-form .events-ui-repeat').slideDown();
				$(this).closest('form').find('.events-ui-repeat').slideDown();
			}
			else {
				//$('.events-ui-form .events-ui-repeat').slideUp();
				$(this).closest('form').find('.events-ui-repeat').slideUp();
			}
		});

		$('input[type="checkbox"][name="all_day"]').live('change', function (e) {
			if ($(this).is(':checked')) {
				$(this).closest('form').find('.events-ui-time').hide();
			}
			else {
				$(this).closest('form').find('.events-ui-time').show();
			}
		});

		$('select[name="start_time"]').live('change', function (e) {
			var $form = $(this).closest('form');
			var val = moment($(this).val(), 'h::mma').add(1, 'hours').format('h:mma');
			$('select[name="end_time"]').val(val);
		});

		$('select[name="repeat_frequency"]').live('change', function (e) {
			var frequency = $(this).val();
			var selector = '[data-frequency="' + frequency + '"]';

			var $matches = $(selector);
			$matches.show();
			$('[data-frequency]').not($matches).hide();
		}).trigger('change');

		$('#events-ui-dialog .elgg-form-events-edit').live('submit', elgg.events_ui.createEvent);

		$('.events-ui-datepicker[autoinit="1"]').each(function () {
			elgg.events_ui.initDatePicker($(this));
		});

		$('.events-ui-repeat').live('change.eventsui', function () {
			var text = [];
			var $form = $(this).closest('form');
			var frequency = $form.find('select[name="repeat_frequency"]').val();
			var start_date = $form.find('[name="start_date"]').val();

			text.push(elgg.echo('events_ui:repeat:' + frequency));

			switch (frequency) {
				case 'monthly' :
					var monthly_by = $form.find('[name="repeat_monthly_by"]:checked').val();
					var date = moment(start_date).date();

					if (monthly_by === 'day_of_month') {
						// Monthly on the 15th of the month
						text.push(elgg.echo('repeat_ui:repeat_monthly_by:day_of_month:date', [moment(start_date).format('Do')]));
					} else {
						// Monthly on the 2nd Thursday of the month
						var weeknum = Math.ceil(date / 7);
						var weekday = moment(start_date).format('dddd');
						text.push(elgg.echo('repeat_ui:repeat_monthly_by:day_of_month:weekday', [weeknum, weekday]));
					}
					break;
				case 'weekly' :
					// select at least one weekday
					if (!$('input[name="repeat_weekly_days[]"]:checked', $form).length) {
						var start_date = $form.find('[name="start_date"]').val();
						var ddd = moment(start_date).format('ddd');
						$('input[name="repeat_weekly_days[]"]', $form).prop('checked', false);
						$('input[name="repeat_weekly_days[]"][value="' + ddd + '"]', $form).prop('checked', true);
					}

					// Weekly on Monday, Friday
					var weekdays = [];
					$('input[name="repeat_weekly_days[]"]:checked', $form).each(function () {
						var weekday = $(this).val();
						weekdays.push(moment(weekday, 'ddd').format('dddd'));
					});
					text.push(elgg.echo('repeat_ui:repeat:weekly:weekday', [weekdays.join(', ')]));
					break;
			}
			$('.events-ui-repeat-text').text(text.join(' '));
		}).trigger('change');

		$('input,select', '.elgg-form-events-edit').live('change', function (e) {
			$('.events-ui-repeat').trigger('change');
		});
	};


	elgg.events_ui.newEvent = function (date) {

		var mdate = moment(date);

		var dialogContainer = elgg.events_ui.getDialogContainer();

		dialogContainer.html('<div class="events-ui-form"></div><div class="elgg-ajax-loader events-ui-loader"></div>');
		dialogContainer.dialog({
			width: '550px',
			dialogClass: 'events-dialog-window',
			title: elgg.echo('events:new'),
			modal: true,
			position: {
				my: 'center top',
				at: 'center top',
				of: '#events-ui-calendar'
			},
			close: function (e, ui) {
				$(this).remove();
			},
			open: function (e, ui) {
				// populate with the form
				var form = $('.events-ui-add-event-form').html();
				$('.events-ui-form').html(form);
				$('.events-ui-loader').addClass('hidden');

				// initialize our datepickers
				$('.events-ui-form .events-ui-datepicker').each(function () {
					elgg.events_ui.initDatePicker($(this));
				});

				// set our dates
				$('.events-ui-form input[name="start_date"], .events-ui-form input[name="end_date"]').val(mdate.format('YYYY-MM-DD'));
				// set our times
				$('.events-ui-form select[name="start_time"], .events-ui-form select[name="end_time"]').val(mdate.format('h:mma'));
				// reset extras
				$('select[name="repeat_frequency"]').trigger('change');
			}
		});
	};


	elgg.events_ui.initDatePicker = function (elem) {

		elem.datepicker({
			// ISO-8601
			dateFormat: 'yy-mm-dd',
			onSelect: function (dateText) {
				if ($(this).is('.elgg-input-timestamp')) {
					// convert to unix timestamp
					var dateParts = dateText.split("-");
					var timestamp = Date.UTC(dateParts[0], dateParts[1] - 1, dateParts[2]);
					timestamp = timestamp / 1000;

					var id = $(this).attr('id');
					$('input[name="' + id + '"]').val(timestamp);
				}

				$('.events-ui-repeat').trigger('change');
			}
		});

	};


// submits the create event form via ajax
	elgg.events_ui.createEvent = function (e) {
		$form = $(this);
		e.preventDefault();

		var data = {};
		data['X-Requested-With'] = 'XMLHttpRequest';
		data['X-PlainText-Response'] = true;
		$form.ajaxSubmit({
			dataType: 'json',
			data: data,
			iframe: false,
			beforeSend: function () {
				$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				$form.hide();
				$('.events-ui-loader').removeClass('hidden');
			},
			success: function (response) {
				if (response.status >= 0) {

					elgg.events_ui.dialogClose();

					$('#events-ui-calendar').fullCalendar('refetchEvents');

				}
				if (response.system_messages.success) {
					elgg.system_message(response.system_messages.success);
				}
				if (response.system_messages.error) {
					elgg.register_error(response.system_messages.error);
					$form.show();
					$('.events-ui-loader').addClass('hidden');
				}
			},
			complete: function () {
				$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
			}
		});
	};


	elgg.events_ui.moveEvent = function (event, dayDelta, minuteDelta, allDay, revertFunc) {
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
	};

	elgg.events_ui.resizeEvent = function (event, dayDelta, minuteDelta, revertFunc) {
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
	};


	elgg.events_ui.getDialogContainer = function () {
		var dialogContainer = $('#events-ui-dialog');
		if (dialogContainer.length === 0) {
			dialogContainer = $('<div id="events-ui-dialog" />');
		}

		return dialogContainer;
	};

	elgg.events_ui.dialogClose = function () {
		$('#events-ui-dialog').dialog('close');
	};

	elgg.events_ui.showLoader = function () {
		var dialogContainer = elgg.events_ui.getDialogContainer();
		dialogContainer.html('<div class="elgg-ajax-loader"></div>');
		dialogContainer.dialog({
			width: '200px',
			dialogClass: 'no-close',
			modal: false,
			draggable: false,
			resizable: false,
			title: elgg.echo('loading...'),
			position: {
				my: 'center',
				at: 'center',
				of: '#events-ui-calendar'
			},
			close: function (e, ui) {
				$(this).remove();
			}
		});
	};

	elgg.register_hook_handler('init', 'system', elgg.events_ui.init);