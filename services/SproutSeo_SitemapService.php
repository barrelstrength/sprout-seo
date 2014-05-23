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
		
		$row = craft()->db->createCommand()
								 ->select('*')
								 ->from('sproutseo_sitemap')
								 ->where('sectionId=:sectionId',array(':sectionId'=>$attributes->sectionId))
								 ->queryRow();
		
		$row['sectionId'] = $attributes->sectionId;
		$row['url'] = $attributes->url;
		$row['priority'] = $attributes->priority;
		$row['changeFrequency'] = $attributes->changeFrequency;
		$row['enabled'] = ($attributes->enabled == 'true') ? 1 : 0;
		$row['ping'] = ($attributes->ping == 'true') ? 1 : 0;
		$row['dateUpdated'] = DateTimeHelper::currentTimeForDb();
		$row['uid'] = StringHelper::UUID();

		if (isset($row['id']))
		{	
			$result = craft()->db->createCommand()
						 ->update('sproutseo_sitemap', $row, 'id = :id', array(':id' => $row['id']));			
		}
		else
		{
			$row['dateCreated'] = DateTimeHelper::currentTimeForDb();
			craft()->db->createCommand()->insert('sproutseo_sitemap', $row);
		}

		return true;
								 
		// if (is_null($id)) 
		// {
		// 	// $record = $this->sitemapRecord->create();			
		// 	// $record->setAttributes($attributes->getAttributes(), false);
		// }
		// else
		// {	
		// 	$record = $this->sitemapRecord->create();
			
		// 	$record->setAttributes($attributes->getAttributes(), false);
		// }

		// if ($record->save()) 
		// {
		// 	return true;
		// } 
		// else 
		// {	
		// 	$attributes->addErrors($record->getErrors());
		// 	return false;
		// }
	}

	public function getSitemap()
	{
		// $sections = craft()->sproutSeo_sitemap->getAllSectionsWithUrls();
		
		$enabledSections = craft()->db->createCommand()
                ->select('*')
                ->from('sproutseo_sitemap')
                ->where('enabled = :enabled', array('enabled' => 1))
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
			            ->where('elements_i18n.enabled = :enabled', array('enabled' => 1))
			            ->andWhere('entries.sectionId = :sectionId', array('sectionId' => $sitemapSettings['sectionId']))
			            ->queryAll();

			// Loop through each entry
			foreach ($entries as $key => $entry) 
			{
				
				$url = craft()->getSiteUrl() . $entry['uri'];

				$sitemap .= '<url>';
				$sitemap .= '<loc>' . $url . '</loc>';
				$sitemap .= '<lastmod>' . $entry['dateUpdated'] . '</lastmod>';
				$sitemap .= '<changefreq>' . $sitemapSettings['changeFrequency'] . '</changefreq>';
				$sitemap .= '<priority>' . $sitemapSettings['priority'] . '</priority>';
	      $sitemap .= '</url>';

			}
			
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
}
