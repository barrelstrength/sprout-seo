<?php
namespace Craft;

abstract class BaseSproutSeoSchemaMap
{
	public $attributes;
	private $isContext;

	public function __construct($attributes = null, $isContext = true)
	{
		if (isset($attributes))
		{
			$this->attributes = $attributes;
		}

		if (isset($isContext))
		{
			$this->isContext = $isContext;
		}
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
		return null;
	}

	// Should we let integrations give users a chance to set setings in the CP UI?
	// public function getSettings()
	// {
	//    ??
	// }

	/**
	 * Convert Schema Map attributes to valid JSON-LD
	 *
	 * @return string
	 */
	public function getSchema()
	{
		$attributes = $this->getAttributes();


		if ($this->isContext)
		{
			// Add the @context tag for the full context
			$schema['@context'] = $this->getContext();
		}

		// Grab the type after we process the attributes in case we need to set it dynamically
		$schema['@type'] = $this->getType();

		foreach ($attributes as $key => $value)
		{
			// Loop through each array attribute and build the schema
			// depending on what type of attribute 'value' is:
			// '@method' vs. 'value' vs. ???
			$schema[$key] = $value;
		}

		if ($this->isContext)
		{
			// Return the JSON-LD script tag and full context
			return '
<script type="application/ld+json">
' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '
</script>';

		}
		else
		{
			// If context has already been established, just return the data
			return $schema;
		}
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
