<?php
namespace Craft;

/**
 * Class SproutSeo_GlobalsModel
 *
 * @package Craft
 */
class SproutSeo_GlobalsModel extends BaseElementModel
{
	protected function defineAttributes()
	{
		return array(
			'id'        => array(AttributeType::Number),
			'locale'    => array(AttributeType::Locale),
			'identity'  => array(AttributeType::Mixed),
			'meta'      => array(AttributeType::Mixed),
			'ownership' => array(AttributeType::Mixed),
			'contacts'  => array(AttributeType::Mixed),
			'social'    => array(AttributeType::Mixed),
		);
	}
}
