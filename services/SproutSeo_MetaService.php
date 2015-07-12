<?php
namespace Craft;

class SproutSeo_MetaService extends BaseApplicationComponent
{
	protected $sproutmeta = array();

	protected $siteInfo;
	protected $divider;
	protected $currentUrl;

	/**
	 * Store our meta data in a place we can access nicely
	 *
	 * @return array
	 */
	public function getMeta()
	{
		return $this->sproutmeta;
	}

	/**
	 * Add values to the master $this->sproutmeta array
	 *
	 * @param $meta
	 */
	public function updateMeta($meta)
	{
		if (count($meta))
		{
			foreach ($meta as $key => $value)
			{
				$this->sproutmeta[$key] = $value;
			}
		}
	}

	/**
	 * @param $overrideInfo
	 *
	 * @return string
	 */
	public function optimize()
	{
		$prioritizedMetaModel = $this->getOptimizedMeta();

		$variables['meta'] = $prioritizedMetaModel->getMetaTagData();

		craft()->path->setTemplatesPath(craft()->path->getPluginsPath());

		$output = craft()->templates->render('sproutseo/templates/_special/meta', $variables);

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return $output;
	}

	/**
	 * Prioritize our meta data
   * ------------------------------------------------------------
   *
   * Loop through and select the highest ranking value for each attribute in our SproutSeo_MetaData model
   *
   * 1) Entry Override (Set by adding `id` override in Twig template code and using Meta Fields)
   * 2) On-Page Override (Set in Twig template code)
   * 3) Default (Set in control panel)
	 * 4) Global Fallback (Set in control panel)
   * 5) Blank (Automatic)
   *
   * Once we have added all the content we need to be outputting to our array we will loop through that array and create the HTML we will output to our page.
   *
   * While we don't define HTML in our PHP as much as possible, the goal here is to be as easy to use as possible on the front end so we want to simplify the front end code to a single function and wrangle what we need to here.
	 *
	 * @param $entryOverrideMetaModel
	 * @param $codeOverrideMetaModel
	 * @param $defaultsMetaModel
	 * @param $globalFallbackMetaModel
	 * @return array
	 * @throws \Exception
	 */
	public function getOptimizedMeta()
	{
		// Prepare a SproutSeo_MetaModel for each of our levels of priority
		$entryOverrideMetaModel  = $this->_getEntryOverridesMetaModel($this->getMeta());
		$codeOverrideMetaModel   = $this->_getCodeOverridesMetaModel($this->getMeta());
		$defaultsMetaModel       = $this->_getDefaultsMetaModel($this->getMeta());
		$globalFallbackMetaModel = $this->_getGlobalFallbackMetaModel();

		$prioritizedMetaModel = new SproutSeo_MetaModel();

		$this->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// Default to the Current URL
		// @todo - this is getting overriden for some reason, even when it shouldn't be
		$prioritizedMetaModel->canonical = SproutSeoMetaHelper::prepareCanonical($prioritizedMetaModel);

		foreach ($prioritizedMetaModel->getAttributes() as $key => $value)
		{
			// Test for a value on each of our models in their order of priority
			if ($entryOverrideMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $entryOverrideMetaModel[$key];
			}
			elseif ($codeOverrideMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $codeOverrideMetaModel[$key];
			}
			elseif ($defaultsMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $defaultsMetaModel->getAttribute($key);
			}
			elseif ($globalFallbackMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $globalFallbackMetaModel->getAttribute($key);
			}
			else
			{
				$prioritizedMetaModel[$key] = '';
			}
		}

		// @todo - reorganize how this stuff works / robots need love.
		$prioritizedMetaModel->title = SproutSeoMetaHelper::prepareAppendedSiteName($prioritizedMetaModel, $defaultsMetaModel, $globalFallbackMetaModel);
		$prioritizedMetaModel->robots = SproutSeoMetaHelper::ensureRobotsHasValues($prioritizedMetaModel);
		
		return $prioritizedMetaModel;
	}

	public function displayGlobalFallback($defaultId = null)
	{
		$globalFallbackMetaModel = $this->_getGlobalFallbackMetaModel();

		$isGlobalFallback = ( $globalFallbackMetaModel->id && ($defaultId == $globalFallbackMetaModel->id) );
		$fallbackExists = !is_null($globalFallbackMetaModel->id);

		if ($isGlobalFallback OR !$fallbackExists)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function _getGlobalFallbackMetaModel()
	{
		$globalFallback = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_defaults')
			->where('globalFallback=:globalFallback', array(':globalFallback' => 1))
			->queryRow();

		$model = SproutSeo_MetaModel::populateModel($globalFallback);
		$model->canonical = SproutSeoMetaHelper::prepareCanonical($model);

		SproutSeoMetaHelper::prepareAssetUrls($model);

		return $model;
	}

	/**
	 * Create our default SproutSeo_MetaModel
	 *
	 * @param $overrideInfo
	 * @return SproutSeo_MetaModel
	 */
	private function _getDefaultsMetaModel(&$overrideInfo)
	{
		$defaultsMetaModel = new SproutSeo_MetaModel();

		if (isset($overrideInfo['default']))
		{
			// Build defaultsMetaModel from settings in template
			$defaultsMetaModel = sproutSeo()->defaults->getDefaultByHandle($overrideInfo['default']);
			$defaultsMetaModel->canonical = SproutSeoMetaHelper::prepareCanonical($defaultsMetaModel);

			SproutSeoMetaHelper::prepareAssetUrls($defaultsMetaModel);
		}

		return $defaultsMetaModel;
	}

	/**
	 * Create a SproutSeo_MetaModel based on an override element ID
	 *
	 * @param $overrideInfo
	 * @return SproutSeo_MetaModel
	 */
	private function _getEntryOverridesMetaModel(&$overrideInfo)
	{
		$entryOverridesMetaModel = new SproutSeo_MetaModel();

		if (isset($overrideInfo['id']))
		{
			// @todo - revisit when adding internationalization
			$locale = (defined('CRAFT_LOCALE') ? CRAFT_LOCALE : craft()->locale->getId());
			$entryOverride = sproutSeo()->overrides->getOverrideByEntryId($overrideInfo['id'], $locale);
			$overrideAttributes = $entryOverride->getAttributes();

			$entryOverridesMetaModel->setAttributes($overrideAttributes);

			SproutSeoMetaHelper::prepareAssetUrls($entryOverridesMetaModel);
		}

		return $entryOverridesMetaModel;
	}

	/**
	 * Process any overrides provided in via the templates and create a SproutSeo_MetaModel
	 *
	 * @param $overrideInfo
	 * @return SproutSeo_MetaModel
	 */
	private function _getCodeOverridesMetaModel($overrideInfo)
	{
		$codeOverrideMetaModel = new SproutSeo_MetaModel();

		if (!empty($overrideInfo))
		{
			$codeOverrideMetaModel->setAttributes($overrideInfo);

			SproutSeoMetaHelper::prepareAssetUrls($codeOverrideMetaModel);
		}

		return $codeOverrideMetaModel;
	}
}
