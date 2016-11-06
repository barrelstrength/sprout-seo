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
		$metadata = $this->prioritizedMetadataModel;

		if ($this->isMainEntity)
		{
			$this->addMainEntityOfPage($this->getSchemaOverrideType());
		}

		$this->addText('title', $metadata->optimizedTitle);
		$this->addText('description', $metadata->optimizedDescription);
		$this->addImage('image', $metadata->optimizedImage);
		$this->addUrl('url', $metadata->canonical);
	}
}