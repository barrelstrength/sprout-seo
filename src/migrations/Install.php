<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbasefields\migrations\Install as SproutBaseFieldsInstall;
use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use barrelstrength\sproutbasesitemaps\migrations\Install as SproutBaseSitemapsInstall;
use barrelstrength\sproutseo\records\GlobalMetadata as GlobalMetadataRecord;
use Craft;
use craft\db\Migration;
use craft\db\Table;
use craft\errors\SiteNotFoundException;
use Throwable;

class Install extends Migration
{
    /**
     * @var string The database driver to use
     */
    public $driver;

    /**
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->insertDefaultGlobalMetadata();

        return true;
    }

    /**
     * @throws Throwable
     * @throws SiteNotFoundException
     */
    protected function createTables()
    {
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        if (!$this->db->tableExists(GlobalMetadataRecord::tableName())) {
            $this->createTable(GlobalMetadataRecord::tableName(), [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer()->notNull(),
                'meta' => $this->text(),
                'identity' => $this->text(),
                'ownership' => $this->text(),
                'contacts' => $this->text(),
                'social' => $this->text(),
                'robots' => $this->text(),
                'settings' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndexes();
            $this->addForeignKeys();
        }

        $migration = new SproutBaseRedirectsInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $migration = new SproutBaseSitemapsInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $migration = new SproutBaseFieldsInstall();
        ob_start();
        $migration->up();
        ob_end_clean();
    }

    protected function createIndexes()
    {
        $this->createIndex(null, GlobalMetadataRecord::tableName(), 'id, siteId', true);
        $this->createIndex(null, GlobalMetadataRecord::tableName(), ['siteId'], true);
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, GlobalMetadataRecord::tableName(), ['siteId'], Table::SITES, ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * @throws Throwable
     */
    protected function insertDefaultGlobalMetadata()
    {
        $siteId = Craft::$app->getSites()->currentSite->id;

        $migration = new InsertDefaultGlobalsBySite([
            'siteId' => $siteId,
        ]);

        ob_start();
        $migration->up();
        ob_end_clean();
    }
}
