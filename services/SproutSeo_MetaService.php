<?php
namespace Craft;

class SproutSeo_MetaService extends BaseApplicationComponent
{
	protected $metaRecord;
	protected $seoOverrideRecord;
	protected $sitemapRecord;
	protected $siteInfo;
	protected $divider;

	protected $fallbackMeta = array();
	protected $sproutmeta = array();

	public function __construct($metaRecord = null, $seoOverrideRecord = null, $sitemapRecord = null)
	{
		$this->metaRecord = $metaRecord;
		if (is_null($this->metaRecord)) {
			$this->metaRecord = SproutSeo_TemplatesRecord::model();
		}

		$this->seoOverrideRecord = $seoOverrideRecord;
		if (is_null($this->seoOverrideRecord)) {
			$this->seoOverrideRecord = SproutSeo_OverridesRecord::model();
		}

		$this->sitemapRecord = $sitemapRecord;
		if (is_null($this->sitemapRecord)) {
			$this->sitemapRecord = SproutSeo_SitemapRecord::model();
		}
	}

	/**
	 * Get a new blank item
	 *
	 * @param  array               $attributes
	 * @return SproutSeo_MetaModel
	 */
	public function newMetaModel($attributes = array())
	{
		$model = new SproutSeo_MetaModel();
		$model->setAttributes($attributes);

		return $model;
	}

	/**
	 * Get all Templates from the database.
	 *
	 * @return array
	 */
	public function getAllTemplates()
	{
		$records = $this->metaRecord->findAll(array('order'=>'name'));

		return SproutSeo_MetaModel::populateModels($records, 'id');
	}

	/**
	 * Get a specific Templates from the database based on ID. If no Templates exists, null is returned.
	 *
	 * @param  int   $id
	 * @return mixed
	 */
	public function getTemplateById($id)
	{
		if ($record = $this->metaRecord->findByPk($id))
		{
			return SproutSeo_MetaModel::populateModel($record);
		}
		else
		{
    	return new SproutSeo_MetaModel();
    }
	}

	public function getTemplateByTemplateHandle($handle)
	{
		$query = craft()->db->createCommand()
					->select('*')
					->from('sproutseo_templates')
					->where('handle=:handle', array(':handle'=> $handle))
					->queryRow();

		$model = SproutSeo_MetaModel::populateModel($query);

		$model->robots = ($model->robots) ? $this->prepRobotsForSettings($model->robots) : null;

		if ($model->latitude && $model->longitude)
		{
			$model->position = $model->latitude . ";" . $model->longitude;
		}

		return $model;
	}

	public function saveTemplateInfo(SproutSeo_MetaModel &$model)
	{

	   if ($id = $model->getAttribute('id'))
       {
			if (null === ($record = $this->metaRecord->findByPk($id)))
            {
				throw new Exception(Craft::t('Can\'t find template with ID "{id}"', array('id' => $id)));
			}
	   }
       else
       {
           $record = $this->metaRecord->create();
	   }

		// @TODO passing 'false' here allows us to save unsafe attributes
		// we should really update this to address validation better.
		$record->setAttributes($model->getAttributes(), false);

		if ($record->save())
        {
			// update id on model (for new records)
			$model->setAttribute('id', $record->getAttribute('id'));

			return true;
		}
        else
        {
			$model->addErrors($record->getErrors());

			return false;
		}

	}

	public function getOverrideById($id)
	{
		if ($record = $this->seoOverrideRecord->findByPk($id))
        {
			return SproutSeo_OverridesModel::populateModel($record);
		}
        else
        {
            return false;
        }
	}

	public function getOverrideByEntryId($entryId)
	{
		$query = craft()->db->createCommand()
				   ->select('*')
				   ->from('sproutseo_overrides')
				   ->where('entryId = :entryId', array(':entryId' => $entryId))
				   ->queryRow();

		return SproutSeo_OverridesModel::populateModel($query);

	}

	public function getBasicMetaFeildsByEntryId($entryId)
	{
		$query = craft()->db->createCommand()
				   ->select('id, title, description, keywords')
				   ->from('sproutseo_overrides')
				   ->where('entryId = :entryId', array(':entryId' => $entryId))
				   ->queryRow();

	   if (isset($query))
	   {
			return SproutSeo_BasicMetaFieldModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_BasicMetaFieldModel;
		}

	}

	public function getTwitterCardFieldsByEntryId($entryId)
	{
		$query = craft()->db->createCommand()
			->select('id, twitterCard, twitterSite, twitterCreator, twitterTitle, twitterDescription')
			->from('sproutseo_overrides')
			->where('entryId = :entryId', array(
				':entryId' => $entryId
				)
			)
			->queryRow();

	if (isset($query))
	{
			return SproutSeo_TwitterCardFieldModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_TwitterCardFieldModel;
		}

	}

	public function getGeographicMetaFeildsByEntryId($entryId)
	{
		$query = craft()->db->createCommand()
				   ->select('region, placename, longitude, latitude')
				   ->from('sproutseo_overrides')
				   ->where('entryId = :entryId', array(':entryId' => $entryId))
				   ->queryRow();

	   if (isset($query))
	   {
			return SproutSeo_GeographicMetaFieldModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_GeographicMetaFieldModel;
		}

	}

	public function getRobotsMetaFeildsByEntryId($entryId)
	{
		$query = craft()->db->createCommand()
				   ->select('canonical, robots')
				   ->from('sproutseo_overrides')
				   ->where('entryId = :entryId', array(':entryId' => $entryId))
				   ->queryRow();

	   if (isset($query))
	   {
			return SproutSeo_RobotsMetaFieldModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_RobotsMetaFieldModel;
		}

	}

	public function createOverride($attributes)
	{
		craft()->db->createCommand()
					   ->insert('sproutseo_overrides', $attributes);
	}

	public function updateOverride($id, $attributes)
	{
		craft()->db->createCommand()
		->update('sproutseo_overrides',
			$attributes,
			'id = :id', array(':id' => $id)
		);

	}

	public function deleteOverrideById($id = null)
	{
		$record = new SproutSeo_OverridesRecord;

		// @TODO is this the right way to do this?  Would this actually return
		// true or false?
		// Returns the number of rows deleted
		// ref: http://www.yiiframework.com/doc/api/1.1/CActiveRecord#deleteByPk-detail
		return $record->deleteByPk($id);


	}

	/**
	 * Deletes a template
	 *
	 * @param int
	 * @return bool
	 */
	public function deleteTemplate($id = null)
	{
		$record = new SproutSeo_TemplatesRecord;
		return $record->deleteByPk($id);
	}

	public function optimize($overrideInfo)
	{
		// by default don't append anything to the end of our title
		$this->siteInfo = "";

		// Divider from settings
		$this->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// If no divider exists, use a dash
		$this->divider = ($this->divider) ? $this->divider : '-';

		// Setup all of our SEO Metadata Arrays
		$entryOverrides = new SproutSeo_MetaModel; // Top Priority
		$codeOverrides  = new SproutSeo_MetaModel; // Second Priority
		$templates      = array(); // Lowest Priority


		// PREPARE Templates
		// ------------------------------------------------------------

		// If our code references a template template, create our template array
		// If no template template is mentioned, we have an empty array
		if (isset($overrideInfo['template']))
		{

			$templateHandle = $overrideInfo['template'];
			$templates = craft()->sproutSeo_meta->getTemplateByTemplateHandle($templateHandle);

			// create the string we will append to the end of our title if we should
			if (isset($templates->appendSiteName))
			{
			  $this->siteInfo = " " . $this->divider . " " . craft()->getInfo('siteName');
			}

			// Remove our template so we can assign the rest of our info to the codeOverride
			// array and have it match up nicely.
			// @TODO - may need to move this outside this if statement, or include other
			// values that aren't part of the seo metadata model
			unset($overrideInfo['template']);
		}


		// Set the default canonical URL to be the current URL
		$scheme = ( isset($_SERVER['HTTPS'] ) ) ? "https://" : "http://" ;
		$siteUrl = $scheme . $_SERVER['SERVER_NAME'];
		$currentUrl = $siteUrl . craft()->request->url;

		$templates['canonical'] = $currentUrl;


		// PREPARE ENTRY OVERRIDES
		// ------------------------------------------------------------

		// If our code overrides include an ID, let's query the database and
		// see if this entry has any Entry Overrides.
		if (isset($overrideInfo['id']))
		{
		  // query for override array
		  $entryOverrides = craft()->sproutSeo_meta->getOverrideByEntryId($overrideInfo['id']);

		  unset($overrideInfo['id']);
		}


		// PREPARE CODE OVERRIDES
		// ------------------------------------------------------------

		// If we have any more values that were set in our template
		// let's store them as code overrides.
		if ( ! empty($overrideInfo))
		{
		  $codeOverrides = SproutSeo_MetaModel::populateModel($overrideInfo);
		}

		// @TODO - this is temporary, figure out the best syntax for 'Robots' values
		// and update this to accomodate both the On-page and Code override situations
		$codeOverrides->robots = ($codeOverrides->robots)
		  ? $codeOverrides->robots
		  : null;


		// PRIORITIZE OUR METADATA

		// For each item in our SEO DATA model, loop through
		// and select the highest ranking item to output.
		//
		// 1) Entry Override
		// 2) On-Page Override
		// 3) Template
		// 4) Blank

		// Once we have added all the content we need to be outputting
		// to our array we will loop through that array and create the
		// HTML we will output to our page.
		//
		// While we don't define HTML in our PHP as much as possible, the
		// goal here is to be as easy to use as possible on the front end
		// so we want to simplify the front end code to a single function
		// and wrangle what we need to here.

		$metaValues = $this->_prioritizeMetaValues($entryOverrides, $codeOverrides, $templates);

		$output = "\n";
		$openGraphPattern = '/^og:/';
		$twitterPattern = '/^twitter:/';

		foreach ($metaValues as $name => $value)
		{

		  if ($value)
		  {
				switch ($name)
				{

				  // Title tag
				  case 'title':
					$output .= "\t<title>$value".$this->siteInfo."</title>\n";
					break;

				  // Open Graph Tags
				  case (preg_match($openGraphPattern, $name) ? true : false):
					$output .= "\t<meta property='$name' content='$value' />\n";
					break;

					// Twitter Cards
				  case (preg_match($twitterPattern, $name) ? true : false):
					$output .= "\t<meta name='$name' content='$value'>\n";
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

		return $output;
	}

	private function _prioritizeMetaValues($entryOverrides, $codeOverrides, $templates)
	{

	  $metaValues = array();

	  // Loop through the entry override model
	  // @TODO - make sure we loop through a defined model... we may not have an
	  // entry override model each time... or maybe we can just define it so its
	  // blank nomatter what.  We really just need to know we are looping through
	  // the samme model for each of the levels of overrides or templates
	  foreach ($entryOverrides->getAttributes() as $key => $value)
    {
	    if ($entryOverrides->getAttribute($key))
      {
	      $metaValues[$key] = $value;
	    }
      elseif ($codeOverrides->getAttribute($key))
      {
	      $metaValues[$key] = $codeOverrides[$key];
	    }
      elseif (isset($templates->handle))
      {
	      $metaValues[$key] = $templates->getAttribute($key);
	    }
      else
      {
	      // We got nuthin'
	      $metaValues[$key] = '';
	    }
	  }

	  // Unset general template info
	  unset($metaValues['id']);
	  unset($metaValues['entryId']);
	  unset($metaValues['name']);
	  unset($metaValues['handle']);
	  unset($metaValues['appendSiteName']);
	  unset($metaValues['globalFallback']);

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
	    'ogLocale'       => 'og:locale',
	    'twitterCard'    => 'twitter:card',
	    'twitterSite'    => 'twitter:site',
	    'twitterCreator' => 'twitter:creator',
	    'twitterTitle'   => 'twitter:title',
	    'twitterDescription' => 'twitter:description',
	    'twitterImage'    => 'twitter:image'
	  );

	  // update our array to use the actual meta name="" parameter values
	  // as our index
	  $meta = array();
	  foreach ($metaValues as $name => $value)
      {
	    $meta[$metaNames[$name]] = $value;
	  }

	  return $meta;
	}

	public function getMeta()
	{
		return $this->sproutmeta;
	}

	public function updateMeta($meta, $fallback = false)
	{
		// @TODO - should updateMeta accept a fallback array?
		// maybe just allow this to be set in the CP.

		if (!$fallback)
		{
			// add meta values to the global meta value
			if (count($meta))
			{
			  foreach ($meta as $key => $value)
			  {
			    // This is the setter
			    $this->sproutmeta[$key] = $value;
			  }
			}
		}
		else
		{
			// merge the optimize fallback with the rest of our meta values
			array_merge($meta, $this->sproutmeta);
	  }
	}

	public function getGlobalFallback()
	{
		return craft()->db->createCommand()
									->select('*')
									->from('sproutseo_templates')
									->where('globalFallback=:globalFallback', array(':globalFallback' => 1))
									->queryRow();
	}

	public function displayGlobalFallback($templateId = null)
	{
		$fallback = $this->getGlobalFallback();

		$isGlobalFallback = ( isset($fallback['id']) && ($templateId == $fallback['id']) );
		$noFallbackExists = !isset($fallback['id']);

    if ($isGlobalFallback OR $noFallbackExists)
    {
    	return true;
    }
    else
    {
    	return false;
    }
	}

	public function prepRobotsForDb($robotsArray)
	{
		return StringHelper::arrayToString($robotsArray);
	}

	public function prepRobotsForSettings($robotsString)
	{
		return ArrayHelper::stringToArray($robotsString);
	}
}
