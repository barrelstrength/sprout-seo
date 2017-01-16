<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000004_sproutSeo_addElementMetadataTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_overrides';
		$newTableName = 'sproutseo_metadata_elements';

		$varchar = array(
			'column'   => ColumnType::Varchar,
			'required' => false,
			'default'  => null,
		);

		$columns = array(
			'customizationSettings' => $varchar,
			'schemaOverrideTypeId'  => $varchar,
			'schemaTypeId'          => $varchar,
			'optimizedKeywords'     => $varchar,
			'optimizedImage'        => $varchar,
			'optimizedDescription'  => $varchar,
			'optimizedTitle'        => $varchar
		);

		$columnsToRename = array(
			'entryId' => 'elementId'
		);

		foreach ($columns as $columnName => $type)
		{
			if (!craft()->db->columnExists($tableName, $columnName))
			{
				$this->addColumnAfter($tableName, $columnName, $type, 'locale');

				SproutSeoPlugin::log("Created column `$columnName` in `$newTableName` .", LogLevel::Info, true);
			}
		}

		MigrationHelper::dropIndexIfExists($tableName, array('entryId', 'locale'), true);

		foreach ($columnsToRename as $columnName => $newColumn)
		{
			if (craft()->db->columnExists($tableName, $columnName))
			{
				$this->renameColumn($tableName, $columnName, $newColumn);
			}
		}

		$columnsToMove = array(
			'ogTitle' => array(
				'type' => $varchar,
				'after' => 'ogUrl'
			),
			'ogSiteName' => array(
				'type' => $varchar,
				'after' => 'ogUrl'
			),
			'ogDescription' => array(
				'type' => $varchar,
				'after' => 'ogTitle'
			),
			'twitterUrl' => array(
				'type' => $varchar,
				'after' => 'twitterCard'
			),
			'twitterDescription' => array(
				'type' => $varchar,
				'after' => 'twitterTitle'
			),
			'twitterImage' => array(
				'type' => $varchar,
				'after' => 'twitterDescription'
			),
		);

		foreach ($columnsToMove as $columnToRename => $info)
		{
			$this->alterColumn($tableName, $columnToRename, $info['type'], $columnToRename, $info['after']);
		}

		$this->addColumnAfter($tableName, 'ogTransform', $varchar, 'ogImage');
		SproutSeoPlugin::log("Created column ogTransform in `$tableName` .", LogLevel::Info, true);

		$this->addColumnAfter($tableName, 'twitterTransform', $varchar, 'twitterImage');
		SproutSeoPlugin::log("Created column twitterTransform in `$tableName` .", LogLevel::Info, true);

		// Removes publisher and author columns
		$this->dropColumn($tableName, 'publisher');
		$this->dropColumn($tableName, 'author');

		/* Removes old field types */
		$oldFieldTypes = array(
			'SproutSeo_BasicMeta',
			'SproutSeo_GeographicMeta',
			'SproutSeo_OpenGraph',
			'SproutSeo_RobotsMeta',
			'SproutSeo_TwitterCard'
		);

		$oldFields = craft()->db->createCommand()
			->select('*')
			->from('fields')
			->where(array('in', 'type', $oldFieldTypes))
			->queryAll();

		if ($oldFields)
		{
			$idsToRemove  = array();
			$defaultGroup = 1;

			foreach ($oldFields as $field)
			{
				array_push($idsToRemove, $field['id']);
				$defaultGroup = $field['groupId'];
			}

			// let's create the a new element metadata field to replace old fields.
			$fieldData['groupId']      = $defaultGroup;
			$fieldData['name']         = Craft::t('Element Metadata');
			$fieldData['handle']       = 'sproutSeoElementMetadata';
			$fieldData['translatable'] = 0;
			$fieldData['type']         = 'SproutSeo_ElementMetadata';
			// Default settings let's set to manually
			$fieldData['settings']     = '{"optimizedTitleField":"manually","optimizedDescriptionField":"manually","optimizedImageField":"manually","optimizedKeywordsField":"manually","showMainEntity":"1","showSearchMeta":"1","showOpenGraph":"1","showTwitter":"1","showGeo":"1","showRobots":"1","displayPreview":"1","requiredTitle":"1","requiredDescription":"1","requiredImage":""}';

			craft()->db->createCommand()->insert('fields', $fieldData);
			$elementFieldId = craft()->db->getLastInsertID();

			$oldFieldlayoutFields = craft()->db->createCommand()
				->select('*')
				->from('fieldlayoutfields')
				->where(array('in', 'fieldId', $idsToRemove))
				->queryAll();

			if ($elementFieldId)
			{
				$layoutId = null;
				foreach ($oldFieldlayoutFields as $key => $fieldlayoutField)
				{
					$data = array('fieldId' => $elementFieldId);

					if ($key == 0)
					{
						$layoutId = $this->_updateFieldLayout($data, $fieldlayoutField);
					}
					else if ($layoutId != $fieldlayoutField['layoutId'])
					{
						$layoutId = $this->_updateFieldLayout($data, $fieldlayoutField);
					}
				}
			}

			// finally delete old sprout seo fields
			craft()->db->createCommand()->delete('fields', array('in', 'id', $idsToRemove));
		}
		// finally rename table
		$this->renameTable($tableName, $newTableName);
		$this->createIndex($newTableName, 'elementId,locale', true);

		return true;
	}

	private function _updateFieldLayout($data, $fieldlayoutField)
	{
		craft()->db->createCommand()->update('fieldlayoutfields', $data, 'id = :id', array(':id' => $fieldlayoutField['id']));

		$layoutId = $fieldlayoutField['layoutId'];

		return $layoutId;
	}
}