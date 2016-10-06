var SproutCommerce = {};

SproutCommerce.AddressForm = {

	settings: null,
	$element: null,
	changeFormInput: null,
	resultHtml: null,
	init: function($element, settings)
	{
		this.$element = $element;
		this.settings = settings;

		var actionUrl = this.settings.actionUrl;
		var countryCode = this.settings.countryCode;

		$element.find('.sproutaddress-country-select select').change($.proxy(function(e) {

			var target = e.currentTarget

			var $target     = $(target);
			var countryCode = $target.val();

			this.changeFormInput(countryCode, this)
		}, this))

		// public method
		var self = this;
		this.initFormInput = $.proxy(function(countryCode) {
			return SproutCommerce.AddressForm.changeFormInput(countryCode)
		},this)
	},
	changeFormInput: function(countryCode, obj) {

		var data = {
			'countryCode' : countryCode
		}

		var actionUrl = obj.settings.actionUrl;

		var self = obj;
		$.post(actionUrl, data, $.proxy(function(response) {
			self.$element.find('.field-address-input').remove();
			self.$element.append(response)
		}, this))
	},
	defaults: {
		onChange: $.noop
	}
}