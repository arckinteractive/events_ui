<?php

$plugin_root = __DIR__;
$root = dirname(dirname($plugin_root));
$alt_root = dirname(dirname(dirname($initial_root)));

if (file_exists("$plugin_root/vendor/autoload.php")) {
	$path = $plugin_root;
} else if (file_exists("$root/vendor/autoload.php")) {
	$path = $root;
} else {
	$path = $alt_root;
	$root = $alt_root;
}

return array(
	'default' => array(
		'fullcalendar.js' => $path . '/vendor/bower-asset/fullcalendar/dist/fullcalendar.min.js',
		'fullcalendar.css' => $path . '/vendor/bower-asset/fullcalendar/dist/fullcalendar.min.css',
		'fullcalendar.print.css' => $path . '/vendor/bower-asset/fullcalendar/dist/fullcalendar.print.css',
		'moment.js' => $path . '/vendor/bower-asset/moment/min/moment.min.js',

		'jquery-ui/theme/' => $root . '/vendor/bower-asset/jquery-ui/themes/smoothness/',
		
		// BC
		'css/events_ui.css' => __DIR__ . '/views/default/components/calendar.css',
	)
);