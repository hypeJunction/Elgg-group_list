<?php

$dbprefix = elgg_get_config('dbprefix');

$options = array(
	'types' => 'object',
	'subtypes' => 'discussion',
	'order_by' => 'e.last_action desc',
	'limit' => 40,
	'full_view' => false,
	'no_results' => elgg_echo('discussion:none'),
	'joins' => array("JOIN {$dbprefix}entities ce ON ce.guid = e.container_guid"),
	'wheres' => array('ce.type = "group"'),
	'distinct' => false,
	'preload_containers' => true,
);

$identifier = elgg_extract('identifier', $vars, 'groups');

$subtypes = is_callable('group_subtypes_get_subtypes') ? group_subtypes_get_subtypes($identifier) : ELGG_ENTITIES_ANY_VALUE;
if (!empty($subtypes)) {
	$subtypes = (array) $subtypes;
	$subtypes_in = array();
	foreach ($subtypes as $subtype) {
		$subtypes_in = get_subtype_id('group', $subtype);
	}
	$subtypes_in = array_filter($subtypes_in);
	if (!empty($subtypes_in)) {
		$subtypes_in = implode(',', $subtypes_in);
		$options['wheres'][] = "ce.subtype IN ($subtypes_in)";
	}
}

echo elgg_list_entities($options);
