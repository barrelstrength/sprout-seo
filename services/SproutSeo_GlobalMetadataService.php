<?php
namespace Craft;

/**
 * Class SproutSeo_GlobalMetadataService
 *
 * @package Craft
 */
class SproutSeo_GlobalMetadataService extends BaseApplicationComponent
{
	/**
	 * Get Global Metadata values
	 *
	 * @return BaseModel
	 */
	public function getGlobalMetadata()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_globals')
			->queryRow();

		$results['meta']      = isset($results['meta']) ? JsonHelper::decode($results['meta']) : null;
		$results['identity']  = isset($results['identity']) ? JsonHelper::decode($results['identity']) : null;
		$results['contacts']  = isset($results['contacts']) ? JsonHelper::decode($results['contacts']) : null;
		$results['ownership'] = isset($results['ownership']) ? JsonHelper::decode($results['ownership']) : null;
		$results['social']    = isset($results['social']) ? JsonHelper::decode($results['social']) : null;
		$results['robots']    = isset($results['robots']) ? JsonHelper::decode($results['robots']) : null;
		$results['settings']  = isset($results['settings']) ? JsonHelper::decode($results['settings']) : null;

		if (isset($results['identity']['url']))
		{
			$results['identity']['url'] = SproutSeoOptimizeHelper::getGlobalMetadataSiteUrl($results['identity']['url']);
		}

		if (isset($results['settings']['ogTransform']))
		{
			$results['meta']['ogTransform'] = $results['settings']['ogTransform'];
		}

		if (isset($results['settings']['twitterTransform']))
		{
			$results['meta']['twitterTransform'] = $results['settings']['twitterTransform'];
		}

		$settings = craft()->plugins->getPlugin('sproutseo')->getSettings();
		$locale   = (defined('CRAFT_LOCALE') ? CRAFT_LOCALE : craft()->locale->getId());

		if ($settings->localeIdOverride)
		{
			$locale = $settings->localeIdOverride;
		}

		$results['meta']['ogLocale'] = $locale;

		$results['identity']['name'] = craft()->templates->renderObjectTemplate($results['identity']['name'], []);

		$results['identity']['description'] = craft()->templates->renderObjectTemplate($results['identity']['description'], []);

		$schema = SproutSeo_GlobalsModel::populateModel($results);

		return $schema;
	}

	/**
	 * Save Global Metadata to database
	 *
	 * @param $globalKeys
	 * @param $globals
	 *
	 * @return bool
	 */
	public function saveGlobalMetadata($globalKeys, $globals)
	{
		if (!is_array($globalKeys))
		{
			array($globalKeys);
		}

		foreach ($globalKeys as $globalKey)
		{
			$values = array(
				$globalKey => $globals->getGlobalByKey($globalKey, 'json')
			);

			$result = craft()->db->createCommand()->update('sproutseo_metadata_globals',
				$values,
				'id=:id',
				array(':id' => 1)
			);
		};

		return true;
	}

	/**
	 * @return int
	 */
	public function installDefaultGlobalMetadata()
	{
		$tableName = "sproutseo_metadata_globals";

		$locale = craft()->i18n->getLocaleById(craft()->language);

		$defaultSettings = '{
			"seoDivider":"-",
			"defaultOgType":"",
			"ogTransform":"sproutSeo-socialSquare",
			"twitterTransform":"sproutSeo-socialSquare",
			"defaultTwitterCard":"summary",
			"appendTitleValueOnHomepage":"",
			"appendTitleValue": ""}
		';

		$result = craft()->db->createCommand()->insert($tableName, array(
			'locale'    => $locale,
			'identity'  => null,
			'ownership' => null,
			'contacts'  => null,
			'social'    => null,
			'robots'    => null,
			'settings'  => $defaultSettings
		));

		return $result;
	}
}
