<?php
namespace Craft;

/**
 * Class SproutSeo_EntryMetadataService
 *
 * @package Craft
 */
class SproutSeo_EntryMetadataService extends BaseApplicationComponent
{
	/**
	 * Get an Entry Metadata by Element ID
	 *
	 * @param $elementId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getEntryMetadataByElementId($elementId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_entries')
			->where('elementId = :elementId', array(':elementId' => $elementId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetadataModel::populateModel($query);

		return $model;
	}

	/**
	 * Create an Entry Metadata record
	 *
	 * @param $attributes
	 */
	public function createEntryMetadata($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metadata_entries', $attributes);
	}

	/**
	 * Update am Entry Metadata record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateEntryMetadata($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metadata_entries',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * Delete an Entry Metadata record
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteEntryMetadataById($id = null)
	{
		$record = new SproutSeo_EntryMetadataRecord();

		return $record->deleteByPk($id);
	}
}
