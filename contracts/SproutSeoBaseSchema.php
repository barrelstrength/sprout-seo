<?php
namespace Craft;

/**
 * Class SproutSeoBaseSchema
 */
abstract class SproutSeoBaseSchema
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
	 * We build our Structured Data object here using the addProperty methods
	 * and can later convert this into JsonLD using the ->getJsonLd() method
	 *
	 * @var
	 */
	public $structuredData = array();

	/**
	 * @var
	 */
	public $globals;

	/**
	 * The Matched Element or Primary Element of the schema
	 *
	 * @var
	 */
	public $element;

	public $prioritizedMetadataModel;

	/**
	 * SproutSeoBaseSchema constructor.
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

	public function setElement()
	{
		$this->element = sproutSeo()->optimize->matchedElementModel;
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
	 * class: Craft\\SproutSeo_ContactPointSchema
	 * unique key: craft-sproutseo-contactpointschema
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
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return false;
	}

	/**
	 * @return array
	 */
	abstract public function addProperties();

	/**
	 * Convert Schema Map attributes to valid JSON-LD
	 *
	 * @return string
	 */
	final public function getSchema()
	{
		$this->addProperties();

		if (empty($this->structuredData))
		{
			return null;
		}

		if ($this->isContext)
		{
			// Add the @context tag for the full context
			$schema['@context'] = $this->getContext();
		}

		// Grab the type after we process the attributes in case we need to set it dynamically
		$schema['@type'] = $this->getType();

		foreach ($this->structuredData as $key => $value)
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

	public function addProperty($propertyName, $attributes)
	{
		$this->structuredData[$propertyName] = $attributes;
	}

	public function addText($propertyName, $string)
	{
		if (is_string($string))
		{
			$this->structuredData[$propertyName] = $string;
		}
	}

	public function addBoolean($propertyName, $bool)
	{
		if (is_bool($bool))
		{
			$this->structuredData[$propertyName] = $bool;
		}
	}

	public function addNumber($propertyName, $number)
	{
		if (is_int($number) OR is_float($number))
		{
			$this->structuredData[$propertyName] = $number;
		}
	}

	/**
	 * Format a date string into ISO 8601
	 * https://schema.org/Date
	 * https://en.wikipedia.org/wiki/ISO_8601
	 *
	 * @param $propertyName
	 * @param $date
	 */
	public function addDate($propertyName, $date)
	{
		$dateTime = new DateTime($date);

		$this->structuredData[$propertyName] = $dateTime->format('c');
	}

	public function addUrl($propertyName, $url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL) === false)
		{
			// Valid URL
			$this->structuredData[$propertyName] = $url;
		}
		else
		{
			SproutSeoPlugin::log("Schema unable to add value. Value is not a valid URL.");
		}
	}

	public function addTelephone($propertyName, $phone)
	{
		if (is_string($phone))
		{
			$this->structuredData[$propertyName] = $phone;
		}
		else
		{
			SproutSeoPlugin::log("Schema unable to add value. Value is not a valid Phone.");
		}
	}

	public function addEmail($propertyName, $email)
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			// Valid Email
			$this->structuredData[$propertyName] = $email;
		}
		else
		{
			SproutSeoPlugin::log("Schema unable to add value. Value is not a valid Email.");
		}
	}

	/**
	 * Returns jsonLd for a image object id
	 *
	 * @param $imageId int
	 *
	 * @return mixed
	 */
	public function addImage($propertyName, $imageId = null)
	{
		if (!isset($imageId))
		{
			return null;
		}

		$image = array();

		if (!filter_var($imageId, FILTER_VALIDATE_URL) === false)
		{
			$meta = $this->prioritizedMetadataModel;

			$image = array(
				"url"    => $meta->optimizedImage,
				"width"  => $meta->ogImageWidth,
				"height" => $meta->ogImageHeight
			);
		}
		else
		{
			if ($image = craft()->assets->getFileById($imageId))
			{
				$image = array(
					"url"    => SproutSeoOptimizeHelper::getAssetUrl($image->id),
					"width"  => $image->getWidth(),
					"height" => $image->getHeight()
				);
			}
		}

		if (count($image))
		{
			$imageObjectSchema = new SproutSeo_ImageObjectSchema();
			$imageObjectSchema->isContext = false;
			$imageObjectSchema->element = $image;

			$this->structuredData[$propertyName] = $imageObjectSchema->getSchema();
		}
	}

	public function addSameAs($urls)
	{
		if (count($urls))
		{
			$sameAsList = array();

			foreach ($urls as $url)
			{
				$sameAsList[] = $url;
			}

			$this->structuredData['sameAs'] = array_values($sameAsList);
		}
	}

	public function addContactPoints($contacts = array())
	{
		if (count($contacts))
		{
			$contactPoints = array();

			foreach ($contacts as $contact)
			{
				$contactPointSchemaMap = new SproutSeo_ContactPointSchema();
				$contactPointSchemaMap->isContext = false;
				$contactPointSchemaMap->contact = $contact;

				$contactPoints[] = $contactPointSchemaMap->getSchema();
			}

			$this->structuredData['contactPoint'] = $contactPoints;
		}
	}

	public function addOpeningHours($openingHours = array())
	{
		$days         = array(0 => "Su", 1 => "Mo", 2 => "Tu", 3 => "We", 4 => "Th", 5 => "Fr", 6 => "Sa");
		$index        = 0;

		foreach ($openingHours as $key => $value)
		{
			$openingHours[$index] = $days[$index];

			if (isset($value['open']['time']) && $value['open']['time'] != '')
			{
				$time = DateTime::createFromString($value['open']);
				$openingHours[$index] .= " " . $time->format('H:m');
			}

			if (isset($value['close']['time']) && $value['close']['time'] != '')
			{
				$time = DateTime::createFromString($value['close']);
				$openingHours[$index] .= "-" . $time->format('H:m');
			}

			// didn't work this day
			if (strlen($openingHours[$index]) == 2)
			{
				unset($openingHours[$index]);
			}

			$index++;
		}

		if (count(array_values($openingHours)))
		{
			// Prepare opening hours as one dimensional array
			$this->structuredData['openingHours'] = array_values($openingHours);
		}
	}

	public function addMainEntityOfPage($type = 'Thing')
	{
		$meta = $this->prioritizedMetadataModel;

		$mainEntity = new SproutSeo_MainEntityOfPageSchema();
		$mainEntity->isContext = false;
		$mainEntity->type = $type;
		$mainEntity->id = $meta->canonical;

		$this->structuredData['mainEntityOfPage'] = $mainEntity->getSchema();
	}
}
