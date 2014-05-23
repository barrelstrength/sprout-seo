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


  public function meta(array $meta = array())
  {
    if (count($meta)) 
    {
      // This is our setter
      craft()->sproutSeo->updateMeta($meta);
    }
    else
    {
      // This is our getter
      $overrideInfo = craft()->sproutSeo->getMeta();
      
      // Output the metadata as pre-defined HTML
      $output = craft()->sproutSeo->optimize($overrideInfo);

      return new \Twig_Markup($output, craft()->templates->getTwig()->getCharset());
    }
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

  /**
   * Get all Sections for our Sitemap settings.
   *
   * @return array of Sections
   */
  public function getAllSectionsWithUrls()
  { 
    return craft()->sproutSeo_sitemap->getAllSectionsWithUrls();
  }

  public function getSitemap()
  {
    $sitemap = craft()->sproutSeo_sitemap->getSitemap();

    return new \Twig_Markup($sitemap, craft()->templates->getTwig()->getCharset());
  }

}
