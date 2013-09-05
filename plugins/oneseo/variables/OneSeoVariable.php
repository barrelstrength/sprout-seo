<?php
namespace Craft;

class OneSeoVariable
{
    /**
     * Plugin Name
     * Make your plugin name available as a variable
     * in your templates as {{ craft.YourPlugin.name }}
     *
     * @return string
     */
    public function getName()
    {
      $plugin = craft()->plugins->getPlugin('oneseo');

      return $plugin->getName();
    }

    public function getVersion()
    {
      $plugin = craft()->plugins->getPlugin('oneseo');

      return $plugin->getVersion();
    }

    /**
     * Define our SEO Meta Tag Output
     * 
     * @param  [type] $overrideInfo [description]
     * @return [type]               [description]
     */
    public function define($overrideInfo)
    {

    // by default don't append anything to the end of our title
    $this->siteInfo = "";

    // set our divider
    $divider = craft()->plugins->getPlugin('oneseo')->getSettings()->seoDivider;

    // create the string we will append to the end of our title if we should
    if (craft()->plugins->getPlugin('oneseo')->getSettings()->appendSiteName) {
      $this->siteInfo = " " . $divider . " " . Craft::getInfo('siteName');
    }

    // Setup all of our SEO Metadata Arrays
    $entryOverrides = new OneSeo_SeoDataModel; // Top Priority
    $codeOverrides  = new OneSeo_SeoDataModel; // Second Priority
    $fallbacks      = array(); // Lowest Priority


    // PREPARE FALLBACKS
    // ------------------------------------------------------------

    // If our code references a fallback template, create our fallback array
    // If no fallback template is mentioned, we have an empty array
    if ($templateHandle = $overrideInfo['template']) {
       $fallbacks = craft()->oneSeo->getFallbackByTemplateHandle($templateHandle);

       // Remove our template so we can assign the rest of our info to the codeOverride
       // array and have it match up nicely.
       // @TODO - may need to move this outside this if statement, or include other
       // values that aren't part of the seo metadata model
       unset($overrideInfo['template']);
    }

    // PREPARE ENTRY OVERRIDES
    // ------------------------------------------------------------

    // If our code overrides include an ID, let's query the database and
    // see if this entry has any Entry Overrides.
    if (isset($overrideInfo['id'])) {
      // query for override array
      $entryOverrides = craft()->oneSeo->getOverrideByEntryId($overrideInfo['id']);

      unset($overrideInfo['id']);
    }


    // PREPARE CODE OVERRIDES
    // ------------------------------------------------------------

    // If we have any more values that were set in our template
    // let's store them as code overrides.
    if ( ! empty($overrideInfo)) {
      $codeOverrides = OneSeo_SeoDataModel::populateModel($overrideInfo);
    }

    // @TODO - this is temporary, figure out the best syntax for 'Robots' values 
    // and update this to accomodate both the Fallback and Code override situations
    $codeOverrides->robots = ($codeOverrides->robots)
      ? craft()->oneSeo->prepRobotsArray($codeOverrides->robots)
      : null;
    

    // PRIORITIZE OUR METADATA

    // For each item in our SEO DATA model, loop through
    // and select the highest ranking item to output.
    //
    // 1) Entry Override
    // 2) Template Override
    // 3) Fallback Template
    // 4) Blank

    // Once we have added all the content we need to be outputting
    // to our array we will loop through that array and create the
    // HTML we will output to our page.
    //
    // While we don't define HTML in our PHP as much as possible, the
    // goal here is to be as easy to use as possible on the front end
    // so we want to simplify the front end code to a single function
    // and wrangle what we need to here.

    $metaValues = $this->_prioritizeMetaValues($entryOverrides, $codeOverrides, $fallbacks);
    
    $output = "\n";
    $openGraphPattern = '/^og:/';

    foreach ($metaValues as $name => $value) 
    {
      
      if ($value)
      {
        switch ($name) {

          // Title tag
          case 'title':
            $output .= "\t<title>$value".$this->siteInfo."</title>\n";
            break;

          // Open Graph Tags
          case (preg_match($openGraphPattern, $name) ? true : false):
            $output .= "\t<meta property='$name' content='$value' />\n";
            break;

          // Canonical URLs
          case 'canonical':
            $output .= "\t<link rel='canonical' href='$value' />\n";
            break;
          
          // Robots
          case 'robots':
            $output .= "\t<meta name='robots' content='$value' />\n";
            break;

          // Standard Meta Tags
          default:
            $output .= "\t<meta name='$name' content='$value' />\n";
            break;
        }
      }

    }

    return new \Twig_Markup($output, craft()->templates->getTwig()->getCharset());
  }


  public function _prioritizeMetaValues($entryOverrides, $codeOverrides, $fallbacks)
  {

    $metaValues = array();

    // Loop through the entry override model
    // @TODO - make sure we loop through a defined model... we may not have an
    // entry override model each time... or maybe we can just define it so its
    // blank nomatter what.  We really just need to know we are looping through
    // the samme model for each of the levels of overrides or fallbacks
    foreach ($entryOverrides->getAttributes() as $key => $value) {

      if ($entryOverrides->getAttribute($key)) {
        $metaValues[$key] = $value;
      } elseif ($codeOverrides->getAttribute($key)) {
        $metaValues[$key] = $codeOverrides[$key];
      } elseif (isset($fallbacks->handle)) {
        $metaValues[$key] = $fallbacks->getAttribute($key);
      } else {
        // We got nuthin'
        $metaValues[$key] = '';
      }
    }

    // Unset general fallback info
    unset($metaValues['id']);
    unset($metaValues['entryId']);
    unset($metaValues['name']);
    unset($metaValues['handle']);

    // These values get combined and become: geo.position
    unset($metaValues['longitude']);
    unset($metaValues['latitude']);

    $metaNames = array(
      'title'          => 'title',
      'description'    => 'description',
      'keywords'       => 'keywords',
      'robots'         => 'robots',
      'canonical'      => 'canonical',
      'region'         => 'geo.region',
      'placename'      => 'geo.placename',
      'position'       => 'geo.position',
      'ogTitle'        => 'og:title',
      'ogType'         => 'og:type',
      'ogUrl'          => 'og:url',
      'ogImage'        => 'og:image',
      'ogSiteName'     => 'og:site_name',
      'ogDescription'  => 'og:description',
      'ogAudio'        => 'og:audio',
      'ogVideo'        => 'og:video',
      'ogLocale'       => 'og:locale'
    );

    // update our array to use the actual meta name="" parameter values
    // as our index
    $meta = array();
    foreach ($metaValues as $name => $value) {
      $meta[$metaNames[$name]] = $value;
    }

    return $meta;
  }


  /**
   * Get all Fallback Templates
   * 
   * @return [type] [description]
   */
  public function allFallbacks()
  {
    $fallbacks = array();

    $record = craft()->oneSeo->getAllFallbacks();

    $i = 0;

    foreach ($record as $key => $value) {

        $fallbacks[$i] = $value->getAttributes();
        $fallbacks[$i]['editUrl'] = 'fallbacks/' . $fallbacks[$i]['id'];

        $i++;
    }

    return $fallbacks;
  }


  /**
   * Get a specific fallback. If no fallback is found, returns null
   *
   * @param  int   $id
   * @return mixed
   */
  public function getFallbackById($id)
  {
      if ($fallback = craft()->oneSeo->getFallbackById($id)) {
          $return = $fallback->getAttributes();
      } else {
          $return = new OneSeo_SeoDataModel();
      }

      return $return;
  }

  /**
   * Get a specific Fallback Tempalte id
   * 
   * @return [type] [description]
   */
  public function getFallbackId()
  {
      $fallbackId = craft()->request->getSegment(3);

      if ($fallbackId == 'new') {
          $fallbackId = null;
      }

      return $fallbackId;
  }

}
