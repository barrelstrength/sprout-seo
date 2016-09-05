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
	public function getAttributes()
	{
		$elementModel = $this->sitemapInfo['elementModel'];
		$prioritized  = $this->sitemapInfo['prioritizedMetaTagModel'];

		return array(
			"mainEntityOfPage" => array(
				"@type" => "WebPage",
			  "@id" => $elementModel->url
			),
			"headline" => $prioritized->title,
			"image" => array(
				"@type" => "ImageObject",
			  "url" => "https://google.com/thumbnail1.jpg",
			  "height" => 800,
			  "width" => 800
			),
			"datePublished" => $elementModel->dateCreated,
			"dateModified" => $elementModel->dateUpdated,
			"author" => array(
				"@type" => "Person",
			  "name" => $elementModel->author->name
			),
			"publisher" => array(
				"@type" => "Organization",
			  "name" => "Google",
			  "logo" => array(
					"@type" => "ImageObject",
			    "url" => "https://google.com/logo.jpg",
			    "width" => 600,
			    "height" => 60
				)
			),
			"description" => $prioritized->description
		);
	}
}