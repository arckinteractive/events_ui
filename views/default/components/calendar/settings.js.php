<?php

namespace Events\UI;

use Events\API\Util;

$tz = Util::getClientTimezone();
?>

	elgg.config.timezone = '<?= $tz ?>';
	