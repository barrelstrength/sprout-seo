if (typeof Craft.SproutSeo === typeof undefined) {
	Craft.SproutSeo = {};
}
(function($) {

	// Set all the standard Craft.SproutFields.* stuff
	$.extend(Craft.SproutSeo,
	{
		initFields: function($container) {
			$('.sproutaddressinfo-box', $container).SproutAddressBox();
		}
	});

	// -------------------------------------------
	//  Custom jQuery plugins
	// -------------------------------------------

	$.extend($.fn,
	{
		SproutAddressBox: function() {
			$container = $(this);
			return this.each(function() {
				console.log('sumpin sumpin');
				new Craft.SproutSeo.AddressBox($container);
			});
		}
	});

	Craft.SproutSeo.AddressBox = Garnish.Base.extend({

		$addressBox: null,

		$addButtons:    null,
		$editButtons:   null,
		$addressFormat: null,

		$addButton:    null,
		$updateButton: null,
		$clearButton:  null,
		$queryButton:  null,

		addressInfoId: null,
		$addressForm:  null,
		countryCode:   null,
		actionUrl:     null,
		$none:         null,
		modal:         null,

		init: function($addressBox, settings) {

			this.$addressBox = $addressBox;

			this.$addButton    = this.$addressBox.find('.address-add-button a');
			this.$updateButton = this.$addressBox.find('.address-edit-buttons a.update-button');
			this.$clearButton  = this.$addressBox.find('.address-edit-buttons a.clear-button');
			this.$queryButton  = $('.query-button');

			this.$addButtons    = this.$addressBox.find('.address-add-button');
			this.$editButtons   = this.$addressBox.find('.address-edit-buttons');
			this.$addressFormat = this.$addressBox.find('.address-format');

			this.settings = settings;

			if (this.settings.namespace == null) {
				this.settings.namespace = 'address';
			}

			this.addressInfoId = this.$addressBox.data('addressinfoid');

			this._renderAddress();

			this.addListener(this.$addButton, 'click', 'editAddressBox');
			this.addListener(this.$updateButton, 'click', 'editAddressBox');
			this.addListener(this.$clearButton, 'click', 'clearAddressBox');
			this.addListener(this.$queryButton, 'click', 'queryGoogleMaps');
		},

		_renderAddress: function() {

			if (this.addressInfoId == '' || this.addressInfoId == null) {
				this.$addButtons.removeClass('hidden');
				this.$editButtons.addClass('hidden');
				this.$addressFormat.addClass('hidden');
			}
			else {

				this.$addButtons.addClass('hidden');
				this.$editButtons.removeClass('hidden');
				this.$addressFormat.removeClass('hidden');
			}

			this.$addressForm = $("<div class='sproutaddress-form hidden' />").appendTo(this.$addressBox);

			this._getAddressFormFields();

			this.actionUrl = Craft.getActionUrl('sproutSeo/address/changeForm');
		},

		editAddressBox: function(ev) {

			ev.preventDefault();

			var source = null;

			if (this.settings.source != null) {
				source = this.settings.source;
			}

			this.$target = $(ev.currentTarget);

			var countryCode = this.$addressForm.find('.sproutaddress-country-select select').val();

			this.modal = new Craft.SproutSeo.EditAddressModal(this.$addressForm, {
				onSubmit:      $.proxy(this, '_getAddress'),
				countryCode:   countryCode,
				actionUrl:     this.actionUrl,
				addressInfoId: this.addressInfoId,
				namespace:     this.settings.namespace,
				source:        source
			}, this.$target);

		},

		clearAddressBox: function(ev) {

			ev.preventDefault();

			var self = this;
			var data = { addressInfoId: self.addressInfoId };

			this.$addButtons.removeClass('hidden');
			this.$editButtons.addClass('hidden');
			this.$addressFormat.addClass('hidden');
			$( ".sproutaddressinfo-box" ).data( "addressinfoid", "" );

			self.addressInfoId = null;

			this._getAddressFormFields();
		},

		queryGoogleMaps: function(ev) {

			ev.preventDefault();

			var self = this;

			if (self.addressInfoId)
			{
				var data = { addressInfoId: self.addressInfoId };

				Craft.postActionRequest('sproutSeo/address/queryAddress', data, $.proxy(function(response) {
						if (response.result == true) {
							var latitude  = response.geo.latitude;
							var longitude = response.geo.longitude;

							$("input[name='sproutseo[globals][identity][latitude]']").val(latitude);
							$("input[name='sproutseo[globals][identity][longitude]']").val(longitude);

							Craft.cp.displayNotice(Craft.t('Latitude and Longitude updated.'));
						}
						else {
							onError(response.errors);
						}
				}, this))

			}
			else
			{
				Craft.cp.displayError(Craft.t('Please save globals with an address'));
			}
		},

		_getAddressFormFields: function() {

			var self = this;

			Craft.postActionRequest('sproutSeo/address/getAddressFormFields', {
				addressInfoId: this.addressInfoId,
				namespace:     this.settings.namespace
			}, $.proxy(function(response) {

				this.$addressBox.find('.address-format .spinner').remove();
				self.$addressBox.find('.address-format').empty();
				self.$addressBox.find('.address-format').append(response.html);
				self.$addressForm.empty();
				self.$addressForm.append(response.countryCodeHtml);
				self.$addressForm.append(response.formInputHtml);

			}, this))
		},

		_getAddress: function(data, onError) {

			var self = this;

			Craft.postActionRequest('sproutSeo/address/getAddress', data, $.proxy(function(response) {
				if (response.result == true) {

					this.$addressBox.find('.address-format').html(response.html);
					self.$addressForm.empty();
					self.$addressForm.append(response.countryCodeHtml);
					self.$addressForm.append(response.formInputHtml);

					self.$addButtons.addClass('hidden');
					self.$editButtons.removeClass('hidden');
					self.$addressFormat.removeClass('hidden');

					this.modal.hide();
					this.modal.destroy();
				}
				else {
					Garnish.shake(this.modal.$form);
					onError(response.errors);
				}

			}, this));
		}
	})
})(jQuery);