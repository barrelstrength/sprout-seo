<?php

namespace Craft;

class SproutSeo_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

	/**
	 * Generates the proper xml
	 *
	 * @throws HttpException
	 */
	public function actionIndex()
	{
		// Get URL and remove .xml extension
		$url = craft()->request->getPath();

		$sitemapSlug    = substr($url, 0, -4);
		$segments       = explode('-', $sitemapSlug);
		$sitemapSegment = array_pop($segments);

		// Extract the page number, if we have one.
		preg_match('/\d+/', $sitemapSegment, $match);
		$pageNumber = isset($match[0]) ? $match[0] : null;

		// Prepare Sitemap Index content
		$sitemapIndexItems = array();
		$elements          = array();

		// @todo - need to distinguish between 4 scenarios:
		// 1. Singles
		// 2. Custom Sections
		// 3. Url-Enabled Section with Offset and Limit
		// 4. Sitemapindex

		switch ($sitemapSlug)
		{
			// Generate Sitemap Index
			case 'sitemap':
				$sitemapIndexItems = sproutSeo()->sitemap->getSitemapIndex();
				break;

			// Display Singles Sitemap
			case 'singles-sitemap':
				$elements = sproutSeo()->sitemap->getDynamicSitemapElements('singles-sitemap', $pageNumber);
				break;

			// Display Custom Section Sitemap
			case 'custom-sections-sitemap':
				$elements = sproutSeo()->sitemap->getCustomsUrls();
				break;

			default:
				$sitemapHandle = $segments[1] . ':' . $segments[0];
				$elements      = sproutSeo()->sitemap->getDynamicSitemapElements($sitemapHandle, $pageNumber);
		}

		header('Content-Type: text/xml');

		$templatePath = craft()->path->getPluginsPath() . 'sproutseo/templates';
		craft()->templates->setTemplatesPath($templatePath);

		if ($sitemapSlug === 'sitemap')
		{
			$this->renderTemplate('_special/sitemapindex', array(
				'sitemapIndexItems' => $sitemapIndexItems
			));
		}
		else
		{
			$this->renderTemplate('_special/sitemap-dynamic', array(
				'elements' => $elements
			));
		}
	}
}
