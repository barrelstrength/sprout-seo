(function($) {

	Craft.SproutSeoSitemap = Garnish.Base.extend(
	{
		$checkboxes:      null,
		$selectDropdowns: null,

		$customPageUrls: null,

		$status:                 null,
		$id:                     null,
		$elementGroupId:         null,
		$sitemapUrl:             null,
		$sitemapPriority:        null,
		$sitemapChangeFrequency: null,
		$enabled:                null,
		$ping:                   null,
		$newMetaDataGroupLinks:  null,

		$addCustomPageButton: null,

		init: function() {
			this.$checkboxes            = $('.sitemap-settings input[type="checkbox"]');
			this.$selectDropdowns       = $('.sitemap-settings select');
			this.$customPageUrls        = $('.sitemap-settings input.sitemap-custom-url');
			this.$newMetaDataGroupLinks = $('.metadatagroup-isnew');

			this.addListener(this.$checkboxes, 'change', 'onChange');
			this.addListener(this.$selectDropdowns, 'change', 'onChange');
			this.addListener(this.$customPageUrls, 'change', 'onChange');
			this.addListener(this.$newMetaDataGroupLinks, 'click', 'redirectToMetadataGroupEditPage');
		},

		redirectToMetadataGroupEditPage: function(event) {

			target    = event.target;
			isNew     = $(target).data('isnew');
			submitUrl = Craft.getUrl('sproutseo/metadata/new');

			data = {
				"metadatagroupname":  $(target).data('metadatagroupname'),
				"elementgrouphandle": $(target).data('elementgrouphandle'),
				"sitemapid":          $(target).data('sitemapid'),
				"elementgroupid":     $(target).data('elementgroupid'),
				"metadataId":         $(target).data('metadataid'),
				"metatag":            $(target).data('link')
			};

			this.postForm(submitUrl, data);
		},

		onChange: function(event) {
			changedElement = event.target;
			rowId          = $(changedElement).closest('tr').data('rowid');

			this.status                 = $('tr[data-rowid="' + rowId + '"] td span.status');
			this.id                     = $('input[name="sitemap_fields[' + rowId + '][id]"]').val();
			this.elementGroupId         = $('input[name="sitemap_fields[' + rowId + '][elementGroupId]"]').val();
			this.sitemapUrl             = $('input[name="sitemap_fields[' + rowId + '][sitemapUrl]"]').val();
			this.sitemapPriority        = $('select[name="sitemap_fields[' + rowId + '][sitemapPriority]"]').val();
			this.sitemapChangeFrequency = $('select[name="sitemap_fields[' + rowId + '][sitemapChangeFrequency]"]').val();
			this.enabled                = $('input[name="sitemap_fields[' + rowId + '][enabled]"]').is(":checked");
			this.ping                   = $('input[name="sitemap_fields[' + rowId + '][ping]"]').is(":checked");

			// @todo - clean these up
			console.log('new request');
			console.log(this.status);
			console.log(this.id);
			console.log(this.elementGroupId);
			console.log(this.sitemapUrl);
			console.log(this.sitemapPriority);
			console.log(this.sitemapChangeFrequency);
			console.log(this.enabled);
			console.log(this.ping);
			console.log(this.categoryGroupId);
			console.log('end request');

			if (this.enabled) {
				this.status.removeClass('disabled');
				this.status.addClass('live');
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr("disabled", false);
			}
			else {
				this.status.removeClass('live');
				this.status.addClass('disabled');
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').prop('checked', false);
				$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr("disabled", true);
				this.ping = false;
			}

			Craft.postActionRequest('sproutSeo/sitemap/saveSitemap', {
				id:                     this.id,
				elementGroupId:         this.elementGroupId,
				sitemapUrl:             this.sitemapUrl,
				sitemapPriority:        this.sitemapPriority,
				sitemapChangeFrequency: this.sitemapChangeFrequency,
				enabled:                this.enabled,
				ping:                   this.ping,
			}, $.proxy(function(response, textStatus) {
				if (textStatus == 'success') {
					if (response.lastInsertId) {
						var keys     = rowId.split("-");
						var type     = keys[0];
						var newRowId = type + "-" + response.lastInsertId;
						$(changedElement).closest('tr').data('rowid', newRowId);

						$('input[name="sitemap_fields[' + rowId + '][id]"]').val(newRowId);
						$('input[name="sitemap_fields[' + rowId + '][id]"]').attr('name', 'sitemap_fields[' + newRowId + '][id]');
						$('input[name="sitemap_fields[' + rowId + '][elementGroupId]"]').attr('name', 'sitemap_fields[' + newRowId + '][elementGroupId]');
						$('input[name="sitemap_fields[' + rowId + '][sitemapUrl]"]').attr('name', 'sitemap_fields[' + newRowId + '][sitemapUrl]');
						$('select[name="sitemap_fields[' + rowId + '][sitemapPriority]"]').attr('name', 'sitemap_fields[' + newRowId + '][sitemapPriority]');
						$('select[name="sitemap_fields[' + rowId + '][sitemapChangeFrequency]"]').attr('name', 'sitemap_fields[' + newRowId + '][sitemapChangeFrequency]');
						$('input[name="sitemap_fields[' + rowId + '][enabled]"]').attr('name', 'sitemap_fields[' + newRowId + '][enabled]');
						$('input[name="sitemap_fields[' + rowId + '][ping]"]').attr('name', 'sitemap_fields[' + newRowId + '][ping]');

						Craft.cp.displayNotice(Craft.t("Sitemap setting saved."));
					}
					else {
						Craft.cp.displayError(Craft.t('Unable to save Sitemap setting.'));
					}
				}
			}, this))
		},

		postForm: function(action, nameValueObj) {

			var form    = document.createElement("form");
			var i, input, prop;
			form.method = "post";
			form.action = action;

			// Loop through properties: name-value pairs
			for (prop in nameValueObj) {
				input       = document.createElement("input");
				input.name  = prop;
				input.value = nameValueObj[prop];
				input.type  = "hidden";
				form.appendChild(input);
			}

			//document.body.appendChild(form); <-- Could be needed by some browsers?

			form.submit();

			return form;
		}

	});

})(jQuery);
