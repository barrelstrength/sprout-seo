<?php
namespace Craft;

/**
 * Class SproutSeoVariable
 *
 * @package Craft
 */
class SproutSeoVariable
{
	/**
	 * @var SproutSeoPlugin
	 */
	protected $plugin;

	public function __construct()
	{
		$this->plugin = craft()->plugins->getPlugin('sproutseo');
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->plugin->getName();
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->plugin->getVersion();
	}

	/**
	 * Sets SEO metadata in templates
	 *
	 * @param array $meta Array of supported meta values
	 */
	public function meta(array $meta = array())
	{
		if (count($meta))
		{
			sproutSeo()->optimize->updateMeta($meta);
		}
	}

	/**
	 * Processes and outputs SEO meta tags
	 *
	 * @return \Twig_Markup
	 */
	public function optimize()
	{
		$output = sproutSeo()->optimize->optimize();

		return TemplateHelper::getRaw($output);
	}

	/**
	 * Prepare an array of the optimized Meta
	 *
	 * @return multi-dimensional array
	 */
	public function getOptimizedMeta()
	{
		$prioritizedMetaModel = sproutSeo()->optimize->getOptimizedMeta();

		return $prioritizedMetaModel->getMetaTagData();
	}

	/**
	 * @deprecated Deprecated in favor of optimize()
	 *
	 */
	public function define($overrideInfo)
	{
		craft()->deprecator->log('{{ craft.sproutSeo.define() }}', '<code>{{ craft.sproutSeo.define() }}</code> has been deprecated. Use <code>{{ craft.sproutSeo.optimize() }}</code> instead.');

		return $this->optimize($overrideInfo);
	}

	/**
	 * Returns all templates
	 *
	 * @return mixed
	 */
	public function getDefaults()
	{
		return sproutSeo()->defaults->getDefaults();
	}

	/**
	 * Returns a specific template if found
	 *
	 * @param int $id
	 *
	 * @return null|mixed
	 */
	public function getDefaultById($id)
	{
		return sproutSeo()->defaults->getDefaultById($id);
	}

	/**
	 * @param $handle
	 *
	 * @return SproutSeo_MetaTagModel
	 */
	public function getDefaultByHandle($handle)
	{
		return sproutSeo()->defaults->getDefaultByHandle($handle);
	}

	/**
	 * @param null $defaultId
	 *
	 * @return mixed
	 */
	public function globalFallbackId()
	{
		return sproutSeo()->defaults->globalFallbackId();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getSitemap(array $options = null)
	{
		return sproutSeo()->sitemap->getSitemap($options);
	}

	/**
	 * Returns all custom pages for sitemap settings
	 *
	 * @return array of Sections
	 */
	public function getAllCustomPages()
	{
		return sproutSeo()->sitemap->getAllCustomPages();
	}

	/**
	 * Returns all sitemaps
	 *
	 * @return array of Sections
	 */
	public function getAllSitemaps()
	{
		return sproutSeo()->sitemap->getAllSitemaps();
	}

	/**
	 * Returns all custom names
	 *
	 * @return array of Sections
	 */
	public function getAllCustomNames()
	{
		return sproutSeo()->sitemap->getAllCustomNames();
	}

	public function getDivider()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;
	}

	public function getSettings()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings();
	}

	public function getGlobalKnowledgeGraphMeta()
	{
		return sproutSeo()->schema->getGlobalKnowledgeGraphMeta();
	}

	public function prepareKnowledgeGraphStructuredData()
	{
		return sproutSeo()->schema->prepareKnowledgeGraphStructuredData();
	}
}
