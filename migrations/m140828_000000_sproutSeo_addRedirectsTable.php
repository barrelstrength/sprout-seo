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
		if (($table = $this->dbConnection->schema->getTable($tableName)))
		{
			$this->createTable($tableName, array(
					'id' => 'pk',
					'oldUrl' => 'string',
					'nextUrl' => 'string',
					'method' => 'integer',
					'regex' => 'boolean',
					'dateCreated' => 'datetime',
					'dateUpdated' => 'datetime',
					'uid' => 'string',
				)
			);

		}
		else
		{
			SproutSeoPlugin::log("The {$tableName} table already exists", LogLevel::Info, true);
		}

		return true;
	}
}
