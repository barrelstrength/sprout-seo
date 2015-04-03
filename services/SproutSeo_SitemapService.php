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
		$row   = array();
		$isNew = false;

		if (isset($attributes->id) && (substr($attributes->id, 0, 3) === "new"))
		{
			$isNew = true;
		}

		if (!$isNew)
		{
			$row = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_sitemap')
				->where('id=:id', array(':id' => $attributes->id))
				->queryRow();
		}

		$model = SproutSeo_SitemapModel::populateModel($row);

		$model->id              = (!$isNew) ? $attributes->id : null;
		$model->sectionId       = (isset($attributes->sectionId)) ? $attributes->sectionId : null;
		$model->url             = (isset($attributes->url)) ? $attributes->url : null;
		$model->priority        = $attributes->priority;
		$model->changeFrequency = $attributes->changeFrequency;
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
	 * @param bool $rendered Whether to return the rendered sitemap or an array of URLs
	 *
	 * @throws Exception
	 * @return array|string
	 */
	public function getSitemap($rendered = true)
	{
		$urls            = array();
		$enabledSections = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->where('enabled = 1')
			->andWhere('sectionId is not null')
			->queryAll();

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSections as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$criteria            = craft()->elements->getCriteria(ElementType::Entry);
				$criteria->limit     = null;
				$criteria->status    = 'live';
				$criteria->locale    = $locale->id;
				$criteria->sectionId = $sitemapSettings['sectionId'];

				/**
				 * @var $entries EntryModel[]
				 *
				 * Fetching all entries enabled for the current locale
				 */
				$entries = $criteria->find();

				foreach ($entries as $entry)
				{
					$location = array(
						'id'        => $entry->id,
						'url'       => $entry->getUrl(),
						'locale'    => $locale->id,
						'modified'  => $entry->dateUpdated->format('Y-m-d\Th:m:s\Z'),
						'priority'  => $sitemapSettings['priority'],
						'frequency' => $sitemapSettings['changeFrequency'],
					);

					// Adding the location to $urls[] as a root or alternate location
					$this->addLocationOrAlternate($urls, $location);
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
			// Adding to the $urls[] indexed by URL to avoid collision with entry ids
			$modified                  = new DateTime($customEntry['dateUpdated']);
			$customEntry['modified']   = $modified->format('Y-m-d\Th:m:s\Z');
			$urls[$customEntry['url']] = craft()->config->parseEnvironmentString($customEntry);
		}

		// Rendering the template if the option is set
		if ($rendered)
		{
			$path = craft()->path->getTemplatesPath();

			craft()->path->setTemplatesPath(dirname(__FILE__).'/../templates/');

			$source = craft()->templates->render('_special/sitemap', array('entries' => $urls));

			craft()->path->setTemplatesPath($path);

			return TemplateHelper::getRaw($source);
		}

		return $urls;
	}

	/**
	 * @return array
	 */
	public function getAllSectionsWithUrls()
	{
		// @TODO - Probably can do this with a SitemapSettingsModel
		$sectionData = array();

		// Get all of our Sections
		$sections = craft()->sections->getAllSections();

		// Get all of the Sitemap Settings regarding our Sections
		$sitemapSettings = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->queryAll();

		// Loop through the sections and
		// 1) Remove any sections without URLs
		foreach ($sections as $key => $section)
		{
			if (!$section->hasUrls)
			{
				// remove sections without URLs
				unset($sections[$key]);
			}

			$sectionData[$section->id] = $section->getAttributes();
		}

		// 2) Add Sitemap data to any sectionIds that match
		foreach ($sitemapSettings as $key => $settings)
		{
			if (array_key_exists($settings['sectionId'], $sectionData))
			{
				$sectionData[$settings['sectionId']]['settings'] = $settings;
			}
		}

		return $sectionData;
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
	 * @param array $stack
	 * @param array $location
	 */
	protected function addLocationOrAlternate(array &$stack, array $location)
	{
		// Determine if this entry is an alternate for default locale
		if (isset($stack[$location['id']]))
		{
			$stack[$location['id']]['alternates'][] = $location;
		}
		else
		{
			$stack[$location['id']] = $location;
		}
	}
}
