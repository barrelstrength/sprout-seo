if (typeof Craft.SproutCommerce === typeof undefined)
{
	Craft.SproutCommerce = {};
}
(function($) {

	// Set all the standard Craft.SproutFields.* stuff
	$.extend(Craft.SproutCommerce,
	{
		initFields: function($container) {
			$('.sproutaddressinfo-box', $container).SproutCommerceField();
		}
	});

	// -------------------------------------------
	//  Custom jQuery plugins
	// -------------------------------------------

	$.extend($.fn,
	{
		SproutCommerceField: function() {
			return this.each(function() {
				if (!$.data(this, 'sproutaddress-edit')) {
					new Craft.SproutCommerce.AddressBox(this);
				}
			});
		},
	});


Craft.SproutCommerce.AddressBox = Garnish.Base.extend({

	$addressBox: null,
	addressInfoId: null,
	$addressForm: null,
	countryCode: null,
	actionUrl: null,
	$none: null,
	modal: null,
	$editButton: null,
	init: function($addressBox, settings)
	{
		this.$addressBox = $addressBox;

		this.addressInfoId = this.$addressBox.data('addressinfoid');

		this._renderAddress();

		this.addListener(this.$editButton, 'click', 'editAddressBox');
	},
	_renderAddress: function()
	{
		//this.$addressBox.html("");

		var $buttons = $("<div class='address-buttons'/>").appendTo(this.$addressBox);

		this.$editButton = $("<a class='small btn right edit sproutaddress-edit' href=''>" + Craft.t("Edit") + "</a>").appendTo($buttons);

		$("<div class='address-format' />").appendTo(this.$addressBox);

		this.$none = $("<div style='display: none' />").appendTo(this.$addressBox);
		this.$addressForm = $("<div class='sproutaddress-form' />").appendTo(this.$none);
		//this.$addressForm = this.$addressBox.find('.sproutaddress-form');

		this._updateAddressFormat();

		this.actionUrl = Craft.getActionUrl('sproutCommerce/address/changeForm');

		//var countryCode = this.$addressForm.find('.sproutaddress-country-select select').val();
		//console.log('upd');
		//console.log(this.$addressForm.html())
		//var addressForm = new SproutCommerce.AddressForm.init(this.$addressForm, { countryCode: countryCode, actionUrl: this.actionUrl } );

		//this._attachListeners();
	},
	editAddressBox: function (ev) {

			ev.preventDefault();

			this.$target = $(ev.currentTarget);

			var countryCode = this.$addressForm.find('.sproutaddress-country-select select').val();

			this.modal = new Craft.SproutCommerce.EditAddressModal(this.$addressForm, {
				onSubmit: $.proxy(this, '_saveAddress'),
				countryCode: countryCode,
				actionUrl: this.actionUrl,
				addressInfoId: this.addressInfoId,
				namespace: 'address'
			}, this.$target);

	},
	_updateAddressFormat: function ()
	{
		var self = this;
		Craft.postActionRequest('sproutCommerce/address/updateAddressFormat', { addressInfoId: this.addressInfoId }, $.proxy(function (response) {
			self.$addressBox.find('.address-format').append(response.html);
			self.$addressForm.append(response.countryCodeHtml);
			self.$addressForm.append(response.formInputHtml);
		}, this))
	},
	_saveAddress: function(data, onError)
	{
		var self = this;

		Craft.postActionRequest('sproutCommerce/address/saveAddress', data, $.proxy(function (response) {
			if (response.result == true)
			{
				self.$addressBox.find('.address-format').html(response.html);
				self.$addressForm.empty();
				self.$addressForm.append(response.countryCodeHtml);
				self.$addressForm.append(response.formInputHtml);

				Craft.cp.displayNotice(Craft.t('Address Updated.'));

				this.modal.hide();
				this.modal.destroy();
			}
			else
			{
				Garnish.shake(this.modal.$form);
				onError(response.errors);
			}
		}, this))
	}
})
})(jQuery);