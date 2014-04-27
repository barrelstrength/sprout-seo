<?php
namespace Craft;

class SproutSeoVariable
{

  public function getName()
  {
    $plugin = craft()->plugins->getPlugin('sproutseo');

    return $plugin->getName();
  }

  public function getVersion()
  {
    $plugin = craft()->plugins->getPlugin('sproutseo');

    return $plugin->getVersion();
  }

  /**
   * Output our SEO Meta Tags
   * 
   * @param  [type] $overrideInfo [description]
   * @return [type]               [description]
   */
  public function optimize($overrideInfo)
  {
    $output = craft()->sproutSeo->optimize($overrideInfo);

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

  /**
   * Get all Fallback Templates
   * 
   * @return [type] [description]
   */
  public function allFallbacks()
  {
    return craft()->sproutSeo->getAllFallbacks();
  }

  /**
   * Get a specific fallback. If no fallback is found, returns null
   *
   * @param  int   $id
   * @return mixed
   */
  public function getFallbackById($id)
  {
    return craft()->sproutSeo->getFallbackById($id);
  }

  /**
   * Get all Sections for our Sitemap settings.
   *
   * @return array of Sections
   */
  public function getAllSections()
  { 
    return craft()->sections->getAllSections();
  }

}
