if (typeof Craft.SproutSeo === typeof undefined) {
	Craft.SproutSeo = {};
}

(function($) {

	/**
	 * Manages the creation of new Section Metadata Sections if they do not exist
	 */
	Craft.SproutSeo.SectionMetadata = Garnish.Base.extend(
	{
		$newSectionMetadataLinks: null,

		init: function() {
			this.$newSectionMetadataLinks = $('.sectionmetadata-isnew .sproutseo-sectiontitle');

			this.addListener(this.$newSectionMetadataLinks, 'click', 'createAndEditSectionMetadata');
		},

		createAndEditSectionMetadata: function(event) {

			event.preventDefault();

			$target = event.target;
			$row    = $($target).closest('tr');

			data = {
				"redirect":  'sproutseo/sections/{id}',
				"sproutseo": {
					"metadata": {
						"name":                $($row).data('name'),
						"handle":              $($row).data('handle'),
						"urlEnabledSectionId": $($row).data('urlEnabledSectionId'),
						"type":                $($row).data('type'),
						"url":                 $($row).data('url')
					}
				}
			};

			Craft.postActionRequest('sproutSeo/sections/saveSectionMetadata', data, $.proxy(function(response, textStatus) {
				if (textStatus == 'success') {
					if (response.success) {
						Craft.redirectTo('sproutseo/sections/' + response.sectionMetadata.id);
					}
				}
			}, this));
		}

	});

	/**
	 * Manages the dynamic updating of Sitemap attributes from the Sitemap page.
	 */
	Craft.SproutSeo.Sitemap = Garnish.Base.extend(
	{
		$lightswitches:   null,
		$selectDropdowns: null,

		$customSectionUrls: null,

		$status:  null,
		$enabled: null,
		isNew:    null,

		init: function() {
			this.$lightswitches     = $('.sitemap-settings .lightswitch');
			this.$selectDropdowns   = $('.sitemap-settings select');
			this.$customSectionUrls = $('.sitemap-settings input.sitemap-custom-url');

			this.addListener(this.$lightswitches, 'click', 'onChange');
			this.addListener(this.$selectDropdowns, 'change', 'onChange');
			this.addListener(this.$customSectionUrls, 'change', 'onChange');
		},

		onChange: function(ev) {

			changedElement = ev.target;
			$row           = $(changedElement).closest('tr');
			rowId          = $row.data('rowid');
			this.isNew     = $row.data('isNew');
			this.enabled   = $('input[name="sproutseo[sections][' + rowId + '][enabled]"]').val();

			this.status = $('tr[data-rowid="' + rowId + '"] td span.status');

			console.log(rowId);

			data = {
				"sproutseo": {
					"metadata": {
						"id":                  $row.data('id'),
						"name":                $row.data('name'),
						"handle":              $row.data('handle'),
						"type":                $row.data('type'),
						"urlEnabledSectionId": $row.data('urlEnabledSectionId'),
						"url":                 $('input[name="sproutseo[sections][' + rowId + '][url]"]').val(),
						"priority":            $('select[name="sproutseo[sections][' + rowId + '][priority]"]').val(),
						"changeFrequency":     $('select[name="sproutseo[sections][' + rowId + '][changeFrequency]"]').val(),
						"enabled":             this.enabled
					}
				}
			};

			console.log(data);

			Craft.postActionRequest('sproutSeo/sections/saveSectionMetadataViaSitemapSection', data, $.proxy(function(response, textStatus) {
				if (textStatus == 'success') {
					if (response.success) {

						var keys     = rowId.split("-");
						var type     = keys[0];
						var newRowId = type + "-" + response.sectionMetadata.urlEnabledSectionId;
						$(changedElement).closest('tr').data('rowid', newRowId);

						console.log(keys);
						console.log(type);
						console.log(newRowId);

						$sectionInputBase = 'input[name="sproutseo[sections][' + rowId + ']';

						$($sectionInputBase + '[id]"]').val(newRowId);
						$($sectionInputBase + '[id]"]').attr('name', 'sproutseo[sections][' + newRowId + '][id]');
						$($sectionInputBase + '[urlEnabledSectionId]"]').attr('name', 'sproutseo[sections][' + newRowId + '][urlEnabledSectionId]');
						$($sectionInputBase + '[url]"]').attr('name', 'sproutseo[sections][' + newRowId + '][url]');
						$($sectionInputBase + '[priority]"]').attr('name', 'sproutseo[sections][' + newRowId + '][priority]');
						$($sectionInputBase + '[changeFrequency]"]').attr('name', 'sproutseo[sections][' + newRowId + '][changeFrequency]');
						$($sectionInputBase + '[enabled]"]').attr('name', 'sproutseo[sections][' + newRowId + '][enabled]');

						Craft.cp.displayNotice(Craft.t("Sitemap Metadata saved."));
					}
					else {
						Craft.cp.displayError(Craft.t('Unable to save Sitemap Metadata.'));
					}
				}
			}, this));

			if (this.enabled) {
				this.status.removeClass('disabled');
				this.status.addClass('live');
			}
			else {
				this.status.removeClass('live');
				this.status.addClass('disabled');
			}
		}
	});

})(jQuery);
