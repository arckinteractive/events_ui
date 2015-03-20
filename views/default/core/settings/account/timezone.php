<?php
/**
 * Allows users to select their preferred timezone
 */

namespace Events\UI;

use Events\API\Util;

$user = elgg_get_page_owner_entity();

if ($user) {
	$title = elgg_echo('user:set:timezone');
	$content = '<label>' . elgg_echo('user:timezone:label') . '</label>';
	$content .= elgg_view("input/timezone", array(
		'name' => 'timezone',
		'value' => Util::getClientTimezone($user),
	));
	echo elgg_view_module('info', $title, $content);
}
