if (typeof Craft.SproutFields === typeof undefined)
{
	Craft.SproutFields = {};
}

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

		if (this.$dropdownField.val() == 'custom')
		{
			// Remove the Select Field and it's wrapping div
			this.$dropdownField.val('');
			this.$dropdownField.parent().addClass('hidden');

			this.$textField.parent().removeClass('hidden');
			this.$textField.find('input').focus();

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


(function() {

	// initialize Select Other fields
	$('.sprout-selectother').each(function() {
		new Craft.SproutFields.SelectOtherField(this);
	});

})();