<?php
namespace Craft;

class SproutSeo_SitemapService extends BaseApplicationComponent
{
	protected $sitemapRecord;

	public function __construct($sitemapRecord = null)
	{
		$this->sitemapRecord = $sitemapRecord;
		if (is_null($this->sitemapRecord)) {
			$this->sitemapRecord = SproutSeo_SitemapRecord::model();
		}
	}

	public function saveSitemap(SproutSeo_SitemapModel $attributes)
	{
		$row = array();
		$isNew = false;

		if (isset($attributes->id) && (substr( $attributes->id, 0, 3 ) === "new"))
		{
			$isNew = true;
		}

		if (!$isNew)
		{
			$row = craft()->db->createCommand()
				->select('*')
				->from('sproutseo_sitemap')
				->where('id=:id',array(':id'=>$attributes->id))
				->queryRow();
		}

		$model = SproutSeo_SitemapModel::populateModel($row);

		$model->id = (!$isNew) ? $attributes->id : null;
		$model->sectionId = (isset($attributes->sectionId)) ? $attributes->sectionId : null;
		$model->url = (isset($attributes->url)) ? $attributes->url : null;
		$model->priority = $attributes->priority;
		$model->changeFrequency = $attributes->changeFrequency;
		$model->enabled = ($attributes->enabled == 'true') ? 1 : 0;
		$model->ping = ($attributes->ping == 'true') ? 1 : 0;
		$model->dateUpdated = DateTimeHelper::currentTimeForDb();
		$model->uid = StringHelper::UUID();

		if ($isNew)
		{
			$model->dateCreated = DateTimeHelper::currentTimeForDb();
			craft()->db->createCommand()->insert('sproutseo_sitemap', $model->getAttributes());

			return craft()->db->lastInsertID;
		}
		else
		{
			$result = craft()->db->createCommand()
				->update('sproutseo_sitemap',
				$model->getAttributes(),
				'id=:id', array(
					':id' => $model->id
				)
			);

			return $model->id;
		}
	}

	public function saveCustomPage(SproutSeo_SitemapModel $customPage)
	{
		$result = craft()->db->createCommand()->insert('sproutseo_sitemap', $customPage->getAttributes());

		return $result;
	}

	public function getSitemap()
	{
		$enabledSections = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->where('enabled = :enabled', array(
				'enabled' => 1
			))
			->queryAll();

		// Begin sitemap
		// @TODO - let's break out this code so that we can return the full sitemap,
		// or just the data so that someone could build it on their own
		$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		// Loop through each of our enabled sections
		foreach ($enabledSections as $key => $sitemapSettings)
		{

			// Grab all of the entries associated with that section
			// @TODO - how do we grab "LIVE" entries?  Do we need to update
			// things to use the ElementCriteriaModel?
			$entries = craft()->db->createCommand()
				->select('elements_i18n.uri, elements_i18n.dateUpdated')
				->from('elements_i18n AS elements_i18n')
				->join('entries AS entries', 'entries.id = elements_i18n.elementId')
				->where('elements_i18n.enabled = :enabled', array(
					'enabled' => 1
				))
				->andWhere('entries.sectionId = :sectionId', array(
					'sectionId' => $sitemapSettings['sectionId']
				))
				->queryAll();

			// Loop through each entry
			foreach ($entries as $key => $entry)
			{

				// check if the uri is the Craft home page
				if ($entry['uri'] == '__home__') {
					$entry['uri'] = null;
				}

				$url = craft()->getSiteUrl() . $entry['uri'];

				$dateUpdated = new DateTime($entry['dateUpdated']);
				$date = $dateUpdated->format('Y-m-d');
				$time = $dateUpdated->format('h:m:s');
				$lastMod = $date . 'T' . $time . 'Z';

				$sitemap .= '<url>';
				$sitemap .= '<loc>' . $url . '</loc>';
				$sitemap .= '<lastmod>' . $lastMod . '</lastmod>';
				$sitemap .= '<changefreq>' . $sitemapSettings['changeFrequency'] . '</changefreq>';
				$sitemap .= '<priority>' . $sitemapSettings['priority'] . '</priority>';
				$sitemap .= '</url>';

			}

		}

		// query all of the custom URL's in the database that are enabled
		$customUrls = craft()->db->createCommand()
			->select('url, priority, changeFrequency, enabled, dateUpdated')
			->from('sproutseo_sitemap')
			->where('enabled = :enabled', array(
				'enabled' => 1
			))
			->andWhere('url is not null')
			->queryAll();

		// Loop through each custom page
		foreach ($customUrls as $key => $entry)
		{

			$url = $entry['url'];

			$dateUpdated = new DateTime($entry['dateUpdated']);
			$date = $dateUpdated->format('Y-m-d');
			$time = $dateUpdated->format('h:m:s');
			$lastMod = $date . 'T' . $time . 'Z';

			$sitemap .= '<url>';
			$sitemap .= '<loc>' . $url . '</loc>';
			$sitemap .= '<lastmod>' . $lastMod . '</lastmod>';
			$sitemap .= '<changefreq>' . $entry['changeFrequency'] . '</changefreq>';
			$sitemap .= '<priority>' . $entry['priority'] . '</priority>';
			$sitemap .= '</url>';

		}

		// End sitemap
		$sitemap .= '</urlset>';

		return $sitemap;
	}

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

	public function getAllCustomPages()
	{
		$customPages = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_sitemap')
			->where('url IS NOT NULL')
			->queryAll();

		return $customPages;

	}

	public function deleteCustomPageById($id)
	{
		$record = new SproutSeo_SitemapRecord;

		return $record->deleteByPk($id);
	}
}
