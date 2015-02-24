<?php

namespace Events\UI;

$start = elgg_extract('start', $vars);
$end = elgg_extract('end', $vars);

if (!$start || !$end) {
	return;
}

$output = '';

if (date('Y-m-d', $start) == date('Y-m-d', $end)) {
	if (date('Y') == date('Y', $start)) {
		$output .= date('D, F j', $start);
	} else {
		$output .= date('D, F j, Y', $start);
	}
	$output .= ', ' . date('g:ia', $start) . ' - ' . date('g:ia', $end);
} else {
	if (date('Y') == date('Y', $start)) {
		$output .= date('D, F j g:ia', $start);
		$output .= ' - ' . date('D, F j g:ia', $end);
	} else {	
		$output .= date('D, F j, Y g:ia', $start);
		$output .= ' - ' . date('D, F j, Y g:ia', $end);
	}
}

echo $output;