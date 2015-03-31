
elgg.provide('elgg.events_timezone');

elgg.events_timezone.cache = [];

elgg.events_timezone.init = function() {
    $('.elgg-input-timezone select[data-timezone-country]').live('change', function() {
        var self = $(this);
        var country = self.val();

	if (elgg.events_timezone.cache[country]) {
		elgg.events_timezone.setOptions(self, elgg.events_timezone.cache[country]);
	} else {
		elgg.getJSON('calendar/timezones/' + country, {
			cache: true,
			success: function (data) {
				elgg.events_timezone.cache[country] = data;
				elgg.events_timezone.setOptions(self, data);
			}
		});
	}
    });


};

elgg.events_timezone.setOptions = function(self, options) {
    var options = options || [];
    var $parent = self.parents('.elgg-input-timezone').eq(0);
    var $tzIdPicker = $parent.find('select[data-timezone-id]').eq(0);
    
    $tzIdPicker.children('option').not(':selected').remove();
    $.each(options, function(index, tz) {
	if ($tzIdPicker.find('[value="' + tz.id + '"]').length === 0) {
		var $option = $('<option>').attr({ value: tz.id }).text(tz.label);
		$option.appendTo($tzIdPicker);
	}
    });
};

elgg.register_hook_handler('init', 'system', elgg.events_timezone.init);