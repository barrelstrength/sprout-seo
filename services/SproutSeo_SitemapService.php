<?php
namespace Craft;

/**
 * Class SproutSeo_SitemapService
 *
 * @package Craft
 */
class SproutSeo_SitemapService extends BaseApplicationComponent
{
	/**
	 * @param SproutSeo_MetadataModel $attributes
	 *
	 * @return mixed|null|string
	 */
	public function saveSitemap(SproutSeo_SitemapModel $attributes)
	{
		$sitemapId = null;

		$keys = explode("-", $attributes->id);
		$type = $keys[0];

		$model = new SproutSeo_SitemapModel();

		$info = array(
			'groupName'      => $type,
			'sitemapId'      => $type . '-1',
			'elementGroupId' => $attributes->elementGroupId
		);

		$elementInfo = sproutSeo()->metadata->getSectionMetadataInfo($info);
		$isNew       = $elementInfo['isNew'];

		if (!$isNew)
		{
			$sitemapId = $elementInfo['metadataId'];

			$row = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_metadata_sections')
				->where('id=:id', array(':id' => $sitemapId))
				->queryRow();

			$model = SproutSeo_SitemapModel::populateModel($row);
		}

		$model->id                  = $sitemapId;
		$model->name                = $attributes->id;
		$model->handle              = str_replace("-", "", $attributes->id);
		$model->type                = $type != "customUrl" ? $type : null;
		$model->elementGroupId      = (isset($attributes->elementGroupId)) ? $attributes->elementGroupId : null;
		$model->url                 = (isset($elementInfo['element']->urlFormat)) ? $elementInfo['element']->urlFormat : null;
		$model->priority            = $attributes->priority;
		$model->changeFrequency     = $attributes->changeFrequency;
		$model->isSitemapCustomPage = 0;
		$model->enabled             = ($attributes->enabled == 1) ? 1 : 0;
		$model->dateUpdated         = DateTimeHelper::currentTimeForDb();
		$model->uid                 = StringHelper::UUID();

		if ($isNew)
		{
			$model->dateCreated = DateTimeHelper::currentTimeForDb();

			craft()->db->createCommand()->insert('sproutseo_metadata_sections', $model->getAttributes());

			return craft()->db->lastInsertID;
		}
		else
		{
			craft()->db->createCommand()
				->update(
					'sproutseo_metadata_sections',
					$model->getAttributes(),
					'id=:id', array(
						':id' => $model->id
					)
				);

			return $model->id;
		}
	}

	/**
	 * Returns all URLs for a given sitemap or the rendered sitemap itself
	 *
	 * @param array|null $options
	 *
	 * @throws Exception
	 * @return array|string
	 */
	public function getSitemap(array $options = null)
	{
		$urls            = array();
		$enabledSitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1 and elementGroupId is not null')
			->queryAll();

		$sitemaps = craft()->plugins->call('registerSproutSeoSitemap');

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$elementInfo = $this->getSectionMetadataElementInfo($sitemaps, $sitemapSettings['type']);

				$elements = array();

				if ($elementInfo != null)
				{
					$elementGroupId = $elementInfo['elementGroupId'];

					$criteria                    = craft()->elements->getCriteria($elementInfo['elementType']);
					$criteria->{$elementGroupId} = $sitemapSettings['elementGroupId'];

					$criteria->limit   = null;
					$criteria->enabled = true;
					$criteria->locale  = $locale->id;

					$elements = $criteria->find();
				}

				foreach ($elements as $element)
				{
					// @todo ensure that this check/logging is absolutely necessary
					// Catch null URLs, log them, and prevent them from being output to the sitemap
					if (is_null($element->getUrl()))
					{
						SproutSeoPlugin::log('Element ID ' . $element->id . " does not have a URL.", LogLevel::Warning, true);

						continue;
					}

					// Add each location indexed by its id
					$urls[$element->id][] = array(
						'id'        => $element->id,
						'url'       => $element->getUrl(),
						'locale'    => $locale->id,
						'modified'  => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
						'priority'  => $sitemapSettings['priority'],
						'frequency' => $sitemapSettings['changeFrequency'],
					);
				}
			}
		}

		// Fetching all Custom Section Metadata defined in Sprout SEO
		$customSectionMetadata = craft()->db->createCommand()
			->select('url, priority, changeFrequency, dateUpdated')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1')
			->andWhere('url is not null and isSitemapCustomPage = 1')
			->queryAll();

		foreach ($customSectionMetadata as $customSection)
		{
			// Adding each custom location indexed by its URL
			$modified                    = new DateTime($customSection['dateUpdated']);
			$customSection['modified']   = $modified->format('Y-m-d\Th:m:s\Z');
			$urls[$customSection['url']] = craft()->config->parseEnvironmentString($customSection);
		}

		$urls = $this->getLocalizedSitemapStructure($urls);

		// Rendering the template and passing in received options
		$path = craft()->templates->getTemplatesPath();

		craft()->templates->setTemplatesPath(dirname(__FILE__) . '/../templates/');

		$source = craft()->templates->render('_special/sitemap', array(
			'elements' => $urls,
			'options'  => is_array($options) ? $options : array(),
		));

		craft()->path->setTemplatesPath($path);

		return TemplateHelper::getRaw($source);
	}

	/**
	 * Get all sitemaps registered on the registerSproutSeoSitemap hook
	 *
	 * @return array
	 */
	public function getAllSitemaps()
	{
		$sitemaps             = craft()->plugins->call('registerSproutSeoSitemap');
		$siteMapData          = array();
		$sitemapGroupSettings = array();

		foreach ($sitemaps as $sitemap)
		{
			foreach ($sitemap as $type => $settings)
			{
				if (isset($settings['service']) && isset($settings['method']))
				{
					$service = $settings['service'];
					$method  = $settings['method'];
					$class   = '\\Craft\\' . ucfirst($service) . "Service";

					if (method_exists($class, $method))
					{
						$elements = craft()->{$service}->{$method}();

						if (!empty($elements))
						{
							$sitemapGroupSettings[$type] = $elements;
						}
					}
					else
					{
						SproutSeoPlugin::log("Unable to access $class", LogLevel::Info, true);
					}
				}
				else
				{
					SproutSeoPlugin::log(Craft::t("The sitemap for {sitemapType} does not have correct integration values for `service` and/or `method`", array(
						'sitemapType' => $type
					)), LogLevel::Warning, true);
				}
			}
		}

		// Prepare a list of all Sitemap Groups we can link to
		foreach ($sitemapGroupSettings as $type => $sitemapGroups)
		{
			foreach ($sitemapGroups as $element)
			{
				if (isset($element->hasUrls) && $element->hasUrls == 1)
				{
					$siteMapData[$type][$element->id] = $element->getAttributes();
				}
			}
		}

		// Prepare the data for our Sitemap Settings page
		foreach ($siteMapData as $type => $data)
		{
			$sitemapSettings = $this->getSiteMapsByType($type);

			foreach ($sitemapSettings as $settings)
			{
				// Add Sitemap data to any elementGroupId that match
				$elementId = $settings['elementGroupId'];

				if (array_key_exists($elementId, $data))
				{
					$siteMapData[$type][$elementId]['settings'] = $settings;
				}
			}
		}

		return $siteMapData;
	}

	/**
	 * Get all customNames sitemaps registered on the registerSproutSeoSitemap hook
	 *
	 * @return array
	 */
	public function getAllCustomNames()
	{
		$sitemaps    = craft()->plugins->call('registerSproutSeoSitemap');
		$customNames = array();

		foreach ($sitemaps as $sitemap)
		{
			foreach ($sitemap as $type => $settings)
			{
				if (isset($settings['name']) && $settings['name'] != null)
				{
					$customNames[$type] = $settings['name'];
				}
				else
				{
					$customNames[$type] = $type;
				}
			}
		}

		return $customNames;
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 */
	public function getSiteMapsByType($type)
	{
		$sitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('elementGroupId iS NOT NULL and type = :type', array(':type' => $type))
			->queryAll();

		return $sitemaps;
	}

	/**
	 * @return array|\CDbDataReader
	 */
	public function getAllCustomPages()
	{
		$customPages = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('isSitemapCustomPage = 1')
			->queryAll();

		return $customPages;
	}

	/**
	 * Returns an array of localized entries for a sitemap from a set of URLs indexed by id
	 *
	 * The returned structure is compliant with multiple locale google sitemap spec
	 *
	 * @param array $stack
	 *
	 * @return array
	 */
	protected function getLocalizedSitemapStructure(array $stack)
	{
		// Defining the containing structure
		$structure = array();

		// Looping through all entries indexed by id
		foreach ($stack as $id => $locations)
		{
			if (is_string($id))
			{
				// Adding a custom location indexed by its URL
				$structure[] = $locations;
			}
			else
			{
				// Looping through each element and adding it as primary and creating its alternates
				foreach ($locations as $index => $location)
				{
					// Add secondary locations as alternatives to primary
					if (count($locations) > 1)
					{
						$structure[] = array_merge($location, array('alternates' => $locations));
					}
					else
					{
						$structure[] = $location;
					}
				}
			}
		}

		return $structure;
	}

	/**
	 * @param $sitemaps
	 * @param $type string
	 *
	 * @return array
	 * @internal param $sitemaps array from hook
	 */
	public function getSectionMetadataElementInfo($sitemaps, $sitemapSettingsType)
	{
		$elementInfo = array();

		foreach ($sitemaps as $sitemap)
		{
			foreach ($sitemap as $sitemapType => $settings)
			{
				if ($sitemapType == $sitemapSettingsType)
				{
					if (isset($settings['elementType']) && isset($settings['elementGroupId']))
					{
						$elementInfo = array(
							"elementType"            => $settings['elementType'],
							"elementGroupId"         => $settings['elementGroupId'],
							"matchedElementVariable" => $settings['matchedElementVariable'],
							"name"                   => $settings['name'],
						);

						return $elementInfo;
					}
					else
					{
						SproutSeoPlugin::log(Craft::t("Could not retrieve element types. The sitemap for {sitemapType} does not have correct integration values for `elementType` and/or `elementGroupId`", array(
							'sitemapType' => $sitemapType
						)), LogLevel::Warning, true);
					}
				}
			}
		}

		return $elementInfo;
	}

	/**
	 * @param $context
	 *
	 * @return array
	 */
	public function getSitemapInfo($context)
	{
		$sitemapInfo = array();

		if (isset($context))
		{
			$sitemaps                 = craft()->plugins->call('registerSproutSeoSitemap');
			$elementTable             = null;
			$elementModel             = null;
			$matchedElementByVariable = array();

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

					$criteria                  = craft()->elements->getCriteria($elementType);
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
}
