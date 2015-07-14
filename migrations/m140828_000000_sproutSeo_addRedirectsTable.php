<?php
namespace Craft;

class m140828_000000_sproutSeo_addRedirectsTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_redirects';
		if (!craft()->db->tableExists($tableName))
		{
			$this->createTable($tableName, array(
					'id' => 'pk',
					'oldUrl' => 'string NOT NULL',
					'newUrl' => 'string NOT NULL',
					'method' => 'integer NOT NULL',
					'regex' => 'boolean NOT NULL DEFAULT 0',
					'dateCreated' => 'datetime DEFAULT NULL',
					'dateUpdated' => 'datetime DEFAULT NULL',
					'uid' => 'string NOT NULL DEFAULT 0',
				),'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
			);
		}
		else
		{
			SproutSeoPlugin::log("The {$tableName} table already exists", LogLevel::Info, true);
		}

		return true;
	}
}
