<?php
namespace Craft;

class SproutSeo_ImageObjectSchema extends SproutSeoBaseSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Image Object';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'ImageObject';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	public function addProperties()
	{
		$image = $this->element;

		if (!$image)
		{
			return null;
		}

		$height = isset($image['height']) ? (int) $image['height'] : null;
		$width  = isset($image['width']) ? (int) $image['width'] : null;

		$this->addUrl('url', $image['url']);
		$this->addNumber('height', $height);
		$this->addNumber('width', $width);
	}
}