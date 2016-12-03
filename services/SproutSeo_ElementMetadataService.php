<?php
namespace Craft;

/**
 * Class SproutSeo_ElementMetadataService
 *
 * @package Craft
 */
class SproutSeo_ElementMetadataService extends BaseApplicationComponent
{
	/**
	 * Get an Element Metadata by Element ID
	 *
	 * @param $elementId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getElementMetadataByElementId($elementId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_elements')
			->where('elementId = :elementId', array(':elementId' => $elementId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetadataModel::populateModel($query);

		return $model;
	}

	/**
	 * Create an Element Metadata record
	 *
	 * @param $attributes
	 */
	public function createElementMetadata($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metadata_elements', $attributes);
	}

	/**
	 * Update am Element Metadata record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateElementMetadata($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metadata_elements',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * Delete an Element Metadata record
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteElementMetadataById($id = null)
	{
		$record = new SproutSeo_ElementMetadataRecord();

		return $record->deleteByPk($id);
	}

	/**
	 * Re-save Elements after a field layout or Element Metadata field is updated
	 *
	 * This is necessary when an Element Metadata field is added to a Field Layout
	 * in a Section that Elements already exist, or if any changes are made to the
	 * Element Metadata field type.
	 *
	 * @param Event $event
	 */
	public function resaveElements(Event $event)
	{
		// The Field Layout event identifies the Element Type that the layout is for:
		// Category, Entry, Commerce_Product, etc.
		$fieldLayout = $event->params['layout'];

		$elementGroupId = $fieldLayout->id;
		$elementType    = $fieldLayout->type;

		$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByElementType($elementType);

		// We only need to save the current field layout. Some Elements, like Commerce_Products
		// also need to save the related Variant field layout which returns as an array
		if (!is_array($urlEnabledSectionType))
		{
			$urlEnabledSectionType->resaveElements();
		}
	}
}
