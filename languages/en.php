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
	'events:settings:sitecalendar:enable' => "Enable a site calendar?",
	
	'events:new' => "New Event",
	'events:edit' => 'Edit event',
	'events:add' => 'New event',
	'events:full:view' => "View full event details and comments",
	
	// forms
	'events:edit:title:placeholder' => 'Untitled Event',
	'events:edit:label:location' => 'Location',
	'events:edit:label:start' => "Start",
	'events:edit:label:end' => "End",
	'events:edit:label:timezone' => 'Timezone',
	'events_ui:allday' => "All Day",
	'events_ui:description' => 'Description',
	'events_ui:byline' => 'By %s',
	'events:status:recurring' => 'Recurring Event',

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

	'events:no_results' => 'There are no events to display',

	'calendar:add' => 'New Calendar',
	'events:calendar:add' => 'Create a new calendar',
	'events:calendar:edit' => 'Edit calendar',
	'events:calendar:groups:enable' => 'Enable group calendar',
	'events:calendar:group' => 'Group calendars',
	'events:add_to_calendar:default' => 'Add to my Calendar',
	'events:remove_from_calendar:default' => 'Remove from my Calendar',
	'events:add_to_calendar:multi' => "Show this event on the following calendar(s)",
	'events:calendars:addedremoved' => "Event has been added to %s calendar(s) and removed from %s calendar(s)",
	'events:calendars:added' => "Event has been added to %s calendar(s)",
	'events:calendars:removed' => "Event has been removed from %s calendar(s)",
	'events:calendar:picker:title' => "Select which calendars the event should be on",
	'events:calendar:picker:help' => "If checked the event will be added to the calendar, if unchecked it will be removed from the calendar.",
	'events:calendars:orphan:added' => "Orphaned event has been restored to the default calendar",
	
	'river:event:create' => "%s created a new event %s",
	'river:event:create:recurring' => "%s created a new recurring event %s",
	'river:comment:object:event' => "%s commented on the event %s",
	'events:start:time' => "Start Time",
	'events:end:time' => "End Time",
	'events:error:empty_title' => "You must enter a title for the event",
	'events_ui:resend:notifications' => "Resend notifications to members with this event on their calendars",
	'events_ui:default:calendar' => "Default Calendar",
	'calendar:settings' => "Calendar settings",
	'calendar:groups:autosync' => "Sync group calendar settings",
	'calendar:groups:autosync:none' => "You are not a member of any groups yet",
	'calendar:autosync' => "Sync to your default calendar",
	'events:calendar:settings:saved' => "Calendar settings have been saved",
	'calendar:notifications' => "Calendar Notifications",
	'calendar:notifications:addtocal' => "Receive notifications when events are added to your calendar",
	'calendar:notifications:eventreminder' => "Receive reminder notifications before events",
	'calendar:notifications:eventupdate' => "Receive notifications when events get changed/updated",
	'event:notify:addtocal:subject' => "Event: %s%s by %s",
	'events:notify:subject:ingroup' => " in the %s group",
	'event:notify:addtocal:message' => "
%s added the %s event%s 

%s
%s
%s
",
	'event:notify:eventupdate:subject' => "Event Updated: %s%s by %s",
	'event:notify:eventupdate:message' => "
%s has updated the %s event%s

%s
%s
%s
",
	'event:notify:eventreminder:subject' => "Event Reminder: %s%s begins %s",
	'event:notify:eventreminder:message' => "
An event on your calendar starts soon!

%s%s
%s
%s

%s
",

	'events_ui:cancel' => 'Delete',
	'events_ui:cancel:all' => 'Delete all occurences of this event',
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

	// Timezones
	'events:settings:timezone' => 'Timezone',
	'events:settings:timezone:help' => 'All timestamps are stored in UTC. If you disable timezone pickers, 
		please specify what timezone inputs should fall back to, if the user does not have timezone defined in their settings',
	'events:settings:timezone:picker' => 'Display timezone picker in event forms',
	'events:settings:timezone:default' => 'Default (fallback) timezone of the site',
	'events:settings:timezone:config' => 'A list of timezones to include in timezone pickers',
	'user:set:timezone' => "Timezone settings",
	'user:timezone:label' => "Your timezone",
	'user:timezone:success' => "Your timezone settings have been updated.",
	'user:timezone:fail' => "Your timezone settings could not be saved.",

	// iCal
	'events:settings:ical:help_page_url' => 'URL of the iCal help page shown in an iCal modal',
	'events:view:ical' => 'iCal',
	'events:ical:feed' => 'Subscribe to Calendar via iCal Feed',
	'events:ical:url' => 'Feed URL',
	'events:ical:help' => 'iCal feeds allow you to stay up to date with the updates to this calendar in your favorite calendaring software, such as Google Calendar. The URL below is permanent: you can add it to your calendar tool, and share it with friends. %s',
	'events:ical:learn_more' => 'Learn more',
	
	// misc
	'admin:administer_utilities:events_migrate' => "Migrate Events",
	'events:migrate:title' => "Migrate events from event_calendar",
	'events:migrate:count:none' => "Congratulations, there are no event_calendar events to migrate!",
	'events:migrate:run' => "Run the migration",
	'events:migrate:count' => "There are %s event_calendar entities that can be migrated",
	'events:migrate:system_message' => "The migration upgrade is running in the background, if there are a lot of events to migrate it may take a while.  Please check back later to see the progress.",
	'events:migrate:inprogress' => "The migration is still in progress, %s left to go, please check back later",
);

add_translation("en", $english);
