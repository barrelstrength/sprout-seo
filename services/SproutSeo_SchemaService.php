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

		// Get all registered sitemap integrations
		//
		// We will use these to determine if an available variable being loaded
		// matches a matchedElementVariable supported by one of the sitemaps. As
		// we can only have one Main Entity on the page, we assume that if the URL
		// being loaded has a match that it's the main entity on the page. We use
		// the first match we find.
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
		$matchedVariables    = array_intersect_key($context, $matchedElementsByVariable);
		$matchedVariableKeys = array_keys($matchedVariables);
		$matchedVariableName = reset($matchedVariableKeys);
		$matchedVariable     = reset($matchedVariables);

		// Let's also grab all the integration settings for the matched variable so we can get more info below
		$matchedVariableSettings = array_intersect_key($matchedElementsByVariable, $context);
		$matchedVariableSetting  = reset($matchedVariableSettings);

		if (!$matchedVariableSetting)
		{
			return null;
		}

		$elementGroupId          = $matchedVariableSetting['elementGroupId'];
		$matchedElementGroupId   = $matchedVariable->{$elementGroupId};
		$matchedElementGroupType = $matchedVariableSetting['type'];

		if (!$matchedElementGroupId or !$matchedElementGroupType)
		{
			return null;
		}

		// Get all enabled sitemaps.
		//
		// We will need to make sure that the Entity we find has an enabled sitemap
		// and that sitemap has defined what the Main Entity Schema Map should be.
		// @todo - can we potentially store the matchedElementVariable info
		// in a way we can grab it with this query and simplify the above logic?
		// @todo - also ensure that we have a where clause that confirms we have a schema map setting
		$enabledMatchingSitemap = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metataggroups')
			->where('enabled = 1')
			->andWhere('elementGroupId = ' . $matchedElementGroupId)
			->andWhere('type = "' . $matchedElementGroupType . '"')
			->queryRow();

		if (!$enabledMatchingSitemap)
		{
			// Nothing to see here!
			return null;
		}

		// Prepare our Matched Element with the Main Entity Schema Map
		// Now we can grab the saved schemaMap setting from our sitemap and build our JSON-LD
		// $schemaMap = $enabledMatchingSitemap['mainEntitySchemaMapId']
		// @todo - fix hard coded Schema Map. Make dynamic.
		$schemaMap = new SproutSeo_NewsArticleSchemaMap(array(
			$matchedVariableName => $matchedVariable
		));

		return $schemaMap->getSchema();
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
		$output = null;

		$globals = sproutSeo()->schema->getGlobals();

		if ($identityType = $globals->identity['@type'])
		{
			// Determine if we have an Organization or Person Schema Type
			$schemaModel = 'Craft\SproutSeo_' . $identityType . 'SchemaMap';

			$identitySchema = new $schemaModel(array(
				'globals' => $globals
			));

			$output = $identitySchema->getSchema();
		}

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
