<?php

if (!elgg_get_plugin_setting('allow_visibility_override', 'group_list', true)) {
	echo elgg_format_element('p', [
		'class' => 'elgg-no-results',
			], elgg_echo('user:groups:settings:empty'));
	return;
}

$entity = elgg_extract('entity', $vars);
/* @var $entity \ElggPlugin */

$user = elgg_extract('user', $vars);
/* @var $user \ElggUser */

echo elgg_view_input('select', array(
	'name' => 'params[group_membership_visibility]',
	'value' => $entity->getUserSetting('group_membership_visibility', $user->guid, $entity->group_membership_visibility),
	'options_values' => array(
		ACCESS_PRIVATE => elgg_echo('user:groups:visibility:private'),
		ACCESS_FRIENDS => elgg_echo('user:groups:visibility:friends'),
		ACCESS_LOGGED_IN => elgg_echo('user:groups:visibility:logged_in'),
		ACCESS_PUBLIC => elgg_echo('user:groups:visibility:public'),
	),
	'label' => elgg_echo('user:groups:group_membership_visibility'),
	'help' => elgg_echo('user:groups:group_membership_visibility:help'),
));
