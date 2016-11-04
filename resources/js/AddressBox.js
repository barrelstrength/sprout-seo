if (typeof Craft.SproutSeo === typeof undefined)
{
	Craft.SproutSeo = {};
}
(function($) {

	// Set all the standard Craft.SproutFields.* stuff
	$.extend(Craft.SproutSeo,
	{
		initFields: function($container) {
			$('.sproutaddressinfo-box', $container).SproutSeoField();
		}
	});

	// -------------------------------------------
	//  Custom jQuery plugins
	// -------------------------------------------

	$.extend($.fn,
	{
		SproutSeoField: function() {
			return this.each(function() {
				if (!$.data(this, 'sproutaddress-edit')) {
					new Craft.SproutSeo.AddressBox(this);
				}
			});
		},
	});


Craft.SproutSeo.AddressBox = Garnish.Base.extend({

	$addressBox: null,
	addressInfoId: null,
	$addressForm: null,
	countryCode: null,
	actionUrl: null,
	$none: null,
	modal: null,
	$editButton: null,
	$clearButton: null,
	init: function($addressBox, settings)
	{
		this.$addressBox = $addressBox;

		this.settings = settings;

		if (this.settings.namespace == null)
		{
			this.settings.namespace = 'address';
		}

		this.addressInfoId = this.$addressBox.data('addressinfoid');

		this._renderAddress();

		this.addListener(this.$editButton, 'click', 'editAddressBox');
		this.addListener(this.$clearButton, 'click', 'clearAddressBox');
	},
	_renderAddress: function()
	{
		var $buttons = $("<div class='address-buttons'/>").appendTo(this.$addressBox);

		var editLabel = '';
		if (this.addressInfoId == '' || this.addressInfoId == null)
		{
			editLabel = Craft.t("Add Address");
			this.$editButton = $("<a class='btn add icon dashed sproutaddress-edit' href=''>" + editLabel + "</a>").appendTo($buttons);
			this.$clearButton = $("<div></div>").appendTo($buttons);
		}
		else
		{
			editLabel = Craft.t("Update Address");
			this.$editButton = $("<a class='small btn right icon sproutaddress-edit' href=''>" + editLabel + "</a>").appendTo($buttons);
			this.$addressBox.find('.address-format').append("<div class='spinner' />");

			this.$clearButton = $("<span class='clear-button'><a class='small btn right icon sproutaddress-edit' href=''>Clear</a></span>").appendTo($buttons);
		}

		$("<div class='address-format' />").appendTo(this.$addressBox);

		this.$none = $("<div style='display: none' />").appendTo(this.$addressBox);
		this.$addressForm = $("<div class='sproutaddress-form' />").appendTo(this.$none);

		this._getAddressFormFields();

		this.actionUrl = Craft.getActionUrl('sproutSeo/address/changeForm');
	},
	editAddressBox: function (ev) {

			ev.preventDefault();

		  var source = null;
			if (this.settings.source != null)
			{
				source = this.settings.source;
			}
			this.$target = $(ev.currentTarget);

			var countryCode = this.$addressForm.find('.sproutaddress-country-select select').val();

			this.modal = new Craft.SproutSeo.EditAddressModal(this.$addressForm, {
				onSubmit: $.proxy(this, '_getAddress'),
				countryCode: countryCode,
				actionUrl: this.actionUrl,
				addressInfoId: this.addressInfoId,
				namespace: this.settings.namespace,
				source: source
			}, this.$target);

	},
	clearAddressBox: function (ev) {

			ev.preventDefault();
			var self = this;
			var data = {addressInfoId: self.addressInfoId};

		  Craft.postActionRequest('sproutSeo/address/deleteAddress', data, $.proxy(function (response) {
		  	if (response.result == true)
		  	{
		  		self.$editButton.removeClass('small right');
		  		self.$editButton.addClass('add dashed');
		  		self.$editButton.text(Craft.t("Add Address"));

		  		self.addressInfoId = null;
		  		self.$clearButton.html("<div></div>");

		  		this._getAddressFormFields();

		  		Craft.cp.displayNotice(Craft.t('Address Deleted.'));
		  	}
		  	else
		  	{
		  		onError(response.errors);
		  	}
		  }, this))

	},
	_getAddressFormFields: function ()
	{
		var self = this;
		Craft.postActionRequest('sproutSeo/address/getAddressFormFields', { addressInfoId: this.addressInfoId, namespace: this.settings.namespace }, $.proxy(function (response) {
			this.$addressBox.find('.address-format .spinner').remove();
			self.$addressBox.find('.address-format').empty();
			self.$addressBox.find('.address-format').append(response.html);
			self.$addressForm.empty();
			self.$addressForm.append(response.countryCodeHtml);
			self.$addressForm.append(response.formInputHtml);

			Craft.SproutSeo.initFields(self.$addressBox);
		}, this))
	},
	_getAddress: function(data, onError)
	{
		var self = this;

		Craft.postActionRequest('sproutSeo/address/getAddress', data, $.proxy(function (response) {
			if (response.result == true)
			{
				self.$addressBox.find('.address-format').html(response.html);
				self.$addressForm.empty();
				self.$addressForm.append(response.countryCodeHtml);
				self.$addressForm.append(response.formInputHtml);

				self.$editButton.removeClass('add dashed');
				self.$editButton.addClass('small right');
				self.$editButton.text(Craft.t("Update Address"));

				self.$clearButton.html("<span class='clear-button'><a class='btn icon sproutaddress-edit small right' href=''>Clear</a></span>");

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