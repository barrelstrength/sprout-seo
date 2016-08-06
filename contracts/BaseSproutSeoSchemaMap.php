<?php
namespace Craft;

abstract class BaseSproutSeoSchemaMap
{
	/**
	 * @var SproutSeo_SchemaModel
	 */
	public $globals;


	public function __construct()
	{
		$this->globals = sproutSeo()->schema->getGlobals();
	}

	/**
	 * @return string
	 */
	final public function getContext()
	{
		return "http://schema.org/";
	}

	/**
	 * Human readable schema name. Admin user will select this schema by this name in the Control Panel.
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Schema.org data type: http://schema.org/docs/full.html
	 *
	 * @return string
	 */
	abstract public function getType();

	// Does syntax user a generic `object` or do we need to assume 
	// we know specifically what the variable is called?
	// 
	// Have some out of box helper methods like getFirst()
	// Do we really need the @methodName syntax? or do we just write this in PHP?
	public function getAttributes()
	{
		return array(
			'name' => 'product.title',
			'image' => '@getFirst(product.featureImage)',
			'image' => $this->getFirst('product.featureImage'),
			'image' => $this->element->featureImage->first(),
			'description' => 'product.description',
			'sku' => 'product.sku',

			// How does this work? Is this defined here or does it referenec another integration? Every @type references a unique integration...
			'brand' => array(
				'@type' => 'Thing',
				'name' => 'ACME'
			),
		);
	}

	// Should we let integrations give users a chance to set setings in the CP UI?
	// public function getSettings()
	// {
	//    ??
	// }

	// method that gets called at top level to 
	// process the settings map and return a 
	// usable JSON-LD schema object
	public function getSchema()
	{
		$attributes = $this->getAttributes();

		$schema['@context'] = $this->getContext();
		$schema['@type'] = $this->getType();

		foreach ($attributes as $key => $value)
		{
			// Loop through each array attribute and build the schema
			// depending on what type of attribute 'value' is:
			// '@method' vs. 'value' vs. ???
			$schema[$key] = $value;
		}

		return '
<script type="application/ld+json">
' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '
</script>';

	}

	/**
	 * Allow our schema to define what a generic or fake object will look like
	 * Give the user a way to refresh or generate a new random mock object in the UI
	 * And then run the markup from that UI directly into the Structured Data testing tool to validate
	 *
	 * @return null
	 */
	public function getMockData() 
	{
		return null;
	}
}
