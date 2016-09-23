(function($) {

	Craft.SproutSeoSitemap = Garnish.Base.extend(
	{
		$lightswitches:   null,
		$selectDropdowns: null,

		$customPageUrls: null,

		$status:                 null,
		$id:                     null,
		$elementGroupId:         null,
		$sitemapUrl:             null,
		$sitemapPriority:        null,
		$sitemapChangeFrequency: null,
		$enabled:                null,

		$addCustomPageButton: null,

		init: function() {
			this.$lightswitches   = $('.sitemap-settings .lightswitch');
			this.$selectDropdowns = $('.sitemap-settings select');
			this.$customPageUrls  = $('.sitemap-settings input.sitemap-custom-url');

			this.addListener(this.$lightswitches, 'click', 'onChange');
			this.addListener(this.$selectDropdowns, 'change', 'onChange');
			this.addListener(this.$customPageUrls, 'change', 'onChange');
		},

		onChange: function(ev) {
			changedElement = ev.target;
			rowId          = $(changedElement).closest('tr').data('rowid');

			this.status                 = $('tr[data-rowid="' + rowId + '"] td span.status');
			this.id                     = $('input[name="sproutseo[sitemap][' + rowId + '][id]"]').val();
			this.elementGroupId         = $('input[name="sproutseo[sitemap][' + rowId + '][elementGroupId]"]').val();
			this.sitemapUrl             = $('input[name="sproutseo[sitemap][' + rowId + '][sitemapUrl]"]').val();
			this.sitemapPriority        = $('select[name="sproutseo[sitemap][' + rowId + '][sitemapPriority]"]').val();
			this.sitemapChangeFrequency = $('select[name="sproutseo[sitemap][' + rowId + '][sitemapChangeFrequency]"]').val();
			this.enabled                = $('input[name="sproutseo[sitemap][' + rowId + '][enabled]"]').val();

			// @todo - clean up logging
			//console.log('new request');
			//console.log(this.status);
			//console.log(this.id);
			//console.log(this.elementGroupId);
			//console.log(this.sitemapUrl);
			//console.log(this.sitemapPriority);
			//console.log(this.sitemapChangeFrequency);
			console.log(this.enabled);
			//console.log(this.categoryGroupId);
			//console.log('end request');

			if (this.enabled) {
				this.status.removeClass('disabled');
				this.status.addClass('live');
			}
			else {
				this.status.removeClass('live');
				this.status.addClass('disabled');
			}

			Craft.postActionRequest('sproutSeo/sitemap/saveSitemap', {
				id:                     this.id,
				elementGroupId:         this.elementGroupId,
				sitemapUrl:             this.sitemapUrl,
				sitemapPriority:        this.sitemapPriority,
				sitemapChangeFrequency: this.sitemapChangeFrequency,
				enabled:                this.enabled,
			}, $.proxy(function(response, textStatus) {

				if (textStatus == 'success') {
					if (response.lastInsertId) {
						var keys     = rowId.split("-");
						var type     = keys[0];
						var newRowId = type + "-" + response.lastInsertId;
						$(changedElement).closest('tr').data('rowid', newRowId);

						$('input[name="sproutseo[sitemap][' + rowId + '][id]"]').val(newRowId);
						$('input[name="sproutseo[sitemap][' + rowId + '][id]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][id]');
						$('input[name="sproutseo[sitemap][' + rowId + '][elementGroupId]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][elementGroupId]');
						$('input[name="sproutseo[sitemap][' + rowId + '][sitemapUrl]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][sitemapUrl]');
						$('select[name="sproutseo[sitemap][' + rowId + '][sitemapPriority]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][sitemapPriority]');
						$('select[name="sproutseo[sitemap][' + rowId + '][sitemapChangeFrequency]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][sitemapChangeFrequency]');
						$('input[name="sproutseo[sitemap][' + rowId + '][enabled]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][enabled]');

						Craft.cp.displayNotice(Craft.t("Sitemap setting saved."));
					}
					else {
						Craft.cp.displayError(Craft.t('Unable to save Sitemap setting.'));
					}
				}
			}, this))
		},

	});

})(jQuery);
