<?php
namespace Craft;

/**
 * Class SproutSeoBaseSchemaMap
 */
abstract class SproutSeoBaseSchemaMap
{
	/**
	 * @var array
	 */
	public $attributes;

	/**
	 * @var null
	 */
	public $sitemapInfo;

	/**
	 * @var bool
	 */
	public $isContext;

	/**
	 * SproutSeoBaseSchemaMap constructor.
	 *
	 * @param array|null $attributes
	 * @param bool       $isContext
	 * @param null       $sitemapInfo
	 */
	public function __construct(array $attributes = null, $isContext = true, $sitemapInfo = null)
	{
		if (!empty($attributes))
		{
			$this->attributes = $attributes;
		}

		if (isset($sitemapInfo))
		{
			$this->sitemapInfo = $sitemapInfo;
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
   * Returns a key that uniquely identifies the schema map integration
   *
   * Example:
   * class: Craft\\SproutSeo_ContactPointSchemaMap
   * unique key: craft-sproutseo-contactpointschemamap
   *
   * @return string
   */
	final public function getUniqueKey()
  {
	  return str_replace('_', '-', ElementHelper::createSlug(get_class($this)));
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

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return array();
	}

	/**
	 * Convert Schema Map attributes to valid JSON-LD
	 *
	 * @return string
	 */
	final public function getSchema()
	{
		$attributes = $this->getProperties();

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
			// @todo Craft 3.0 - clean up logic once we can ditch PHP 5.3
			$output = (version_compare(PHP_VERSION, '5.4.0', '>='))
			  ? json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
				: str_replace('\\/', '/', json_encode($schema));

			return '
<script type="application/ld+json">
' . $output . '
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

	// Helper Methods
	// =========================================================================

	/**
	 * @param $dateTime
	 *
	 * @return null
	 */
	public function getDateFromDatetime($dateTime)
	{
		$date = null;

		if ($dateTime)
		{
			$date = $dateTime->format('Y-m-d');
		}

		return $date;
	}

	/**
	 * Returns jsonLd for a image object id
	 *
	 * @param $imageId int
	 *
	 * @return mixed
	 */
	public function getSchemaImageById($imageId)
	{
		$image  = craft()->assets->getFileById($imageId);
		$schema = "";

		if ($image)
		{
			$asset = array(
				"url"    => SproutSeoOptimizeHelper::getAssetUrl($image->id),
				"width"  => $image->getWidth(),
				"height" => $image->getHeight()
			);

			$imageObjectSchemaMap = new SproutSeo_ImageObjectSchemaMap(array(
				'image' => $asset
			), false);

			$schema = $imageObjectSchemaMap->getSchema();
		}

		return $schema;
	}
}
