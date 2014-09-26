(function($) {

Craft.SproutSeoSitemap = Garnish.Base.extend(
{

	$checkboxes: null,
	$selectDropdowns: null,

	$customPageUrls: null,

	$status: null,
	$id: null,
	$sectionId: null,
	$url: null,
	$priority: null,
	$changeFrequency: null,
	$enabled: null,
	$ping: null,

	$addCustomPageButton: null,

	init: function()
	{
		this.$checkboxes = $('.sitemap-settings input[type="checkbox"]');
		this.$selectDropdowns = $('.sitemap-settings select');
		this.$customPageUrls = $('.sitemap-settings input.sitemap-custom-url');

		// this.$addCustomPageButton = $('#add-custom-page');

		this.addListener(this.$checkboxes, 'change', 'onChange');
		this.addListener(this.$selectDropdowns, 'change', 'onChange');
		this.addListener(this.$customPageUrls, 'change', 'onChange');
		// this.addListener(this.$addCustomPageButton, 'click', 'addCustomPage');
	},

	// addCustomPage: function()
	// {
	// 	var $customPageTable = $('.custom-pages');
	// 	var $lastRow = $customPageTable.find("tr:last");
	// 	lastId = $lastRow.data('rowid');
	// 	lastValue = $lastRow.find("input.sitemap-custom-url").val();
	//
	// 	var $newRow = $lastRow.clone(true);
	//
	// 	console.log($newRow);
	// 	$newRow.attr('data-rowid','new-0');
	//
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][id]"]').val('new-0');
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][id]"]').attr('name', 'sitemap_fields[new-0][id]');
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][sectionId]"]').attr('name', 'sitemap_fields[new-0][sectionId]');
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][url]"]').attr('name', 'sitemap_fields[new-0][url]');
	// 	$newRow.find('select[name="sitemap_fields['+lastId+'][priority]"]').attr('name', 'sitemap_fields[new-0][priority]');
	// 	$newRow.find('select[name="sitemap_fields['+lastId+'][changeFrequency]"]').attr('name', 'sitemap_fields[new-0][changeFrequency]');
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][enabled]"]').attr('name', 'sitemap_fields[new-0][enabled]');
	// 	$newRow.find('input[name="sitemap_fields['+lastId+'][ping]"]').attr('name', 'sitemap_fields[new-0][ping]');
	//
	// 	$newRow.find("input.sitemap-custom-url").val('');
	// 	$lastRow.after($newRow);
	//
	// },

	onChange: function(ev)
	{
		changedElement = ev.target;
		rowId = $(changedElement).closest('tr').data('rowid');

		this.status = $('tr[data-rowid="'+rowId+'"] td span.status');
		this.id = $('input[name="sitemap_fields['+rowId+'][id]"]').val();
		this.sectionId = $('input[name="sitemap_fields['+rowId+'][sectionId]"]').val();
		this.url = $('input[name="sitemap_fields['+rowId+'][url]"]').val();
		this.priority = $('select[name="sitemap_fields['+rowId+'][priority]"]').val();
		this.changeFrequency = $('select[name="sitemap_fields['+rowId+'][changeFrequency]"]').val();
		this.enabled = $('input[name="sitemap_fields['+rowId+'][enabled]"]').is(":checked");
		this.ping = $('input[name="sitemap_fields['+rowId+'][ping]"]').is(":checked");

		console.log('new request');
		console.log(this.status);
		console.log(this.id);
		console.log(this.sectionId);
		console.log(this.url);
		console.log(this.priority);
		console.log(this.changeFrequency);
		console.log(this.enabled);
		console.log(this.ping);
		console.log('end request');

		if (this.enabled)
		{
			this.status.removeClass('disabled');
			this.status.addClass('live');
			$('input[name="sitemap_fields['+rowId+'][ping]"]').attr("disabled", false);
		}
		else
		{
			this.status.removeClass('live');
			this.status.addClass('disabled');
			$('input[name="sitemap_fields['+rowId+'][ping]"]').prop('checked', false);
			$('input[name="sitemap_fields['+rowId+'][ping]"]').attr("disabled", true);
			this.ping = false;
		}

		Craft.postActionRequest('sproutSeo/sitemap/saveSitemap', {
			id: this.id,
			sectionId: this.sectionId,
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
					$(changedElement).closest('tr').data('rowid', response.lastInsertId);

					$('input[name="sitemap_fields['+rowId+'][id]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][id]');
					$('input[name="sitemap_fields['+response.lastInsertId+'][id]"]').val(response.lastInsertId);
					$('input[name="sitemap_fields['+rowId+'][sectionId]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][sectionId]');
					$('input[name="sitemap_fields['+rowId+'][url]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][url]');
					$('select[name="sitemap_fields['+rowId+'][priority]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][priority]');
					$('select[name="sitemap_fields['+rowId+'][changeFrequency]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][changeFrequency]');
					$('input[name="sitemap_fields['+rowId+'][enabled]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][enabled]');
					$('input[name="sitemap_fields['+rowId+'][ping]"]').attr('name', 'sitemap_fields['+response.lastInsertId+'][ping]');

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
