<?php
namespace Craft;

class SproutSeoVariable
{
	protected $plugin;

	public function __construct()
	{
		$this->plugin = craft()->plugins->getPlugin('sproutseo');
	}

	## ------------------------------------------------------------
	## General Variables
	## ------------------------------------------------------------

	public function getName()
	{
		return $this->plugin->getName();
	}

	public function getVersion()
	{
		return $this->plugin->getVersion();
	}

	## ------------------------------------------------------------
	## Meta Variables (Front-end)
	## ------------------------------------------------------------

	/**
	* Set SEO Meta data in our templates
	*
	* @param  array  $meta Array of supported meta values
	* @return [type]       [description]
	*/
	public function meta(array $meta = array())
	{
		if (count($meta))
		{
			craft()->sproutSeo_meta->updateMeta($meta);
		}
	}

	/**
	* Output our SEO Meta Tags, and provide fallbacks
	*
	* @param  [type] $overrideInfo [description]
	* @return [type]               [description]
	*/
	public function optimize(array $meta = array())
	{
		// @TODO - should optimize accept a fallback array?
		// maybe just allow this to be set in the CP.
		if (count($meta))
		{
			// If an array is defined, add it to our meta array before we output anything
			craft()->sproutSeo_meta->updateMeta($meta, true);
		}

		// This is our getter
		$overrideInfo = craft()->sproutSeo_meta->getMeta();

		// Output the metadata as pre-defined HTML
		$output = craft()->sproutSeo_meta->optimize($overrideInfo);

		return new \Twig_Markup($output, craft()->templates->getTwig()->getCharset());
	}

	/**
	* @DEPRECATED - Now use optimize()
	*/
	public function define($overrideInfo)
	{
		craft()->deprecator->log('{{ craft.sproutSeo.define() }}', '<code>{{ craft.sproutSeo.define() }}</code> has been deprecated. Use <code>{{ craft.sproutSeo.optimize() }}</code> instead.');

		return $this->optimize($overrideInfo);
	}

  ## ------------------------------------------------------------
  ## Meta Variables (Control Panel)
  ## ------------------------------------------------------------

  /**
   * Get all Templates
   *
   * @return [type] [description]
   */
  public function allDefaults()
  {
	return craft()->sproutSeo_meta->getAllDefaults();
  }

  /**
   * Get a specific template. If no template is found, returns null
   *
   * @param  int   $id
   * @return mixed
   */
  public function getDefaultById($id)
  {
	return craft()->sproutSeo_meta->getDefaultById($id);
  }

  public function displayGlobalFallback($defaultId = null)
  {
	return craft()->sproutSeo_meta->displayGlobalFallback($defaultId);
  }

  ## ------------------------------------------------------------
  ## Sitemap Variables (Front-end)
  ## ------------------------------------------------------------

  public function getSitemap()
  {
	$sitemap = craft()->sproutSeo_sitemap->getSitemap();

	return new \Twig_Markup($sitemap, craft()->templates->getTwig()->getCharset());
  }

  ## ------------------------------------------------------------
  ## Sitemap Variables (Control Panel)
  ## ------------------------------------------------------------

  /**
   * Get all Sections for our Sitemap settings.
   *
   * @return array of Sections
   */
  public function getAllSections()
  {
	return craft()->sections->getAllSections();
  }

  /**
   * Get all Sections for our Sitemap settings.
   *
   * @return array of Sections
   */
  public function getAllSectionsWithUrls()
  {
	return craft()->sproutSeo_sitemap->getAllSectionsWithUrls();
  }

  /**
   * Get all Custom Pages for our Sitemap settings.
   *
   * @return array of Sections
   */
  public function getAllCustomPages()
  {
	return craft()->sproutSeo_sitemap->getAllCustomPages();
  }

}
