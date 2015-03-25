<?php
/**
 * Display a timezone input
 */

namespace Events\UI;

use Events\API\Util;

$name = elgg_extract('name', $vars, 'timezone');
$value = elgg_extract('value', $vars, Util::getClientTimezone());
$this_timezone = Util::getTimezoneInfo($value);
$this_country_code = $this_timezone->country_code;

$timezones = Util::getTimezonesByCountry();

$country_options = array();
$timezone_options = array();

foreach ($timezones as $country_code => $country_timezones) {
	$country_options[$country_code] = elgg_echo("timezone:country:$country_code");
	if ($country_code == $this_country_code) {
		foreach ($country_timezones as $country_timezone) {
			$timezone_options[$country_timezone->id] = $country_timezone->label;
		}
	}
}
ksort($country_options);
?>
<div class="elgg-input-timezone clearfix">
	<div class="elgg-col elgg-col-1of3">
		<?php
		echo elgg_view('input/dropdown', array(
			'data-timezone-country' => $this_country_code,
			'value' => $this_country_code,
			'options_values' => $country_options,
		));
		?>
	</div>
	<div class="elgg-col elgg-col-2of3">
		<?php
		echo elgg_view('input/dropdown', array(
			'data-timezone-id' => $value,
			'name' => $name,
			'value' => $value,
			'options_values' => $timezone_options,
		));
		?>
	</div>
</div>