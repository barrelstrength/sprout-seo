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
	 * @return array
	 * @throws \Exception
	 */
	public function getOptimizedMeta()
	{
		$entryOverrideMetaModel  = new SproutSeo_MetaModel();
		$codeOverrideMetaModel   = new SproutSeo_MetaModel();
		$defaultMetaModel        = new SproutSeo_MetaModel();
		$globalFallbackMetaModel = new SproutSeo_MetaModel();

		// Prepare a SproutSeo_MetaModel for each of our levels of priority
		$entryOverrideMetaModel  = $entryOverrideMetaModel->setMeta('entry', $this->getMeta());
		$codeOverrideMetaModel   = $codeOverrideMetaModel->setMeta('code', $this->getMeta());
		$defaultMetaModel        = $defaultMetaModel->setMeta('default', $this->getMeta());
		$globalFallbackMetaModel = $globalFallbackMetaModel->setMeta('fallback');
		
		$prioritizedMetaModel = new SproutSeo_MetaModel();

		$this->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// Default to the Current URL
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
			elseif ($defaultMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $defaultMetaModel->getAttribute($key);
			}
			elseif ($globalFallbackMetaModel->getAttribute($key))
			{
				$prioritizedMetaModel[$key] = $globalFallbackMetaModel->getAttribute($key);
			}
			else
			{
				$prioritizedMetaModel[$key] = $prioritizedMetaModel->getAttribute($key);
			}
		}

		// @todo - reorganize how this stuff works / robots need love.
		$prioritizedMetaModel->title = SproutSeoMetaHelper::prepareAppendedSiteName($prioritizedMetaModel, $defaultMetaModel, $globalFallbackMetaModel);
		$prioritizedMetaModel->robots = SproutSeoMetaHelper::prepRobotsAsString($prioritizedMetaModel->robots);

		return $prioritizedMetaModel;
	}
}
