<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbase\app\metadata\records\GlobalMetadata as GlobalMetadataRecord;
use barrelstrength\sproutbase\app\metadata\SproutSeo;
use barrelstrength\sproutbase\config\base\DependencyInterface;
use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\SproutBase;
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
     * @return bool
     * @throws Throwable
     */
    public function safeDown(): bool
    {
        SproutBase::$app->config->runUninstallMigrations(SproutSeo::getInstance());

        // Delete Global Metadata Table
        $this->dropTableIfExists(GlobalMetadataRecord::tableName());

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

        SproutBase::$app->config->runInstallMigrations(SproutSeo::getInstance());
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
