<?php

namespace Events\UI;

$upgrade_version = elgg_get_plugin_setting('upgrade_version', PLUGIN_ID);
if (!$upgrade_version) {
	elgg_set_plugin_setting('upgrade_version', 20141215, PLUGIN_ID);
}