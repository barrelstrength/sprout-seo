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
   * Get all Templates
   * 
   * @return [type] [description]
   */
  public function allTemplates()
  {
    return craft()->sproutSeo->getAllTemplates();
  }

  /**
   * Get a specific template. If no template is found, returns null
   *
   * @param  int   $id
   * @return mixed
   */
  public function getTemplateById($id)
  {
    return craft()->sproutSeo->getTemplateById($id);
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
