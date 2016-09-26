(function($) {

	Craft.SproutSeoSitemap = Garnish.Base.extend(
	{
		$checkboxes:      null,
		$selectDropdowns: null,

		$customPageUrls: null,

		$status:                  null,
		$id:                      null,
		$elementGroupId:          null,
		$url:                     null,
		$priority:                null,
		$changeFrequency:         null,
		$enabled:                 null,
		$newSectionMetadataLinks: null,

		$addCustomPageButton: null,

		init: function() {
			this.$checkboxes              = $('.sitemap-settings input[type="checkbox"]');
			this.$selectDropdowns         = $('.sitemap-settings select');
			this.$customPageUrls          = $('.sitemap-settings input.sitemap-custom-url');
			this.$newSectionMetadataLinks = $('.sectionmetadata-isnew');

			this.addListener(this.$newSectionMetadataLinks, 'click', 'redirectToSectionMetadataEditTemplate');
		},

		redirectToSectionMetadataEditTemplate: function(event) {

			target    = event.target;
			isNew     = $(target).data('isnew');
			submitUrl = Craft.getUrl('sproutseo/sections/new');

			data = {
				"sectionmetadataname": $(target).data('sectionmetadataname'),
				"elementgrouphandle":  $(target).data('elementgrouphandle'),
				"sitemapid":           $(target).data('sitemapid'),
				"elementgroupid":      $(target).data('elementgroupid')
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
