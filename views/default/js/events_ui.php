//<script>

	elgg.provide('elgg.events_ui');

	elgg.events_ui.init = function() {

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
				events: elgg.get_site_url() + 'calendar/feed/' + guid,
				eventLimit: 3,
				loading: function(isLoading, view) {
					if (isLoading) {
						elgg.events_ui.showLoader();
					}
					else {
						elgg.events_ui.dialogClose();
					}
				},
				dayClick: function(date, allDay, jsEvent, view) {
					// if we can edit the calendar create a new event
					if (parseInt($('#events-ui-calendar').attr('data-editable')) == 1) {
						elgg.events_ui.newEvent(date);
					}
					else {
						$cal.fullCalendar('gotoDate', date);
						$cal.fullCalendar('changeView', 'agendaDay');
					}
				},
				eventClick: function(event, jsEvent, view) {
					// just letting them go to their url for now
				},
				eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
					elgg.events_ui.moveEvent(event, dayDelta, minuteDelta, allDay, revertFunc);
				},
				eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
					elgg.events_ui.resizeEvent(event, dayDelta, minuteDelta, revertFunc);
				}
			});
		}

		$('input[type="checkbox"][name="repeat"]').live('change', function(e) {
			if ($(this).is(':checked')) {
				//$('.events-ui-form .events-ui-repeat').slideDown();
				$(this).closest('form').find('.events-ui-repeat').slideDown();
			}
			else {
				//$('.events-ui-form .events-ui-repeat').slideUp();
				$(this).closest('form').find('.events-ui-repeat').slideUp();
			}
		});

		$('#events-ui-dialog .elgg-form-events-edit').live('submit', elgg.events_ui.createEvent);
		
		$('.events-ui-datepicker[autoinit="1"]').each(function() {
			elgg.events_ui.initDatePicker($(this));
		});
	};


	elgg.events_ui.newEvent = function(date) {

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
			close: function(e, ui) {
				$(this).remove();
			},
			open: function(e, ui) {
				// populate with the form
				var form = $('.events-ui-add-event-form').html();
				$('.events-ui-form').html(form);
				$('.events-ui-loader').addClass('hidden');

				// initialize our datepickers
				$('.events-ui-form .events-ui-datepicker').each(function() {
					elgg.events_ui.initDatePicker($(this));
				});

				// set our dates
				$('.events-ui-form input[name="start_date"], .events-ui-form input[name="end_date"]').val(mdate.format('YYYY-MM-DD'));
				// set our times
				$('.events-ui-form select[name="start_time"], .events-ui-form select[name="end_time"]').val(mdate.format('h:mma'));
			}
		});
	};


	elgg.events_ui.initDatePicker = function(elem) {
		elem.datepicker({
			// ISO-8601
			dateFormat: 'yy-mm-dd',
			onSelect: function(dateText) {
				if ($(this).is('.elgg-input-timestamp')) {
					// convert to unix timestamp
					var dateParts = dateText.split("-");
					var timestamp = Date.UTC(dateParts[0], dateParts[1] - 1, dateParts[2]);
					timestamp = timestamp / 1000;

					var id = $(this).attr('id');
					$('input[name="' + id + '"]').val(timestamp);
				}
			}
		});
	};


// submits the create event form via ajax
	elgg.events_ui.createEvent = function(e) {
		$form = $(this);
		e.preventDefault();

		var data = {};
		data['X-Requested-With'] = 'XMLHttpRequest';
		data['X-PlainText-Response'] = true;
		$form.ajaxSubmit({
			dataType: 'json',
			data: data,
			iframe: false,
			beforeSend: function() {
				$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				$form.hide();
				$('.events-ui-loader').removeClass('hidden');
			},
			success: function(response) {
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
			complete: function() {
				$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
			}
		});
	};


	elgg.events_ui.moveEvent = function(event, dayDelta, minuteDelta, allDay, revertFunc) {
		// attempt to move the event
		elgg.action('events/move', {
			data: {
				guid: event.id,
				day_delta: dayDelta,
				minute_delta: minuteDelta,
				all_day: allDay ? 1 : 0
			},
			success: function(response) {
				if (response.status != 0) {
					// some error has occurred
					revertFunc();
				}
			},
			error: function(response) {
				revertFunc();
			}
		});
	};

	elgg.events_ui.resizeEvent = function(event, dayDelta, minuteDelta, revertFunc) {
		// attempt to move the event
		elgg.action('events/resize', {
			data: {
				guid: event.id,
				day_delta: dayDelta,
				minute_delta: minuteDelta
			},
			success: function(response) {
				if (response.status != 0) {
					// some error has occurred
					revertFunc();
				}
			},
			error: function(response) {
				revertFunc();
			}
		});
	};


	elgg.events_ui.getDialogContainer = function() {
		var dialogContainer = $('#events-ui-dialog');
		if (dialogContainer.length === 0) {
			dialogContainer = $('<div id="events-ui-dialog" />');
		}

		return dialogContainer;
	};

	elgg.events_ui.dialogClose = function() {
		$('#events-ui-dialog').dialog('close');
	};

	elgg.events_ui.showLoader = function() {
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
			close: function(e, ui) {
				$(this).remove();
			}
		});
	};

	elgg.register_hook_handler('init', 'system', elgg.events_ui.init);