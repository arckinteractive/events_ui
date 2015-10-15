<?php

// NOTE: due to the old version of jquery in Elgg 1.8 we need to use the old version of fullcalendar
// tried upgrading but too much stuff breaks

namespace Events\UI;

require_once __DIR__ . '/autoloader.php';

const UPGRADE_VERSION = 20141215;

if (!defined('ELGG_SITE_TIMEZONE')) {
	define('ELGG_SITE_TIMEZONE', elgg_get_plugin_setting('default_timezone', 'events_ui'));
}

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');
elgg_register_event_handler('pagesetup', 'system', __NAMESPACE__ . '\\pagesetup');

function init() {
	elgg_register_library('events:upgrades', __DIR__ . '/lib/upgrades.php');
	
	elgg_extend_view('notifications/subscriptions/personal', 'core/settings/calendar/notifications');

	elgg_register_viewtype('ical');

	elgg_extend_view('components/calendar.css', 'fullcalendar.css', 100);
	elgg_register_css('components/calendar', elgg_get_simplecache_url('components/calendar.css'));
	elgg_extend_view('jquery-ui/theme/theme.css', 'jquery-ui/theme/jquery-ui.min.css', 100);
	elgg_register_css('jquery-ui', elgg_get_simplecache_url('jquery-ui/theme/theme.css'));
	
	elgg_extend_view('initialize_elgg.js', 'components/calendar/settings.js');

	elgg_register_page_handler('calendar', __NAMESPACE__ . '\\page_handler');
	elgg_register_page_handler('events', __NAMESPACE__ . '\\event_pagehandler');

	elgg_register_plugin_hook_handler('entity:url', 'object', __NAMESPACE__ . '\\url_handler');

	elgg_register_action('events_ui/settings/save', __DIR__ . '/actions/plugins/settings/save.php', 'admin');

	elgg_register_plugin_hook_handler('register', 'menu:owner_block', __NAMESPACE__ . '\\owner_block_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:entity', __NAMESPACE__ . '\\entity_menu_setup');
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', __NAMESPACE__ . '\\entity_icon_url');

	elgg_register_plugin_hook_handler('container_permissions_check', 'object', __NAMESPACE__ . '\\container_permissions_check');
	elgg_register_plugin_hook_handler('action', 'notificationsettings/save', __NAMESPACE__ . '\\notification_settings_save');
	elgg_register_plugin_hook_handler('cron', 'minute', __NAMESPACE__ . '\\event_reminders');
	elgg_register_plugin_hook_handler('subscription_types', 'comment_tracker', __NAMESPACE__ . '\\register_comment_tracker');

	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', __NAMESPACE__ . '\\setup_public_pages');

	elgg_register_plugin_hook_handler('export:instance', 'events_api', __NAMESPACE__ . '\\export_event_instance');


	elgg_register_event_handler('create', 'object', __NAMESPACE__ . '\\event_create');
	elgg_register_event_handler('update', 'object', __NAMESPACE__ . '\\event_update');
	elgg_register_event_handler('events_api', 'add_to_calendar', __NAMESPACE__ . '\\add_to_calendar');
	elgg_register_event_handler('shutdown', 'system', __NAMESPACE__ . '\\vroom_functions');

	add_group_tool_option('calendar', elgg_echo('events:calendar:groups:enable'), true);

	elgg_register_widget_type('events', elgg_echo('events:widget:name'), 'events:widget:description', array('profile','dashboard','group'));

	elgg_register_ajax_view('events_ui/ajax/picker');
	elgg_register_ajax_view('events_ui/ajax/ical_modal');
	elgg_register_ajax_view('components/calendar');

	// Timezone logic
	elgg_extend_view('forms/account/settings', 'core/settings/account/timezone', 100);
	elgg_register_plugin_hook_handler('usersettings:save', 'user', __NAMESPACE__ . '\\save_default_user_timezone');
	elgg_register_plugin_hook_handler('timezones', 'events_api', __NAMESPACE__ . '\\filter_timezones');
	
	// migration stuff
	elgg_register_event_handler('upgrade', 'system', __NAMESPACE__ . '\\upgrades');
	elgg_register_admin_menu_item('administer', 'events_migrate', 'administer_utilities');
	elgg_register_action('events/migrate', __DIR__ . '/actions/events/migrate.php', 'admin');
}
