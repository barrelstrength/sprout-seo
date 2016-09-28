<?php
namespace Craft;

class SproutSeo_CreativeWorkSchemaMap extends SproutSeoBaseSchemaMap
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Creative Work';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'CreativeWork';
	}

	/**
	 * @return array|null
	 */
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