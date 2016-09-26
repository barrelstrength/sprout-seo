(function($) {

	Craft.SproutSeoSitemap = Garnish.Base.extend(
	{
		$lightswitches:   null,
		$selectDropdowns: null,

		$customPageUrls: null,

		$status:          null,
		$id:              null,
		$elementGroupId:  null,
		$url:             null,
		$priority:        null,
		$changeFrequency: null,
		$enabled:         null,

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

			this.status          = $('tr[data-rowid="' + rowId + '"] td span.status');
			this.id              = $('input[name="sproutseo[sitemap][' + rowId + '][id]"]').val();
			this.elementGroupId  = $('input[name="sproutseo[sitemap][' + rowId + '][elementGroupId]"]').val();
			this.url             = $('input[name="sproutseo[sitemap][' + rowId + '][url]"]').val();
			this.priority        = $('select[name="sproutseo[sitemap][' + rowId + '][priority]"]').val();
			this.changeFrequency = $('select[name="sproutseo[sitemap][' + rowId + '][changeFrequency]"]').val();
			this.enabled         = $('input[name="sproutseo[sitemap][' + rowId + '][enabled]"]').val();

			// @todo - clean up logging
			//console.log('new request');
			//console.log(this.status);
			//console.log(this.id);
			//console.log(this.elementGroupId);
			//console.log(this.url);
			//console.log(this.priority);
			//console.log(this.changeFrequency);
			//console.log(this.enabled);
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
				id:              this.id,
				elementGroupId:  this.elementGroupId,
				url:             this.url,
				priority:        this.priority,
				changeFrequency: this.changeFrequency,
				enabled:         this.enabled,
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
						$('input[name="sproutseo[sitemap][' + rowId + '][url]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][url]');
						$('select[name="sproutseo[sitemap][' + rowId + '][priority]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][priority]');
						$('select[name="sproutseo[sitemap][' + rowId + '][changeFrequency]"]').attr('name', 'sproutseo[sitemap][' + newRowId + '][changeFrequency]');
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
