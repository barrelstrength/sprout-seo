<?php

class SproutSeo_ProductSchemaMap extends BaseSproutSeoSchemaMap
{
	// Human readable schema name for UI
	public function getName()
	{
		return 'Product';
	}

	// Probably just defined at the top level.
	public function getContext()
	{
		return "http://schema.org/";
	}

	/**
	 * Schema.org @type name. Each integration will be required to define this.
	 */
	public function getType()
	{
		return 'Product';
	}

	// Does syntax user a generic `object` or do we need to assume
	// we know specifically what the variable is called?
	//
	// Have some out of box helper methods like getFirst()
	// Do we really need the @methodName syntax? or do we just write this in PHP?
	public function getAttributes()
	{
		return array(
			'name'        => 'product.title',
			'image'       => '@getFirst(product.featureImage)',
			'image'       => $this->getFirst('product.featureImage'),
			'image'       => $this->element->featureImage->first(),
			'description' => 'product.description',
			'sku'         => 'product.sku',

			// How does this work? Is this defined here or does it referenec another integration? Every @type references a unique integration...
			'brand'       => array(
				'@type' => 'Thing',
				'name'  => 'ACME'
			),
		);
	}

	// Should we let integrations give users a chance to set setings in the CP UI?
	public function getSettings()
	{
		// ??
	}

	// method that gets called at top level to
	// process the settings map and return a
	// usable JSON-LD schema object
	protected function getSchema()
	{
		$attributes = $this->getAttributes();

		$schema['@context'] = $this->getContext();
		$schema['@type']    = $this->getType();

		foreach ($attributes as $key => $value)
		{
			// Loop through each array attribute and build the schema
			// depending on what type of attribute 'value' is:
			// '@method' vs. 'value' vs. ???
			$schema[$key] = $value;
		}

		return JsonHelper::encode($schema);
	}

	// Allow our schema to define what a generic or fake object will look like
	// Give the user a way to refresh or generate a new random mock object in the UI
	// And then run the markup from that UI directly into the Structured Data testing tool to validate
	public function getMockData()
	{
		return craft()->commerce->getProducts()->first();
	}

	// <script type="application/ld+json">
	// {
	//   "@context": ,
	//   "@type": "Product",
	//   "name": "Executive Anvil",
	//   "image": "http://www.example.com/anvil_executive.jpg",
	//   "description": "Sleeker than ACME's Classic Anvil, the Executive Anvil is perfect for the business traveler looking for something to drop from a height.",
	//   "mpn": "925872",
	//   "brand": {
	//     "@type": "Thing",
	//     "name": "ACME"
	//   },
	//   "aggregateRating": {
	//     "@type": "AggregateRating",
	//     "ratingValue": "4.4",
	//     "reviewCount": "89"
	//   },
	//   "offers": {
	//     "@type": "Offer",
	//     "priceCurrency": "USD",
	//     "price": "119.99",
	//     "priceValidUntil": "2020-11-05",
	//     "itemCondition": "http://schema.org/UsedCondition",
	//     "availability": "http://schema.org/InStock",
	//     "seller": {
	//       "@type": "Organization",
	//       "name": "Executive Objects"
	//     }
	//   }
	// }
	// </script>
}