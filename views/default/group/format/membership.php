<?php
$size = elgg_extract('size', $vars, 'small');
$entity = elgg_extract('entity', $vars);

if (!$entity instanceof ElggGroup) {
	return;
}

$menu_params = $vars;
$menu_params['sort_by'] = 'priority';
$menu_params['class'] = 'elgg-menu-group-membership';
$menu = elgg_view_menu('membership', $menu_params);

$metadata = '';
if (!elgg_in_context('widgets')) {
	$menu_params['class'] = 'elgg-menu-hz';
	$metadata = elgg_view_menu('entity', $menu_params);
}

$title = null;
$query = elgg_extract('query', $vars, get_input('query'));
if ($query && elgg_is_active_plugin('search')) {
	$name = search_get_highlighted_relevant_substrings($entity->getDisplayName(), $query);
	$title = elgg_view('output/url', array(
		'href' => $entity->getURL(),
		'text' => $name,
	));
}

$icon = elgg_view_entity_icon($entity, $size);
$summary = elgg_view('group/elements/summary', array(
	'entity' => $entity,
	'title' => $title,
	'metadata' => $metadata,
	'subtitle' => $entity->briefdescription,
	'content' => $menu,
		));

echo elgg_view_image_block($icon, $summary, array(
	'class' => 'elgg-group',
));
?>
<script>
	require(['group/format/membership']);
</script>