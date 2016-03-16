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
	 * @var SproutSeo_SitemapRecord
	 */
	protected $sitemapRecord;

	/**
	 * @param null|SproutSeo_SitemapRecord $sitemapRecord
	 */
	public function __construct($sitemapRecord = null)
	{
		$this->sitemapRecord = $sitemapRecord;

		if (is_null($this->sitemapRecord))
		{
			$this->sitemapRecord = SproutSeo_SitemapRecord::model();
		}
	}

	/**
	 * @param SproutSeo_SitemapModel $attributes
	 *
	 * @return mixed|null|string
	 */
	public function saveSitemap(SproutSeo_SitemapModel $attributes)
	{
		$row       = array();
		$isNew     = false;
		$sitemapId = null;

		$keys = explode("-", $attributes->id);
		$type = $keys[0];

		if (isset($keys) && $keys[1] == "new")
		{
			$isNew = true;
		}

		if (!$isNew)
		{
			$sitemapId = $keys[1];

			$row = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_sitemap')
				->where('id=:id', array(':id' => $sitemapId))
				->queryRow();
		}

		$model = SproutSeo_SitemapModel::populateModel($row);

		$model->id              = $sitemapId;
		$model->elementGroupId  = (isset($attributes->elementGroupId)) ? $attributes->elementGroupId : null;
		$model->url             = (isset($attributes->url)) ? $attributes->url : null;
		$model->priority        = $attributes->priority;
		$model->changeFrequency = $attributes->changeFrequency;
		$model->type            = $type != "customUrl" ? $type : null;
		$model->enabled         = ($attributes->enabled == 'true') ? 1 : 0;
		$model->ping            = ($attributes->ping == 'true') ? 1 : 0;
		$model->dateUpdated     = DateTimeHelper::currentTimeForDb();
		$model->uid             = StringHelper::UUID();

		if ($isNew)
		{
			$model->dateCreated = DateTimeHelper::currentTimeForDb();

			craft()->db->createCommand()->insert('sproutseo_sitemap', $model->getAttributes());

			return craft()->db->lastInsertID;
		}
		else
		{
			$result = craft()->db->createCommand()
				->update(
					'sproutseo_sitemap',
					$model->getAttributes(),
					'id=:id', array(
						':id' => $model->id
					)
				);

			return $model->id;
		}
	}

	/**
	 * @param SproutSeo_SitemapModel $customPage
	 *
	 * @return int
	 */
	public function saveCustomPage(SproutSeo_SitemapModel $customPage)
	{
		$result = craft()->db->createCommand()->insert('sproutseo_sitemap', $customPage->getAttributes());

		return $result;
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
		$enabledSections = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->where('enabled = 1 and (sectionId is not null or categoryGroupId is not null)')
			->queryAll();

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSections as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$criteria = new \CDbCriteria();

				if (isset($sitemapSettings['sectionId']))
				{
					$criteria  = craft()->elements->getCriteria(ElementType::Entry);
					$criteria->sectionId = $sitemapSettings['sectionId'];
				}
				else if (isset($sitemapSettings['categoryGroupId']))
				{
					$criteria  = craft()->elements->getCriteria(ElementType::Category);
					$criteria->groupId = $sitemapSettings['categoryGroupId'];
				}

				$criteria->limit  = null;
				$criteria->status = 'live';
				$criteria->locale = $locale->id;

				/**
				 * @var $entries EntryModel[]
				 *
				 * Fetching all entries enabled for the current locale
				 */
				$entries = $criteria->find();

				foreach ($entries as $entry)
				{
					// @todo ensure that this check/logging is absolutely necessary
					// Catch null URLs, log them, and prevent them from being output to the sitemap
					if (is_null($entry->getUrl()))
					{
						SproutSeoPlugin::log('Entry ID ' . $entry->id . " does not have a URL.", LogLevel::Warning, true);
						continue;
					}

					// Adding each location indexed by its id
					$urls[$entry->id][] = array(
						'id'        => $entry->id,
						'url'       => $entry->getUrl(),
						'locale'    => $locale->id,
						'modified'  => $entry->dateUpdated->format('Y-m-d\Th:m:s\Z'),
						'priority'  => $sitemapSettings['priority'],
						'frequency' => $sitemapSettings['changeFrequency'],
					);
				}
			}
		}

		// Fetching all custom pages define in Sprout SEO
		$customUrls = craft()->db->createCommand()
			->select('url, priority, changeFrequency as frequency, dateUpdated')
			->from('sproutseo_sitemap')
			->where('enabled = 1')
			->andWhere('url is not null')
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
		$path = craft()->path->getTemplatesPath();

		craft()->path->setTemplatesPath(dirname(__FILE__) . '/../templates/');

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
	 * Get all sitemaps registered on the sproutSeoRegisterSitemap hook
	 *
	 * @return array
	 */
	public function getAllSitemapsRegistered()
	{
		$sitemaps             = craft()->plugins->call('sproutSeoRegisterSitemap');
		$siteMapData          = array();
		$sitemapGroupSettings = array();

		foreach ($sitemaps as $sitemap)
		{
			foreach ($sitemap as $type => $settings)
			{
				$service = $settings['service'];
				$method  = $settings['method'];
				$class   = '\\Craft\\'.ucfirst($service)."Service";

				if(method_exists($class, $method))
				{
					$elements = craft()->{$service}->{$method}();
					$sitemapGroupSettings[$type] = $elements;
				}
				else
				{
					SproutSeoPlugin::log("Can't access to $class", LogLevel::Info, true);
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
	 * @return array
	 */
	public function getAllSectionsWithUrls()
	{
		$sections = craft()->sections->getAllSections();

		// Get all of the Sitemap Settings regarding our Sections
		$sitemapSettings = $this->getSiteMapsByType("sections");

		// Prepare a list of all Sections we can link to
		foreach ($sections as $key => $section)
		{
			if ($section->hasUrls == 1)
			{
				$sectionData[$section->id] = $section->getAttributes();
			}
			else
			{
				// Remove Sections without URLs. They don't have links!
				unset($sections[$key]);
			}
		}

		// Prepare the data for our Sitemap Settings page
		foreach ($sitemapSettings as $key => $settings)
		{
			// Add Sitemap data to any sectionIds that match
			if (array_key_exists($settings['sectionId'], $sectionData))
			{
				$sectionData[$settings['sectionId']]['settings'] = $settings;
			}
		}

		return $sectionData;
	}

	/**
	 * @return array
	 */
	public function getAllCategoriesWithUrls()
	{
		$categories   = craft()->categories->getAllGroups();
		$categoryData = array();

		// Get all of the Sitemap Settings regarding our Sections
		$sitemapSettings = $this->getSiteMapsByType("category");

		// Prepare a list of all categories we can link to
		foreach ($categories as $key => $category)
		{
			if ($category->hasUrls == 1)
			{
					$categoryData[$category->id] = $category->getAttributes();
			}
			else
			{
				// Remove Sections without URLs. They don't have links!
				unset($categories[$key]);
			}
		}

		// Prepare the data for our Sitemap Settings page
		foreach ($sitemapSettings as $key => $settings)
		{
			// Add Sitemap data to any sectionIds that match
			if (array_key_exists($settings['categoryGroupId'], $categoryData))
			{
				$categoryData[$settings['categoryGroupId']]['settings'] = $settings;
			}
		}

		return $categoryData;
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public function getSiteMapsByType($type)
	{
		$sitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
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
			->from('sproutseo_sitemap')
			->where('url IS NOT NULL')
			->queryAll();

		return $customPages;
	}

	/**
	 * @param $id
	 *
	 * @return int
	 */
	public function deleteCustomPageById($id)
	{
		$record = new SproutSeo_SitemapRecord;

		return $record->deleteByPk($id);
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
}
