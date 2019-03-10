<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\migrations;

use Craft;

use craft\db\Migration;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutbasefields\migrations\Install as SproutBaseFieldsInstall;
use craft\services\Plugins;
use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use barrelstrength\sproutbasesitemaps\migrations\Install as SproutBaseSitemapsInstall;

class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

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
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultSettings();
        $this->insertDefaultGlobalMetadata();
        $this->createAddressTable();

        return true;
    }

    /**
     * @return bool|void
     * @throws \Throwable
     */
    public function safeDown()
    {
        $this->dropTable('{{%sproutseo_globals}}');
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $this->createTable('{{%sproutseo_globals}}', [
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
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function insertDefaultSettings()
    {
        $settings = new Settings();
        // default site id for sections
        $site = Craft::$app->getSites()->getPrimarySite();
        $settings->siteSettings[$site->id] = $site->id;

        // Add our default plugin settings
        $pluginHandle = 'sprout-seo';
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY . '.' . $pluginHandle . '.settings', $settings->toArray());
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
