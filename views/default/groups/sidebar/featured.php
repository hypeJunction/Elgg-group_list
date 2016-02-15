<?php
/**
 * Featured groups
 *
 * @package ElggGroups
 */

$identifier = elgg_extract('identifier', $vars, 'groups');

$subtypes = is_callable('group_subtypes_get_subtypes') ? group_subtypes_get_subtypes($identifier) : ELGG_ENTITIES_ANY_VALUE;

$featured_groups = elgg_get_entities_from_metadata(array(
	'metadata_name' => 'featured_group',
	'metadata_value' => 'yes',
	'type' => 'group',
	'subtypes' => $subtypes,
));

if ($featured_groups) {

	elgg_push_context('widgets');
	$body = '';
	foreach ($featured_groups as $group) {
		$body .= elgg_view_entity($group, array('full_view' => false));
	}
	elgg_pop_context();

	echo elgg_view_module('aside', elgg_echo("$identifier:featured"), $body);
}
