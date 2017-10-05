<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170811_000000_sproutSeo_addCountColumnToRedirectsTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName  = 'sproutseo_redirects';
		$columnName = 'count';

		if (!craft()->db->columnExists($tableName, $columnName))
		{

			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Int,
					'required' => true,
					'default'  => 0,
				),
				'regex'
			);

			SproutSeoPlugin::log("Created the column `$columnName` in `$tableName` .", LogLevel::Info, true);

			// Update settings
			$row = craft()->db->createCommand()->select('settings')
				->from('plugins')
				->where('class=:class', array(':class' => 'SproutSeo'))
				->queryRow();

			$settings = $row['settings'];

			$settings = json_decode($settings, true);

			$settings['enable404RedirectLog'] = 0;

			$settingsJson = JsonHelper::encode($settings);

			craft()->db->createCommand()->update('plugins', array(
				'settings' => $settingsJson
			),
				'class=:class', array(':class' => 'SproutSeo')
			);

			SproutSeoPlugin::log("Added enable404RedirectLog setting", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$columnName` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}
