/** global: Craft */
/** global: Garnish */
/** global: $ */

(function (window) {
	if (!window.Craft || !window.Garnish || !window.$) {
		return;
	}

	Craft.SearchablePlugin = {
		settings: {},

		init: function (data) {
			var _this = this;

			this.data = data;

			this.addSearchableIcon();
		},

		addSearchableIcon: function () {
			// Find all headings
			var _this = this;
			var targets = $(".field .heading");

			targets.each(function () {
				var $target = $(this).get();
				var $label = $($target).find("label, legend").get();

				if (!$label.length) {
					return;
				}

				// Get corresponding field
				var $field = $($label).closest(".field").get();

				// Check if searchable
				var isSearchable = _this.getFieldSearchable($field);

				if (isSearchable) {
					// Append label with icon if searchable
					$($label).append(
						'<span class="searchable-indicator" data-icon="search" title="' +
						_this.data.searchLabel +
						'" aria-label="' +
						_this.data.searchLabel +
						'" role="img" />'
					);
				}
			});
		},

		getFieldSearchable: function (field) {
			var id = $(field).attr("id");
			var defaultSetting = false;

			if (!id) {
				return defaultSetting;
			}

			var segments = id.split("-");
			var handle = segments[segments.length - 2];

			// Check if field might be a matrix-like field
			if (segments.length > 3) {
				var parentFieldId = segments[0] + "-" + segments[1] + "-field";
				var parentField = $("#" + parentFieldId);

				if (parentField) {
					var parentFieldType = parentField.attr("data-type");

					if (parentFieldType && parentFieldType == "craft\\fields\\Matrix") {
						var handle = "matrix-" + segments[segments.length - 2];
					}
				}
			}

			// Check if handle is a default search attributes
			var defaultAttributes = [
				"filename",
				"extension",
				"kind",
				"title",
				"slug",
				"username",
				"firstname",
				"lastname",
				"fullname",
				"email",
			];

			if (defaultAttributes.includes(handle)) {
				return true;
			} else {
				// Else check the dataset of fields
				var fieldData = this.data.fields[handle] || false;

				if (fieldData) {
				return fieldData.searchable;
				}
			}

			return defaultSetting;
		},
	};
})(window);
