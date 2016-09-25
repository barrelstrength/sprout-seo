(function($) {

	Craft.SproutBase = Garnish.Base.extend(
	{
		pluginName:        null,
		pluginUrl:         null,
		pluginVersion:     null,
		pluginDescription: null,
		developerName:     null,
		developerUrl:      null,

		init: function(settings) {
			this.pluginName        = settings.pluginName;
			this.pluginUrl         = settings.pluginUrl;
			this.pluginVersion     = settings.pluginVersion;
			this.pluginDescription = settings.pluginDescription;
			this.developerName     = settings.developerName;
			this.developerUrl      = settings.developerUrl;

			this.initUiElements();
		},

		initUiElements: function() {
			this.displayFooter();
		},

		displayFooter: function() {
			brandHtml = '<ul>';
			brandHtml += '<li><a href="' + this.pluginUrl + '" target="_blank">' + this.pluginName + '</a> ' + this.pluginVersion + '</li>';
			brandHtml += '<li>' + this.pluginDescription + '</li>';
			brandHtml += '<li> Designed by <a href="' + this.developerUrl + '" target="_blank">' + this.developerName + '</a></li>';
			brandHtml += '</ul>';

			$('#footer').append(brandHtml);
		},

	});

})(jQuery);
