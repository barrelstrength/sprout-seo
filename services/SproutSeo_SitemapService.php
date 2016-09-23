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

		$elementInfo = sproutSeo()->metadata->getMetadataInfo($info);
		$isNew       = $elementInfo['isNew'];

		if (!$isNew)
		{
			$sitemapId = $elementInfo['metadataId'];

			$row = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_metadatagroups')
				->where('id=:id', array(':id' => $sitemapId))
				->queryRow();

			$model = SproutSeo_SitemapModel::populateModel($row);
		}

		$model->id                     = $sitemapId;
		$model->name                   = $attributes->id;
		$model->handle                 = str_replace("-", "", $attributes->id);
		$model->type                   = $type != "customUrl" ? $type : null;
		$model->elementGroupId         = (isset($attributes->elementGroupId)) ? $attributes->elementGroupId : null;
		$model->sitemapUrl             = (isset($elementInfo['element']->urlFormat)) ? $elementInfo['element']->urlFormat : null;
		$model->sitemapPriority        = $attributes->sitemapPriority;
		$model->sitemapChangeFrequency = $attributes->sitemapChangeFrequency;
		$model->isSitemapCustomPage    = 0;
		$model->enabled                = ($attributes->enabled == 1) ? 1 : 0;
		$model->dateUpdated            = DateTimeHelper::currentTimeForDb();
		$model->uid                    = StringHelper::UUID();

		if ($isNew)
		{
			$model->dateCreated = DateTimeHelper::currentTimeForDb();

			craft()->db->createCommand()->insert('sproutseo_metadatagroups', $model->getAttributes());

			return craft()->db->lastInsertID;
		}
		else
		{
			craft()->db->createCommand()
				->update(
					'sproutseo_metadatagroups',
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
			->from('sproutseo_metadatagroups')
			->where('enabled = 1 and elementGroupId is not null')
			->queryAll();

		$sitemaps = craft()->plugins->call('registerSproutSeoSitemap');

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$elementInfo = $this->getElementInfo($sitemaps, $sitemapSettings['type']);

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
						'priority'  => $sitemapSettings['sitemapPriority'],
						'frequency' => $sitemapSettings['sitemapChangeFrequency'],
					);
				}
			}
		}

		// Fetching all custom pages defined in Sprout SEO
		$customUrls = craft()->db->createCommand()
			->select('sitemapUrl as url, sitemapPriority as priority, sitemapChangeFrequency as frequency, dateUpdated')
			->from('sproutseo_metadatagroups')
			->where('enabled = 1')
			->andWhere('url is not null and isSitemapCustomPage = 1')
			->queryAll();

		foreach ($customUrls as $customEntry)
		{
			// Adding each custom location indexed by its URL
			$modified                  = new DateTime($customEntry['dateUpdated']);
			$customEntry['modified']   = $modified->format('Y-m-d\Th:m:s\Z');
			$urls[$customEntry['url']] = craft()->config->parseEnvironmentString($customEntry);
		}

		$urls = $this->getLocalizedSitemapStructure($urls);

		// Rendering the template and passing in received options
		$path = craft()->templates->getTemplatesPath();

		craft()->templates->setTemplatesPath(dirname(__FILE__) . '/../templates/');

		$source = craft()->templates->render(
			'_special/sitemap', array(
				'entries' => $urls,
				'options' => is_array($options) ? $options : array(),
			)
		);

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
			->from('sproutseo_metadatagroups')
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
			->from('sproutseo_metadatagroups')
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
				// Looping through each entry and adding it as primary and creating its alternates
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
	public function getElementInfo($sitemaps, $sitemapSettingsType)
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
}
