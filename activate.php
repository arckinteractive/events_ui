<?php

namespace Events\UI;

$upgrade_version = elgg_get_plugin_setting('upgrade_version', 'events_ui');
if (!$upgrade_version) {
	elgg_set_plugin_setting('upgrade_version', 20141215, 'events_ui');
}
