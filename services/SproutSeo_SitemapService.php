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
	 * Returns all URLs for a given sitemap or the rendered sitemap itself
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

	public function getSitemapIndex()
	{
		$enabledSitemaps = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('enabled = 1 and urlEnabledSectionId is not null')
			->queryAll();

		$entryTypes    = $this->getEntriesByType($enabledSitemaps);
		$categoryTypes = $this->getCategoriesByType($enabledSitemaps);
		$productTypes  = $this->getProductsByType($enabledSitemaps);

		$response = array(
			'entryTypes'=> $entryTypes,
			'categoryTypes'=> $categoryTypes,
			'productTypes'=> $productTypes,
			'custom'=> $this->getCustoms($enabledSitemaps)
		);

		return $response;
	}

	public function getElementsPerSite()
	{
		return 20;
	}

	public function getEntriesByType($enabledSitemaps)
	{
		$entryTypes = array(
			'single' => 0,
			'channel' => array(),
			'structure' => array());
		// Fetching settings for each enabled section in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

			// lets make sure that are entries
			if ($urlEnabledSectionType->getElementType() == ElementType::Entry)
			{
				$sectionModel = $urlEnabledSectionType->getById($sitemapSettings['urlEnabledSectionId']);

				$criteria = craft()->elements->getCriteria(ElementType::Entry);

				if ($sectionModel->type == 'single')
				{
					$entryTypes['single'] = 1;
				}
				//'channel' or 'structure'
				else
				{
					$criteria->sectionId = $sitemapSettings['urlEnabledSectionId'];
					$criteria->limit = null;
					$rowCount = $criteria->total();
					$handle = $sectionModel->handle;

					if ($rowCount>0)
					{
						array_push($entryTypes[$sectionModel->type], array($handle => $rowCount));
					}
				}
			}
		}

		return $entryTypes;
	}

	public function getCategoriesByType($enabledSitemaps)
	{
		$categoryTypes = array();
		// Fetching settings for each enabled category groups in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

			// lets make sure that are entries
			if ($urlEnabledSectionType->getElementType() == ElementType::Category)
			{
				$categoryGroupModel = $urlEnabledSectionType->getById($sitemapSettings['urlEnabledSectionId']);

				$criteria = craft()->elements->getCriteria(ElementType::Category);
				$criteria->groupId = $sitemapSettings['urlEnabledSectionId'];
				$criteria->limit = null;
				$rowCount = $criteria->total();
				$handle = $categoryGroupModel->handle;

				if ($rowCount>0)
				{
					$categoryTypes[$handle] = $rowCount;
				}
			}
		}

		return $categoryTypes;
	}

	public function getProductsByType($enabledSitemaps)
	{
		$productTypes = array();
		// Fetching settings for each enabled products in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sitemapSettings['type']);

			// lets make sure that are entries
			if ($urlEnabledSectionType->getElementType() == 'Commerce_Product')
			{
				$productTypeModel = $urlEnabledSectionType->getById($sitemapSettings['urlEnabledSectionId']);

				$criteria = craft()->elements->getCriteria('Commerce_Product');
				$criteria->productId = $sitemapSettings['urlEnabledSectionId'];
				$criteria->limit = null;
				$rowCount = $criteria->total();
				$handle = $productTypeModel->handle;

				if ($rowCount>0)
				{
					$productTypes[$handle] = $rowCount;
				}
			}
		}

		return $productTypes;
	}

	public function getCustoms($enabledSitemaps)
	{
		// Fetching settings for each enabled custom in Sprout SEO
		foreach ($enabledSitemaps as $key => $sitemapSettings)
		{
			if ($sitemapSettings['isCustom'])
			{
				return true;
			}
		}

		return false;
	}
}
