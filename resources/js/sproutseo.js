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
		submitStatus: false,

		init: function() {
			this.$newSectionMetadataLinks = $('.sectionmetadata-isnew .sproutseo-sectiontitle');

			this.addListener(this.$newSectionMetadataLinks, 'click', 'createAndEditSectionMetadata');
		},

		createAndEditSectionMetadata: function(event) {

			event.preventDefault();

			if (!this.submitStatus)
			{
				this.submitStatus = true;
				$target = event.target;
				$row    = $($target).closest('tr');
				var handle = $($row).data('handle').split(':');
				handle = handle.length == 2 ? handle[1] : $($row).data('handle');

				data = {
					"redirect":  'sproutseo/sections/{id}',
					"sproutseo": {
						"metadata": {
							"name":                $($row).data('name'),
							"handle":              handle,
							"urlEnabledSectionId": $($row).data('urlEnabledSectionId'),
							"type":                $($row).data('type'),
							"url":                 $($row).data('url')
						}
					}
				};

				Craft.postActionRequest('sproutSeo/sectionMetadata/saveSectionMetadata', data, $.proxy(function(response, textStatus) {
					if (textStatus == 'success') {
						if (response.success) {
							Craft.redirectTo('sproutseo/sections/' + response.sectionMetadata.id);
						}
					}
				}, this));
			}
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

			Craft.postActionRequest('sproutSeo/sectionMetadata/saveSectionMetadataViaSitemapSection', data, $.proxy(function(response, textStatus) {
				if (textStatus == 'success') {
					if (response.success) {

						var keys     = rowId.split("-");
						var type     = keys[0];
						var newRowId = null;

						if (response.sectionMetadata.urlEnabledSectionId) {
							newRowId = type + "-" + response.sectionMetadata.urlEnabledSectionId;
						}
						else {
							newRowId = type + "-" + response.sectionMetadata.id;
						}

						$changedElementRow       = $(changedElement).closest('tr');
						$changedElementTitleLink = $changedElementRow.find('a.sproutseo-sectiontitle');

						if ($changedElementRow.data('isNew')) {
							$changedElementTitleLink.attr('href', 'sections/' + response.sectionMetadata.id);
							$changedElementRow.removeClass('sectionmetadata-isnew');
							$changedElementRow.data('isNew', 0);
							$changedElementRow.data('id', response.sectionMetadata.id);

							$changedElementTitleLink.unbind('click');
						}

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
