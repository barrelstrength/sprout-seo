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
	 * @internal param $schemaType
	 */
	public function saveGlobalMetadata($globalKeys, $globals)
	{
		// @todo - what do we do if $schemaType doesn't have a value?

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

		// @todo - add proper validation. Currently the above assumes everything is always working.
		return true;
	}

	/**
	 * @return int
	 */
	public function installDefaultGlobalMetadata()
	{
		$tableName = "sproutseo_metadata_globals";

		$locale = craft()->i18n->getLocaleById(craft()->language);

		$result = craft()->db->createCommand()->insert($tableName, array(
			'locale'    => $locale,
			'identity'  => null,
			'ownership' => null,
			'contacts'  => null,
			'social'    => null,
			'robots'    => null,
			'settings'  => null
		));

		return $result;
	}
}
