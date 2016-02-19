<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

$base_url = elgg_normalize_url($identifier) . '?' . parse_url(current_page_url(), PHP_URL_QUERY);

$list_class = (array) elgg_extract('list_class', $vars, array());
$list_class[] = 'elgg-list-groups';

$item_class = (array) elgg_extract('item_class', $vars, array());

$options = (array) elgg_extract('options', $vars, array());

$list_options = array(
	'full_view' => false,
	'limit' => elgg_extract('limit', $vars, elgg_get_config('default_limit')) ? : 10,
	'list_class' => implode(' ', $list_class),
	'item_class' => implode(' ', $item_class),
	'no_results' => elgg_echo("$identifier:none"),
	'pagination' => elgg_is_active_plugin('hypeLists') || !elgg_in_context('widgets'),
	'pagination_type' => 'default',
	'base_url' => $base_url,
	'list_id' => 'groups',
	'auto_refresh' => false,
	'item_view' => elgg_get_plugin_setting('use_membership_view', 'group_list') ? 'group/format/membership' : null,
);

$getter_options = array(
	'types' => array('group'),
	'subtypes' => is_callable('group_subtypes_get_subtypes') ? group_subtypes_get_subtypes($identifier) : ELGG_ENTITIES_ANY_VALUE,
);

$options = array_merge_recursive($list_options, $options, $getter_options);

$params = $vars;
$params['options'] = $options;
$params['callback'] = 'elgg_list_entities';
echo elgg_view('lists/groups', $params);
