<?php

namespace Events\UI;

// this could take a long time, so we're going to vroom it

register_vroom_function(__NAMESPACE__ . '\\event_calendar_migration');

// this is where we keep the migration function out of the way
elgg_load_library('events:upgrades');

system_message(elgg_echo('events:migrate:system_message'));