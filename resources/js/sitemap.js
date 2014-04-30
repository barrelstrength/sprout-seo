(function($) {

Craft.SproutSeoSitemap = Garnish.Base.extend(
{
	
	$checkboxes: null,
	$selectDropdowns: null,

	init: function()
	{
		this.$checkboxes = $('.sitemap-settings input[type="checkbox"]');
		this.$selectDropdowns = $('.sitemap-settings select');

		this.addListener(this.$checkboxes, 'change', 'onChange');
		this.addListener(this.$selectDropdowns, 'change', 'onChange');
	},

	onChange: function(ev)
	{
		changedElement = ev.target;
		sectionId = $(changedElement).closest('tr').data('sectionid');

		priority = $('select[name="sitemap_fields['+sectionId+'][priority]').val();
		changeFrequency = $('select[name="sitemap_fields['+sectionId+'][changeFrequency]').val();
		enabled = $('input[name="sitemap_fields['+sectionId+'][enabled]').is(":checked");
		ping = $('input[name="sitemap_fields['+sectionId+'][ping]').is(":checked");
		
		// console.log(priority);
		// console.log(changeFrequency);
		// console.log(enabled);
		// console.log(ping);

		Craft.postActionRequest('sproutSeo/sitemap/saveSitemap', { 
			sectionId: sectionId,
			priority: priority,
			changeFrequency: changeFrequency,
			enabled: enabled,
			ping: ping,
		}, $.proxy(function(response, textStatus)
		{
			if (textStatus == 'success')
			{
				if (response.success)
				{
					Craft.cp.displayNotice(Craft.t('Sitemap setting saved.'));
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