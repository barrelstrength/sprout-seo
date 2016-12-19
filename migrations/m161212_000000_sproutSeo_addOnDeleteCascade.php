<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161212_000000_sproutSeo_addOnDeleteCascade extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_metadata_elements';
		// Find all current elements and check if exists
		$elements = craft()->db->createCommand()
			->select('elementId')
			->from($tableName)
			->where('elementId is not null')
			->queryAll();

		$elementsToDelete = array();

		foreach ($elements as $element)
		{
			$craftElement = craft()->db->createCommand()
				->select('id')
				->from('elements')
				->where('id =:elementId', array(':elementId' => $element['elementId']))
				->queryRow();

			if (!$craftElement)
			{
				array_push($elementsToDelete,  $element['elementId']);
			}
		}

		if (count($elementsToDelete))
		{
			craft()->db->createCommand()->delete($tableName, array('in', 'elementId', $elementsToDelete));
		}

		MigrationHelper::refresh();

		MigrationHelper::dropForeignKeyIfExists($tableName, array('elementId'));

		$this->addForeignKey($tableName, 'elementId', 'elements', 'id', 'CASCADE');

		return true;
	}
}
