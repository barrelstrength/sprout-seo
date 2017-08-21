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
	 * Prepares sitemaps for a sitemapindex
	 *
	 * @return array
	 */
	public function getSitemapIndex()
	{
		$sitemapIndexItems = array();
		$hasSingles        = false;

		$totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

		$urlEnabledSectionTypes = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypes();

		foreach ($urlEnabledSectionTypes as $urlEnabledSectionType)
		{
			$urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

			foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSection)
			{
				$sectionMetadata = $urlEnabledSection->sectionMetadata;

				if ($sectionMetadata->enabled and $sectionMetadata->hasUrls)
				{

					// Get Total Elements for this URL-Enabled Section
					$criteria                             = craft()->elements->getCriteria($urlEnabledSectionType->getElementType());
					$criteria->{$urlEnabledSectionTypeId} = $urlEnabledSection->id;
					$totalElements                        = $criteria->total();

					// Is this a Singles Section?
					$section = $urlEnabledSectionType->getById($urlEnabledSection->id);

					if (isset($section->type) && $section->type === 'single')
					{
						// only add this once
						if ($hasSingles === false)
						{
							$hasSingles = true;

							// Add the singles at the beginning of our sitemap
							array_unshift($sitemapIndexItems, craft()->getSiteUrl() . 'singles-sitemap.xml');
						}
					}
					else
					{
						$totalSitemaps = ceil($totalElements / $totalElementsPerSitemap);

						// Build Sitemap Index URLs
						for ($i = 1; $i <= $totalSitemaps; $i++)
						{
							$elementTableName = $urlEnabledSectionType->getElementTableName();
							$sitemapHandle    = strtolower($sectionMetadata->handle . '-' . $elementTableName);

							$sitemapIndexUrl = craft()->getSiteUrl() . $sitemapHandle . '-sitemap' . $i . '.xml';

							$sitemapIndexItems[] = $sitemapIndexUrl;
						}
					}
				}
			}
		}

		// Fetching all Custom Section Metadata defined in Sprout SEO
		$customSectionMetadata = craft()->db->createCommand()
			->select('id')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1')
			->andWhere('url is not null and isCustom = 1')
			->query();

		if ($customSectionMetadata->getRowCount() > 0)
		{
			$sitemapIndexItems[] = UrlHelper::getSiteUrl('custom-sections-sitemap.xml');
		}

		return $sitemapIndexItems;
	}

	/**
	 * Prepares urls for a dynamic sitemap
	 *
	 * @param     $sitemapHandle
	 * @param     $pageNumber
	 *
	 * @return array
	 * @throws HttpException
	 */
	public function getDynamicSitemapElements($sitemapHandle, $pageNumber)
	{
		$urls                    = array();
		$totalElementsPerSitemap = $this->getTotalElementsPerSitemap();

		// Our offset should be zero for the first page
		$offset = ($totalElementsPerSitemap * $pageNumber) - $totalElementsPerSitemap;

		$criteria = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1 and urlEnabledSectionId is not null');

		if ($sitemapHandle == 'singles-sitemap')
		{
			$criteria->andWhere('type = :type', array(':type' => 'entries'));
		}
		else
		{
			$criteria->andWhere('handle = :handle', array(':handle' => $sitemapHandle));
		}

		$enabledSitemaps = $criteria->queryAll();

		if (empty($enabledSitemaps))
		{
			throw new HttpException(404);
		}

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

				$elements = array();

				if ($urlEnabledSectionType != null)
				{
					$urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

					$criteria = craft()->elements->getCriteria($urlEnabledSectionType->getElementType());

					$criteria->{$urlEnabledSectionTypeId} = $sitemapSettings['urlEnabledSectionId'];

					$criteria->offset  = $offset;
					$criteria->limit   = $totalElementsPerSitemap;
					$criteria->enabled = true;
					$criteria->locale  = $locale->id;

					if ($sitemapHandle == 'singles-sitemap')
					{
						$sectionModel = $urlEnabledSectionType->getById($sitemapSettings['urlEnabledSectionId']);

						if ($sectionModel->type == 'single')
						{
							$elements = $criteria->find();
						}
					}
					else
					{
						$elements = $criteria->find();
					}
				}

				foreach ($elements as $element)
				{
					// @todo - Confirm this is necessary
					// Confirm that this check/logging is necessary
					// Catch null URLs, log them, and prevent them from being output to the sitemap
					if (is_null($element->getUrl()))
					{
						SproutSeoPlugin::log('Element ID ' . $element->id . ' does not have a URL.', LogLevel::Warning, true);

						continue;
					}

					// Add each location indexed by its id
					$urls[$element->id][] = array(
						'id'              => $element->id,
						'url'             => $element->getUrl(),
						'locale'          => $locale->id,
						'modified'        => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
						'priority'        => $sitemapSettings['priority'],
						'changeFrequency' => $sitemapSettings['changeFrequency'],
					);
				}
			}
		}

		$urls = $this->getLocalizedSitemapStructure($urls);

		return $urls;
	}

	/**
	 * Returns all Custom Section URLs
	 *
	 * @return array
	 */
	public function getCustomSectionUrls()
	{
		$urls = array();

		// Fetch all Custom Section Metadata defined in Sprout SEO
		$customSectionMetadata = craft()->db->createCommand()
			->select('url, priority, changeFrequency, dateUpdated')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1')
			->andWhere('url is not null and isCustom = 1')
			->queryAll();

		foreach ($customSectionMetadata as $customSection)
		{
			// Adding each custom location indexed by its URL
			if (!UrlHelper::isAbsoluteUrl($customSection['url']))
			{
				$customSection['url'] = UrlHelper::getSiteUrl($customSection['url']);
			}
			$modified                    = new DateTime($customSection['dateUpdated']);
			$customSection['modified']   = $modified->format('Y-m-d\Th:m:s\Z');
			$urls[$customSection['url']] = craft()->config->parseEnvironmentString($customSection);
		}

		$urls = $this->getLocalizedSitemapStructure($urls);

		return $urls;
	}

	/**
	 * Returns all URLs for a given sitemap or the rendered sitemap itself
	 *
	 * @deprecated - this method was used for the simple Craft Variable based sitemap
	 *               and will be retired for Craft 3. Use dynamic sitemaps instead.
	 *
	 * @param array|null $options
	 *
	 * @throws Exception
	 * @return array|string
	 */
	public function getSitemap(array $options = null)
	{
		$urls = array();

		$enabledSitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1 and urlEnabledSectionId is not null')
			->queryAll();

		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			// Fetching all enabled locales
			foreach (craft()->i18n->getSiteLocales() as $locale)
			{
				$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

				$elements = array();

				if ($urlEnabledSectionType != null)
				{
					$urlEnabledSectionTypeId = $urlEnabledSectionType->getIdColumnName();

					$criteria = craft()->elements->getCriteria($urlEnabledSectionType->getElementType());

					$criteria->{$urlEnabledSectionTypeId} = $sitemapSettings['urlEnabledSectionId'];

					$criteria->limit   = null;
					$criteria->enabled = true;
					$criteria->locale  = $locale->id;

					$elements = $criteria->find();
				}

				foreach ($elements as $element)
				{
					// @todo - Confirm this is necessary
					// Confirm that this check/logging is necessary
					// Catch null URLs, log them, and prevent them from being output to the sitemap
					if (is_null($element->getUrl()))
					{
						SproutSeoPlugin::log('Element ID ' . $element->id . " does not have a URL.", LogLevel::Warning, true);

						continue;
					}

					// Add each location indexed by its id
					$urls[$element->id][] = array(
						'id'              => $element->id,
						'url'             => $element->getUrl(),
						'locale'          => $locale->id,
						'modified'        => $element->dateUpdated->format('Y-m-d\Th:m:s\Z'),
						'priority'        => $sitemapSettings['priority'],
						'changeFrequency' => $sitemapSettings['changeFrequency'],
					);
				}
			}
		}

		// Fetching all Custom Section Metadata defined in Sprout SEO
		$customSectionMetadata = craft()->db->createCommand()
			->select('url, priority, changeFrequency, dateUpdated')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1')
			->andWhere('url is not null and isCustom = 1')
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

		craft()->templates->setTemplatesPath($path);

		return TemplateHelper::getRaw($source);
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
	 * Returns the value for the totalElementsPerSitemap setting. Default is 500.
	 *
	 * @param int $total
	 *
	 * @return int
	 */
	public function getTotalElementsPerSitemap($total = 500)
	{
		$plugin      = craft()->plugins->getPlugin('sproutseo');
		$seoSettings = $plugin->getSettings();

		if (isset($seoSettings['totalElementsPerSitemap']) && $seoSettings['totalElementsPerSitemap'])
		{
			$total = $seoSettings['totalElementsPerSitemap'];
		}

		return $total;
	}
}
