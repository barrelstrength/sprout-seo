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
	 * @var SproutSeo_GlobalMetadataService
	 */
	public $globalMetadata;

	/**
	 * @var SproutSeo_SectionMetadataService
	 */
	public $sectionMetadata;

	/**
	 * @var SproutSeo_ElementMetadataService
	 */
	public $elementMetadata;

	/**
	 * @var SproutSeo_SchemaService
	 */
	public $schema;

	/**
	 * @var SproutSeo_SitemapService
	 */
	public $sitemap;

	/**
	 * @var SproutSeo_RedirectsService
	 */
	public $redirects;

	/**
	 * @var SproutSeo_SettingsService
	 */
	public $settings;

	public function init()
	{
		parent::init();

		$this->optimize        = Craft::app()->getComponent('sproutSeo_optimize');
		$this->globalMetadata  = Craft::app()->getComponent('sproutSeo_globalMetadata');
		$this->sectionMetadata = Craft::app()->getComponent('sproutSeo_sectionMetadata');
		$this->elementMetadata = Craft::app()->getComponent('sproutSeo_elementMetadata');
		$this->schema          = Craft::app()->getComponent('sproutSeo_schema');
		$this->sitemap         = Craft::app()->getComponent('sproutSeo_sitemap');
		$this->redirects       = Craft::app()->getComponent('sproutSeo_redirects');
		$this->settings        = Craft::app()->getComponent('sproutSeo_settings');
	}
}
