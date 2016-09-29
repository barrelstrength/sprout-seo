<?php
namespace Craft;

class SproutSeo_ThingSchema extends SproutSeoBaseSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Thing';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Thing';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		$meta = $this->prioritizedMetadataModel;

		$this->addMainEntityOfPage($this->getType());

		$this->addText('title', $meta->optimizedTitle);
		$this->addText('description', $meta->optimizedDescription);
		$this->addText('headline', $meta->optimizedTitle);
		$this->addText('about', $meta->optimizedDescription);
		$this->addImage('image', $meta->optimizedImage);
		$this->addUrl('url', $meta->canonical);
	}
}