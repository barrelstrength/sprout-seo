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
	 * @var SproutSeo_OptimizeService
	 */
	public $optimize;

	/**
	 * @var SproutSeo_MetadataService
	 */
	public $metadata;

	/**
	 * @var SproutSeo_GlobalsService
	 */
	public $globals;

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

		$this->optimize  = Craft::app()->getComponent('sproutSeo_optimize');
		$this->metadata  = Craft::app()->getComponent('sproutSeo_metadata');
		$this->globals   = Craft::app()->getComponent('sproutSeo_globals');
		$this->schema    = Craft::app()->getComponent('sproutSeo_schema');
		$this->sitemap   = Craft::app()->getComponent('sproutSeo_sitemap');
		$this->settings  = Craft::app()->getComponent('sproutSeo_settings');
		$this->redirects = Craft::app()->getComponent('sproutSeo_redirects');
	}
}
