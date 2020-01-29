<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbasefields\migrations\Install as SproutBaseFieldsInstall;
use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use barrelstrength\sproutbasesitemaps\migrations\Install as SproutBaseSitemapsInstall;
use Craft;
use craft\db\Migration;

class Install extends Migration
{
    /**
     * @var string The database driver to use
     */
    public $driver;

    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\SiteNotFoundException
     * @throws \craft\errors\StructureNotFoundException
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->createTables();
        $this->insertDefaultGlobalMetadata();
        $this->createAddressTable();

        return true;
    }

    protected function createTables()
    {
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $globalsTable = '{{%sproutseo_globals}}';

        if (!$this->db->tableExists($globalsTable)) {
            $this->createTable($globalsTable, [
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
    }

    protected function createIndexes()
    {
        $this->createIndex(null, '{{%sproutseo_globals}}', 'id, siteId', true);
        $this->createIndex(null, '{{%sproutseo_globals}}', ['siteId'], true);
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%sproutseo_globals}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * @throws \Throwable
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

    /**
     * @throws \Throwable
     */
    protected function createAddressTable()
    {
        $migration = new SproutBaseFieldsInstall();

        ob_start();
        $migration->up();
        ob_end_clean();
    }
}
