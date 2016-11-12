<?php
namespace Craft;

class SproutSeo_ProductSchema extends SproutSeo_ThingSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Product';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Product';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return false;
	}

	/**
	 * @return array
	 */
	public function addProperties()
	{
		parent::addProperties();

		$elementType = $this->element->getElementType();

		if ($elementType == 'Commerce_Product')
		{
			$this->addCommerceProductProperties();
		}
	}

	public function addCommerceProductProperties()
	{
		$identity = $this->globals['identity'];
		$element  = $this->element;

		$primaryCurrencyIso = craft()->commerce_paymentCurrencies->getPrimaryPaymentCurrencyIso();

		$offers = array();

		if ($identityType = $identity['@type'])
		{
			// Determine if we have an Organization or Person Schema Type
			$schemaModel = 'Craft\SproutSeo_WebsiteIdentity' . $identityType . 'Schema';

			$identitySchema          = new $schemaModel();
			$identitySchema->globals = $this->globals;
			$seller                  = $identitySchema->getSchema();
		}

		foreach ($element->variants as $variant)
		{

			$offers[$variant->id]['@type']         = 'Offer';
			$offers[$variant->id]['sku']           = $variant->sku;
			$offers[$variant->id]['price']         = $variant->price;
			$offers[$variant->id]['priceCurrency'] = $primaryCurrencyIso;

			if ($variant->unlimitedStock == 1 OR $variant->stock > 0)
			{
				$availability = 'https://schema.org/InStock';
			}
			else
			{
				$availability = 'https://schema.org/OutOfStock';
			}

			$offers[$variant->id]['availability'] = $availability;
			$offers[$variant->id]['seller']       = $seller;
		}

		$this->addProperty('offers', array_values($offers));
	}
}