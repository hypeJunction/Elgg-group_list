define(function(require) {
	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');

	$(document).on('click', '.elgg-menu-item-groups-join > a, .elgg-menu-item-groups-joinrequest > a', function(e) {
		var $elem = $(this);

		e.preventDefault();
		elgg.action($elem.attr('href'), {
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function(response) {
				if (response.status >= 0) {
					$elem.parent().fadeOut();
				}
			}
		});
	});

	$(document).on('click', '.elgg-menu-item-groups-invitation-accept > a, .elgg-menu-item-groups-invitation-decline > a, .elgg-menu-item-groups-killrequest > a', function(e) {
		var $elem = $(this);
		e.preventDefault();
		elgg.action($elem.attr('href'), {
			beforeSend: spinner.start,
			complete: spinner.stop,
			success: function(response) {
				if (response.status < 0) {
					return;
				}
				if ($elem.closest('.elgg-list-groups-invited,.elgg-list-groups-membership_request').length) {
					$elem.closest('.elgg-item').fadeOut().remove();
					$elem.closest('.elgg-list-groups-invited,.elgg-list-groups-membership_request').trigger('refresh');
				} else {
					$elem.parent().fadeOut().remove();
				}
			}
		});
	});
	
});
