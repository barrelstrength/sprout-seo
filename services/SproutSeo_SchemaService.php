<?php
namespace Craft;

/**
 * Class SproutSeo_SchemaService
 *
 * @package Craft
 */
class SproutSeo_SchemaService extends BaseApplicationComponent
{
	/**
	 * Full schema.org core and extended vocabulary as described on schema.org
	 * http://schema.org/docs/full.html
	 *
	 * @var array
	 */
	public $vocabularies = array();

	/**
	 * @return string
	 */
	public function getStructureDataHtml()
	{
		$schema = $this->getGlobals();

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$schemaHtml = craft()->templates->render('sproutseo/templates/_special/schema', array(
			'schema' => $schema
		));

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return $schemaHtml;
	}

	/**
	 * @return mixed
	 */
	public function getMainEntityStructuredDataHtml($context = null)
	{
		if (!isset($context))
		{
			return null;
		}

		// 1. Check the page load and see if it has any matchedElement variables

		// Get Enabled Sitemaps and index them by type
		$enabledSitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->where('enabled = 1 and elementGroupId is not null')
			->queryAll();

		foreach ($enabledSitemaps as $key => $enabledSitemap)
		{
			$enabledSitemaps[$enabledSitemap['type']] = $enabledSitemap;
			unset($enabledSitemaps[$key]);
		}

		// Do we need the hook here? Or should we just return Enabled Sitemap integrations from the CP?
		$sitemaps                  = craft()->plugins->call('registerSproutSeoSitemap');
		$matchedElementsByVariable = array();
		$matchedElementsByType     = array();

		// Loop through all of our sitemap integrations and create an array of our matched element variables
		foreach ($sitemaps as $plugin)
		{
			foreach ($plugin as $type => $element)
			{
				if (isset($element['matchedElementVariable']))
				{
					// Create an array of all matched elements using the Element Variable as the key
					$matchedElementsByVariable[$element['matchedElementVariable']]         = $element;
					$matchedElementsByVariable[$element['matchedElementVariable']]['type'] = $type;

					// Create an array of all matched elements using the Group Type as the key
					$matchedElementsByType[$type] = $element;
				}
			}
		}

		// If we don't have any matched elements, this page doesn't have any Main Entity Structured Data
		if (empty($matchedElementsByVariable))
		{
			return null;
		}

		// If we do have matched elements, let's grab their element data from the $context
		$matchedVariables = array_intersect_key($context, $matchedElementsByVariable);
		$matchedVariable = reset($matchedVariables);

		// If we do have matched enabled Sitemaps types, grab the info from their saved Sitemap settings
		// @todo - add the selected SchemaMap target to the sitemap settings. We want to grab it here next.
		$matchedTypes = array_intersect_key($enabledSitemaps, $matchedElementsByType);
		$matchedType = reset($matchedTypes);

		// Now we can grab the saved schemaMap setting from our sitemap and build our JSON-LD
		// $matchedType['mainEntitySchemaMapId']


		// Not sure this gets us anything new. It gets us routeParams like `entry` and `category`, but we're
		// already figuring that out just by looking at our settings and seeing if one of those exists in
		// the context....
		//$routeParams          = craft()->urlManager->getRouteParams();
		//$availableRouteParams = array_keys($routeParams['variables']);
		//Craft::dd($routeParams);

		// 2. Check our Sprout SEO Sitemap settings and see if we have any sitemaps
		//    that match and have a schema mapping associated with them (we can test $routeParams['template'] against the sitemap elementGroup Entry Template setting. I=i.e. $routeParams['template'] = news/_entry => Entry Template ?? This can be duplicate, does it matter? OR MAYBE we just need to test the groupId of the element found against the sitemap records groupId... for example, found entry element has a entry.sectionId that we can match to our sitemap options to ensure we match up entry with the right sitemap Schema mapping setting.
		// 3. Hand off the available element to the mapping and get back JSON-LD
		// $pageSpecificSchemaHtml = sproutSeo()->schema->getMainEntitySchema($context);


		// @todo - hard coded for proof of concept, need to make dynamic
		$schemaMaps = craft()->plugins->call('registerSproutSeoSchemaMaps');

		// @todo - need to compare the registered schema with a Selected Schema Mapping for the page.
		$newSchema = $schemaMaps['SproutSeo'][0];

		return $newSchema->getSchema();
	}

	/**
	 * Get global meta values
	 *
	 * @return BaseModel
	 */
	public function getGlobals()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_globals')
			->queryRow();

		$results['meta']      = isset($results['meta']) ? JsonHelper::decode($results['meta']) : null;
		$results['identity']  = isset($results['identity']) ? JsonHelper::decode($results['identity']) : null;
		$results['contacts']  = isset($results['contacts']) ? JsonHelper::decode($results['contacts']) : null;
		$results['ownership'] = isset($results['ownership']) ? JsonHelper::decode($results['ownership']) : null;
		$results['social']    = isset($results['social']) ? JsonHelper::decode($results['social']) : null;

		$schema = SproutSeo_SchemaModel::populateModel($results);

		return $schema;
	}

	/**
	 * Returns an array of vocabularies based on the path provided
	 * sproutSeo()->schema->getVocabularies('Organization.LocalBusiness.AutomotiveBusiness');
	 *
	 * @param null $path
	 *
	 * @return array
	 */
	public function getVocabularies($path = null)
	{
		$jsonLdTreePath = craft()->path->getPluginsPath() . 'sproutseo/resources/jsonld/tree.jsonld';

		$allVocabularies = JsonHelper::decode(file_get_contents($jsonLdTreePath));

		$this->vocabularies = $this->updateArrayKeys($allVocabularies['children'], 'name');

		if ($path)
		{
			return $this->getArrayByPath($this->vocabularies, $path);
		}
		else
		{
			return $this->vocabularies;
		}
	}

	public function getKnowledgeGraphLinkedData()
	{
		$schemaRaw = sproutSeo()->schema->getGlobals();

		$schemaRaw = SproutSeo_SchemaModel::populateModel($schemaRaw);

		$schema                 = $schemaRaw->getJsonLd('identity');
		$schema['contactPoint'] = $schemaRaw->getJsonLd('contacts');
		$schema['sameAs']       = $schemaRaw->getJsonLd('social');

		$output = $this->prepareLinkedDataForHtml($schema);

		return TemplateHelper::getRaw($output);
	}

	/**
	 * @param $schema
	 *
	 * @return string
	 */
	protected function prepareLinkedDataForHtml($schema)
	{
		return '
<script type="application/ld+json">
' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '
</script>';
	}

	/**
	 * Save schema to database
	 *
	 * @param $schemaType
	 * @param $schema
	 *
	 * @return bool
	 */
	public function saveSchema($schemaTypes, $schema)
	{
		// @todo - what do we do if $schemaType doesn't have a value?

		if (!is_array($schemaTypes))
		{
			array($schemaTypes);
		}

		foreach ($schemaTypes as $schemaType)
		{
			$values = array(
				$schemaType => $schema->getSchema($schemaType, 'json')
			);

			$result = craft()->db->createCommand()->update('sproutseo_globals',
				$values,
				'id=:id', array(':id' => 1)
			);
		};

		// @todo - add proper validation. Currently the above assumes everything is always working.
		return true;
	}

	/**
	 * @return int
	 */
	public function installDefaultGlobals()
	{
		$tableName = "sproutseo_globals";

		$locale = craft()->i18n->getLocaleById(craft()->language);

		$result = craft()->db->createCommand()->insert($tableName, array(
				'locale'    => $locale,
				'identity'  => null,
				'ownership' => null,
				'contacts'  => null,
				'social'    => null
			)
		);

		return $result;
	}

	/**
	 * @param        $array
	 * @param        $path
	 * @param string $separator
	 *
	 * @return mixed
	 */
	protected function getArrayByPath($array, $path, $separator = '.')
	{
		$keys = explode($separator, $path);

		$level = 1;
		foreach ($keys as $key)
		{
			if ($level == 1)
			{
				$array = $array[$key];
			}
			else
			{
				$array = $array['children'][$key];
			}

			$level++;
		}

		return $array;
	}

	/**
	 * @param array $oldArray
	 * @param       $replaceKey
	 *
	 * @return array
	 */
	protected function updateArrayKeys(array $oldArray, $replaceKey)
	{
		$newArray = array();

		foreach ($oldArray as $key => $value)
		{
			if (isset($value[$replaceKey]))
			{
				$key = $value[$replaceKey];
			}

			if (is_array($value))
			{
				$value = $this->updateArrayKeys($value, $replaceKey);
			}

			$newArray[$key] = $value;
		}

		return $newArray;
	}
}
