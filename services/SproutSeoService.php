<?php
namespace Craft;

/**
 * Class SproutSeoService
 *
 * @package Craft
 */
class SproutSeoService extends BaseApplicationComponent
{
	public $meta;
	public $settings;
	public $sitemap;

	public function init()
	{
		parent::init();

		$this->meta       = Craft::app()->getComponent('sproutSeo_meta');
		$this->settings   = Craft::app()->getComponent('sproutSeo_settings');
		$this->sitemap    = Craft::app()->getComponent('sproutSeo_sitemap');
	}
}