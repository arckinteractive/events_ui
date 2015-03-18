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
	'events:edit:label:location' => 'Location',
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
	'events_ui:byline' => 'By %s',

	'repeat_ui:repeat:weekly:weekday' => 'on %s',
	'repeat_ui:repeat_monthly_by' => 'Repeat by',
	'repeat_ui:repeat_monthly_by:day_of_month' => 'Day of the month',
	'repeat_ui:repeat_monthly_by:day_of_month:date' => 'on the %s day of the month',
	'repeat_ui:repeat_monthly_by:day_of_month:weekday' => 'on the %s %s of the month',
	'repeat_ui:repeat_monthly_by:day_of_week' => 'Day of the week',
	'repeat_ui:repeat_weekly_days' => 'Repeat on',

	'events_ui:enable_reminders' => 'Enable Reminders',
	'events_ui:reminders' => 'Reminders',
	'events_ui:reminders:add' => 'Add Reminder',
	
	'events_ui:minutes' => 'minutes',
	'events_ui:hours' => 'hours',
	'events_ui:days' => 'days',
	
	'events:feed:range' => 'Events between %s and %s',
	'events:feed:month' => 'Events in %s',

	'events:view:calendar' => 'Calendar view',
	'events:view:calendar:switch' => 'Switch to calendar view',
	'events:view:feed' => 'Feed view',
	'events:view:feed:switch' => 'Switch to feed view',
	'events:view:ical' => 'iCal',
	'events:ical:url' => 'iCal Feed: ',

	'events:no_results' => 'There are no events to display',

	'calendar:add' => 'New Calendar',
	'events:calendar:add' => 'Create a new calendar',
	'events:calendar:edit' => 'Edit calendar',
	'events:calendar:groups:enable' => 'Enable group calendar',
	'events:calendar:group' => 'Group calendars',
	'events:add_to_calendar:default' => 'Add to my Calendar',
	'events:add_to_calendar:multi' => "Show this event on the following calendar(s)",
	'events:calendars:added' => "Event has been added to selected calendars",
	'events:calendars:orphan:added' => "Orphaned event has been restored to the default calendar",
	
	'river:event:create' => "%s created a new event %s",
	'events:start:time' => "Start Time",
	'events:end:time' => "End Time",
	'events:error:empty_title' => "You must enter a title for the event",
	'events_ui:resend:notifications' => "Resend notifications to members with this event on their calendars",
	'events_ui:default:calendar' => "Default Calendar",
	'calendar:settings' => "Calendar Settings",
	'calendar:groups:autosync' => "Sync group calendar settings",
	'calendar:groups:autosync:none' => "You are not a member of any groups yet",
	'calendar:autosync' => "Sync to your default calendar",
	'events:calendar:settings:saved' => "Calendar settings have been saved",
	'calendar:notifications' => "Calendar Notifications",
	'calendar:notifications:addtocal' => "Receive notifications when events are added to your calendar",
	'calendar:notifications:eventreminder' => "Receive reminder notifications before events",
	'calendar:notifications:eventupdate' => "Receive notifications when events get changed/updated",
	'event:notify:addtocal:subject' => "A new event has been added to your calendar: %s",
	'event:notify:addtocal:message' => "
An event has been added to your calendar

%s
%s
%s

%s

See the full details: %s
",
	'event:notify:eventupdate:subject' => "An event on your calendar has been updated: %s",
	'event:notify:eventupdate:message' => "
An event has been updated on your calendar

%s
%s
%s

%s

See the full details: %s",
	'event:notify:eventreminder:subject' => "Reminder: %s begins %s",
	'event:notify:eventreminder:message' => "
An event on your calendar starts soon!

%s
%s
%s

%s

See the full details: %s
",

	'events_ui:cancel' => 'Cancel',
	'events_ui:cancel:all' => 'Cancel All',
	'events_ui:cancel:confirm' => 'Are you sure you want to cancel this event? This can not be undone',
	'events_ui:cancel:all:confirm' => 'Are you sure you want to cancel all event in this series? This can not be undone',
	'events:settings:reminder:offsettime' => "Reminder Offset Time",
	'events:settings:reminder:offsettime:help' => "Enter a number of seconds in which to process event reminders ahead of schedule.  This should compensate for the time of processing, email lag, and help for popular events that require a lot of notifications",
	
	//widgets
	'events:widget:name' => "Events",
	'events:widget:description' => "Upcoming events on your calendars",
	'events:widget:settings:numresults' => "Number of events to show",
	'events:widgets:noresults' => "No events to list",
	'events:widget:settings:upcoming' => "Limit to upcoming events?",
);

add_translation("en", $english);
