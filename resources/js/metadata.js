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
		$newMetaDataGroupLinks:  null,

		$addCustomPageButton: null,

		init: function() {
			this.$checkboxes            = $('.sitemap-settings input[type="checkbox"]');
			this.$selectDropdowns       = $('.sitemap-settings select');
			this.$customPageUrls        = $('.sitemap-settings input.sitemap-custom-url');
			this.$newMetaDataGroupLinks = $('.metadatagroup-isnew');

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
