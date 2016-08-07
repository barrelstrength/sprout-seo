<?php
namespace Craft;

class SproutSeo_ImageObjectSchemaMap extends BaseSproutSeoSchemaMap
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

	public function getAttributes()
	{
		$image = $this->attributes['image'];

		if (!$image)
		{
			return null;
		}

		$schema['url']    = isset($image['url']) ? $image['url'] : null;
		$schema['height'] = isset($image['height']) ? $image['height'] : null;
		$schema['width']  = isset($image['width']) ? $image['width'] : null;

		return array_filter($schema);
	}
}