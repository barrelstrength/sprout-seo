if (typeof Craft.SproutFields === typeof undefined)
{
	Craft.SproutFields = {};
}

(function($){

// Set all the standard Craft.SproutFields.* stuff
$.extend(Craft.SproutFields,
{
	initFields: function($container)
	{
		$('.sprout-selectother', $container).sproutSelectOther();
	}
});

// -------------------------------------------
//  Custom jQuery plugins
// -------------------------------------------

$.extend($.fn,
{
	sproutSelectOther: function()
	{
		return this.each(function()
		{
			if (!$.data(this, 'sprout-selectother'))
			{
				new Craft.SproutFields.SelectOtherField(this);
			}
		});
	},
});

Craft.SproutFields.SelectOtherField = Garnish.Base.extend(
{
	$container: null,

	$dropdownField: null,
	$textField: null,
	$clearIcon: null,

	init: function(container)
	{
		this.$container = $(container);

		this.$dropdownField = this.$container.find('.sprout-selectotherdropdown select');
		this.$textField = this.$container.find('.sprout-selectothertext input');
		this.$clearIcon = this.$container.find('.sprout-selectothertext .clear');

		this.addListener(this.$dropdownField, 'change', 'handleSelectOtherChange');
		this.addListener(this.$clearIcon, 'click', 'handleCancelOther');
	},

	handleSelectOtherChange: function()
	{
		selectedValue = this.$dropdownField.val();

		if (selectedValue == 'custom')
		{
			// Remove the Select Field and it's wrapping div
			this.$dropdownField.val('');
			this.$dropdownField.parent().addClass('hidden');

			this.$textField.parent().removeClass('hidden');
			this.$textField.find('input').focus();
		}
		else
		{
			// Store the selected value in the other field, as it takes precedence
			this.$textField.val(selectedValue);
		}

	},

	handleCancelOther: function()
	{

		// Hide our Custom text field
		this.$textField.parent().addClass('hidden');

		// Show our dropdown again
		this.$dropdownField.parent().removeClass('hidden');

	},

});

})(jQuery);