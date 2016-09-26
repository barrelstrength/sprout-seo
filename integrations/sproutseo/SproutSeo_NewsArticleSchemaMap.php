<?php
namespace Craft;

class SproutSeo_NewsArticleSchemaMap extends BaseSproutSeoSchemaMap
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'News Article';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'NewsArticle';
	}

	// Does syntax user a generic `object` or do we need to assume
	// we know specifically what the variable is called?
	//
	// Have some out of box helper methods like getFirst()
	// Do we really need the @methodName syntax? or do we just write this in PHP?
	public function getProperties()
	{
		$elementModel = $this->sitemapInfo['elementModel'];
		$prioritized  = $this->sitemapInfo['prioritizedMetadataModel'];
		$globals      = $this->sitemapInfo['globals'];

		$jsonLd = array(
			"mainEntityOfPage" => array(
				"@type" => "WebPage",
				"@id" => $elementModel->url
			),
			"headline" => $prioritized->title
		);

		if (isset($prioritized->ogImage))
		{
			$jsonLd['image'] = array(
				"@type" => "ImageObject",
				"url" => $prioritized->ogImage,
				"height" => $prioritized->ogImageHeight,
				"width" => $prioritized->ogImageWidth
			);
		}

		$jsonLd['datePublished'] = $this->getDateFromDatetime($elementModel->dateCreated);
		$jsonLd['dateModified']  = $this->getDateFromDatetime($elementModel->dateUpdated);

		if (isset($elementModel->author->name))
		{
			$jsonLd['author'] = array(
				"@type" => "Person",
				"name" => $elementModel->author->name
			);
		}

		if (isset($globals['identity']['@type']))
		{
			$jsonLd['publisher'] = array(
				"@type" => $globals['identity']['@type'],
				"name" => $globals['identity']['name']
			);

			if (isset($prioritized->ogImage))
			{
				$jsonLd['publisher']["logo"] = array(
					"@type"  => "ImageObject",
					"url"    => $prioritized->ogImage,
					"height" => $prioritized->ogImageHeight,
					"width"  => $prioritized->ogImageWidth
				);
			}
		}

		$jsonLd['description'] = $prioritized->description;

		return $jsonLd;
	}
}