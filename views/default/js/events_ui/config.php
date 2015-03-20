<?php

namespace Events\UI;

use Events\API\Util;

$tz = Util::getClientTimezone();
echo "elgg.config.timezone='{$tz}';";
