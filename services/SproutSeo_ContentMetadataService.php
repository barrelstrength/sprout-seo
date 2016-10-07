<?php
namespace Craft;

/**
 * Class SproutSeo_ContentMetadataService
 *
 * @package Craft
 */
class SproutSeo_ContentMetadataService extends BaseApplicationComponent
{
	/**
	 * Get an Content Metadata by Element ID
	 *
	 * @param $elementId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getContentMetadataByElementId($elementId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_content')
			->where('elementId = :elementId', array(':elementId' => $elementId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetadataModel::populateModel($query);

		return $model;
	}

	/**
	 * Create an Content Metadata record
	 *
	 * @param $attributes
	 */
	public function createContentMetadata($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metadata_content', $attributes);
	}

	/**
	 * Update am Content Metadata record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateContentMetadata($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metadata_content',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * Delete an Content Metadata record
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteContentMetadataById($id = null)
	{
		$record = new SproutSeo_ContentMetadataRecord();

		return $record->deleteByPk($id);
	}
}
