<?php
namespace Craft;

/**
 * Class SproutSeoService
 *
 * @package Craft
 */
class SproutSeoService extends BaseApplicationComponent
{
	/**
	 * @var SproutSeo_MetaService
	 */
	public $meta;

	/**
	 * @var SproutSeo_MetaDefaultsService
	 */
	public $defaults;

	/**
	 * @var SproutSeo_MetaOverridesService
	 */
	public $overrides;

	/**
	 * @var SproutSeo_SchemaService
	 */
	public $schema;

	/**
	 * @var SproutSeo_SitemapService
	 */
	public $sitemap;

	/**
	 * @var SproutSeo_SettingsService
	 */
	public $settings;

	/**
	 * @var SproutSeo_RedirectsService
	 */
	public $redirects;

	public function init()
	{
		parent::init();

		$this->meta      = Craft::app()->getComponent('sproutSeo_meta');
		$this->defaults  = Craft::app()->getComponent('sproutSeo_metaDefaults');
		$this->overrides = Craft::app()->getComponent('sproutSeo_metaOverrides');
		$this->schema    = Craft::app()->getComponent('sproutSeo_schema');
		$this->sitemap   = Craft::app()->getComponent('sproutSeo_sitemap');
		$this->settings  = Craft::app()->getComponent('sproutSeo_settings');
		$this->redirects = Craft::app()->getComponent('sproutSeo_redirects');
	}
}
