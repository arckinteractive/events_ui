<?php

$english = array(

	'events' => 'Events',
	'events:calendar' => 'Calendar',
	'events:calendar:all' => 'All site calendars',
	'events:calendar:mine' => 'My calendars',
	'events:calendar:owner' => '%s\'s calendars',
	'events:calendar:friends' => 'Friends\'s calendars',
	'events:calendar:group' => 'Group calendars',
	'events:calendar:none' => 'No calendars to display',
	
	'events:new' => "New Event",
	'events:edit' => 'Edit event',
	'events:add' => 'New event',
	
	// forms
	'events:edit:title:placeholder' => 'Untitled Event',
	'events:edit:label:start' => "Start",
	'events:edit:label:end' => "End",
	'events_ui:allday' => "All Day",
	'events_ui:repeat' => "Repeat",
	'events_ui:repeats' => "Repeats",
	'events_ui:repeat:daily' => 'Daily',
	'events_ui:repeat:weekday' => "Every Weekday (Monday - Friday)",
	'events_ui:repeat:dailymwf' => "Every Monday, Wednesday, and Friday",
	'events_ui:repeat:dailytt' => "Every Tuesday and Thursday",
	'events_ui:repeat:weekly' => "Weekly",
	'events_ui:repeat:monthly' => "Monthly",
	'events_ui:repeat:yearly' => "Yearly",
	'repeat_ui:repeat_ends' => "Ends",
	'events_ui:repeat_ends:never' => "Never",
	'events_ui:repeat_ends:after' => "After %s occurrences",
	'events_ui:repeat_ends:on' => "On %s",
	'events_ui:description' => 'Description',

	'repeat_ui:repeat:weekly:weekday' => 'on %s',
	'repeat_ui:repeat_monthly_by' => 'Repeat by',
	'repeat_ui:repeat_monthly_by:day_of_month' => 'Day of the month',
	'repeat_ui:repeat_monthly_by:day_of_month:date' => 'on the %s day of the month',
	'repeat_ui:repeat_monthly_by:day_of_month:weekday' => 'on the %s %s of the month',
	'repeat_ui:repeat_monthly_by:day_of_week' => 'Day of the week',
	'repeat_ui:repeat_weekly_days' => 'Repeat on',

	'events:feed:range' => 'Events between %s and %s',
	'events:feed:month' => 'Events in %s',

	'events:view:calendar' => 'Switch to calendar view',
	'events:view:feed' => 'Switch to feed view',
	'events:view:ical' => 'iCal',

	'events:no_results' => 'There are no events to display',

	'calendar:add' => 'New Calendar',
	'events:calendar:add' => 'Create a new calendar',
	'events:calendar:edit' => 'Edit calendar',
	'events:calendar:groups:enable' => 'Enable group calendar',
	'events:calendar:group' => 'Group calendars',
	'events:add_to_calendar:default' => 'Add to my calendar',
	
);

add_translation("en", $english);