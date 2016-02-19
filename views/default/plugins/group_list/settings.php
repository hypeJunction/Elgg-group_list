<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_input('select', array(
	'name' => 'params[group_membership_visibility]',
	'value' => isset($entity->group_membership_visibility) ? $entity->group_membership_visibility : ACCESS_PUBLIC,
	'options_values' => array(
		ACCESS_PRIVATE => elgg_echo('user:groups:visibility:private'),
		ACCESS_FRIENDS => elgg_echo('user:groups:visibility:friends'),
		ACCESS_LOGGED_IN => elgg_echo('user:groups:visibility:logged_in'),
		ACCESS_PUBLIC => elgg_echo('user:groups:visibility:public'),
	),
	'label' => elgg_echo('user:groups:group_membership_visibility'),
	'help' => elgg_echo('user:groups:group_membership_visibility:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[allow_visibility_override]',
	'value' => isset($entity->allow_visibility_override) ? $entity->allow_visibility_override : true,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('user:groups:allow_visibility_override'),
	'help' => elgg_echo('user:groups:allow_visibility_override:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[use_membership_view]',
	'value' => isset($entity->use_membership_view) ? $entity->use_membership_view : false,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('group:list:use_membership_view'),
	'help' => elgg_echo('group:list:use_membership_view:help'),
));