(function($) {

	Craft.SproutSeoSitemap = Garnish.Base.extend(
	{

		$checkboxes: null,
		$selectDropdowns: null,

		$customPageUrls: null,

		$status: null,
		$id: null,
		$elementGroupId: null,
		$url: null,
		$priority: null,
		$changeFrequency: null,
		$enabled: null,
		$ping: null,
		$metadataLinks: null,
		$metatag: null,
		$isNew : null,
		$metadataId: null,

		$addCustomPageButton: null,

		init: function()
		{
			this.$checkboxes = $('.sitemap-settings input[type="checkbox"]');
			this.$selectDropdowns = $('.sitemap-settings select');
			this.$customPageUrls = $('.sitemap-settings input.sitemap-custom-url');
			this.$metadataLinks = $('.metadata-link');
			// this.$addCustomPageButton = $('#add-custom-page');

			this.addListener(this.$checkboxes, 'change', 'onChange');
			this.addListener(this.$selectDropdowns, 'change', 'onChange');
			this.addListener(this.$customPageUrls, 'change', 'onChange');
			this.addListener(this.$metadataLinks, 'click', 'redirectToMetadata');
			// this.addListener(this.$addCustomPageButton, 'click', 'addCustomPage');
		},

		redirectToMetadata: function(ev)
		{
			changedElement = ev.target;
			this.metatag   = $(changedElement).data('link');
			this.isNew   = $(changedElement).data('isnew');
			this.metadataId   = $(changedElement).data('metadataid');

			if (this.isNew)
			{
				Craft.redirectTo(Craft.getUrl('sproutseo/metadata/new', 'metatag='+this.metatag));
			}
			else
			{
				Craft.redirectTo(Craft.getUrl('sproutseo/metadata/'+this.metadataId));
			}
		},

		onChange: function(ev)
		{
			changedElement = ev.target;
			rowId          = $(changedElement).closest('tr').data('rowid');

			this.status          = $('tr[data-rowid="' + rowId + '"] td span.status');
			this.id              = $('input[name="sitemap_fields[' + rowId + '][id]"]').val();
			this.elementGroupId  = $('input[name="sitemap_fields[' + rowId + '][elementGroupId]"]').val();
			this.url             = $('input[name="sitemap_fields[' + rowId + '][url]"]').val();
			this.priority        = $('select[name="sitemap_fields[' + rowId + '][priority]"]').val();
			this.changeFrequency = $('select[name="sitemap_fields[' + rowId + '][changeFrequency]"]').val();
			this.enabled         = $('input[name="sitemap_fields[' + rowId + '][enabled]"]').is(":checked");
			this.ping            = $('input[name="sitemap_fields[' + rowId + '][ping]"]').is(":checked");

			console.log('new request');
			console.log(this.status);
			console.log(this.id);
			console.log(this.elementGroupId);
			console.log(this.url);
			console.log(this.priority);
			console.log(this.changeFrequency);
			console.log(this.enabled);
			console.log(this.ping);
			console.log(this.categoryGroupId);
			console.log('end request');

			if (this.enabled)
			{
				this.status.removeClass('disabled');
				this.status.addClass('live');
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr("disabled", false);
			}
			else
			{
				this.status.removeClass('live');
				this.status.addClass('disabled');
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').prop('checked', false);
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr("disabled", true);
				this.ping = false;
			}

			Craft.postActionRequest('sproutSeo/sitemap/saveSitemap', {
				id: this.id,
				elementGroupId: this.elementGroupId,
				url: this.url,
				priority: this.priority,
				changeFrequency: this.changeFrequency,
				enabled: this.enabled,
				ping: this.ping,
			}, $.proxy(function(response, textStatus)
			{
				if (textStatus == 'success')
				{
					if (response.lastInsertId)
					{
						var keys = rowId.split("-");
						var type = keys[0];
						var newRowId = type+"-"+response.lastInsertId;
						$(changedElement).closest('tr').data('rowid', newRowId);

						$('input[name="sitemap_fields[' + rowId  + '][id]"]').val(newRowId);
						$('input[name="sitemap_fields[' + rowId + '][id]"]').attr('name', 'sitemap_fields[' + newRowId + '][id]');
						$('input[name="sitemap_fields[' + rowId + '][elementGroupId]"]').attr('name', 'sitemap_fields[' + newRowId + '][elementGroupId]');
						$('input[name="sitemap_fields[' + rowId + '][url]"]').attr('name', 'sitemap_fields[' + newRowId + '][url]');
						$('select[name="sitemap_fields[' + rowId + '][priority]"]').attr('name', 'sitemap_fields[' + newRowId + '][priority]');
						$('select[name="sitemap_fields[' + rowId + '][changeFrequency]"]').attr('name', 'sitemap_fields[' + newRowId + '][changeFrequency]');
						$('input[name="sitemap_fields[' + rowId + '][enabled]"]').attr('name', 'sitemap_fields[' + newRowId + '][enabled]');
						$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr('name', 'sitemap_fields[' + newRowId + '][ping]');

						Craft.cp.displayNotice(Craft.t("Sitemap setting saved."));
					}
					else
					{
						Craft.cp.displayError(Craft.t('Unable to save Sitemap setting.'));
					}
				}
			}, this))
		},

	});

})(jQuery);
