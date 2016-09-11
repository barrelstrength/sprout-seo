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
	public function getStructureDataHtml($sitemapInfo)
	{
		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$rawHtml = $this->getKnowledgeGraphLinkedData($sitemapInfo);

		$schemaHtml = craft()->templates->render('sproutseo/templates/_special/schema',
			array('jsonLd'=>$rawHtml)
		);

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return $schemaHtml;
	}

	/**
	 * @return mixed
	 */
	public function getMainEntityStructuredDataHtml($sitemapInfo)
	{
		$schema = '';

		if (isset($sitemapInfo['elementTable']) && isset($sitemapInfo['elementGroupId']))
		{
			$matchedElementGroupId   = $sitemapInfo['elementGroupId'];
			$matchedElementGroupType = $sitemapInfo['elementTable'];

			$enabledMatchingSitemap = sproutSeo()->metadata->getMetadataGroupByInfo(
				$matchedElementGroupType,
				$matchedElementGroupId
			);

			if ($enabledMatchingSitemap)
			{
				if ($enabledMatchingSitemap->schemaMap)
				{
					$class = 'Craft\SproutSeo_'.$enabledMatchingSitemap->schemaMap.'SchemaMap';
					$schemaMap = new $class($enabledMatchingSitemap, true, $sitemapInfo);

					$schema = $schemaMap->getSchema();
				}
			}
		}

		return $schema;
	}

	public function getSitemapInfo($context)
	{
		$sitemapInfo = array();

		if (isset($context))
		{
			$sitemaps                  = craft()->plugins->call('registerSproutSeoSitemap');
			$elementTable 						 = null;
			$elementModel  						 = null;
			$matchedElementByVariable  = array();

			// Loop through all of our sitemap integrations and create an array of our matched element variables
			foreach ($sitemaps as $plugin)
			{
				foreach ($plugin as $definedElementTable => $element)
				{
					if (isset($element['matchedElementVariable']))
					{
						$matchedElementVariable = $element['matchedElementVariable'];

						if (isset($context[$matchedElementVariable]))
						{
							$matchedElementByVariable = $element;
							$elementTable             = $definedElementTable;
							$elementModel             = $context[$matchedElementVariable];
							break 2;
						}
					}
				}
			}

			if ($matchedElementByVariable && $elementTable && $elementModel)
			{
				$elementGroup = isset($matchedElementByVariable['elementGroupId']) ?
					$matchedElementByVariable['elementGroupId'] :
					null;
				$elementType  = isset($matchedElementByVariable['elementType']) ?
					$matchedElementByVariable['elementType'] :
					null;

				if (isset($elementModel->{$elementGroup}) && $elementType)
				{
					$locale = craft()->i18n->getLocaleById(craft()->language);

					$criteria  = craft()->elements->getCriteria($elementType);
					$criteria->{$elementGroup} = $elementModel->{$elementGroup};
					$criteria->limit           = null;
					$criteria->enabled         = true;
					$criteria->locale          = $locale->id;
					// Support one locale for now
					$results = $criteria->find();

					if (count($results) > 0)
					{
						$result = $results[0];

						$sitemapInfo = array(
							'hookInfo'       => $matchedElementByVariable,
							'urlFormat'      => $result->urlFormat,
							'elementModel'   => $elementModel,
							'elementTable'   => $elementTable,
							'elementGroupId' => $elementModel->{$elementGroup}
						);
					}
				}
			}
		}

		return $sitemapInfo;
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

	public function getKnowledgeGraphLinkedData($sitemapInfo)
	{
		$output = null;

		$globals = $sitemapInfo['globals'];

		if ($identityType = $globals->identity['@type'])
		{
			// Determine if we have an Organization or Person Schema Type
			$schemaModel = 'Craft\SproutSeo_' . $identityType . 'SchemaMap';

			$identitySchema = new $schemaModel(array(
				'globals' => $globals
			), true, $sitemapInfo);

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
