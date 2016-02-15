<?php

echo elgg_view('lists/groups/all', array(
	'rel' => 'invited',
	'show_rel' => false,
	'user' => elgg_extract('user', $vars, elgg_get_page_owner_entity()),
));