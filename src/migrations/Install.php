<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\migrations;

use Craft;

use craft\db\Migration;
use craft\models\Structure;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutbase\app\fields\migrations\Install as SproutBaseFieldsInstall;
use craft\services\Plugins;

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
        $this->dropTable('{{%sproutseo_sitemaps}}');
        $this->dropTable('{{%sproutseo_redirects}}');

        $sproutFields = Craft::$app->plugins->getPlugin('sprout-fields');

        if (!$sproutFields) {
            $migration = new SproutBaseFieldsInstall();

            ob_start();
            $migration->down();
            ob_end_clean();
        }
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

        $this->createTable('{{%sproutseo_sitemaps}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'uniqueKey' => $this->string(),
            'urlEnabledSectionId' => $this->integer(),
            'enabled' => $this->boolean()->defaultValue(false),
            'type' => $this->string(),
            'uri' => $this->string(),
            'priority' => $this->decimal(11, 1),
            'changeFrequency' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutseo_redirects}}', [
            'id' => $this->primaryKey(),
            'oldUrl' => $this->string()->notNull(),
            'newUrl' => $this->string(),
            'method' => $this->integer(),
            'regex' => $this->boolean()->defaultValue(false),
            'count' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    protected function createIndexes()
    {
        $this->createIndex(null, '{{%sproutseo_globals}}', 'id, siteId', true);
        $this->createIndex(null, '{{%sproutseo_globals}}', ['siteId'], true);
        $this->createIndex(null, '{{%sproutseo_redirects}}', 'id');
        $this->createIndex(null, '{{%sproutseo_sitemaps}}', ['siteId'], false);
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            null,
            '{{%sproutseo_redirects}}', 'id',
            '{{%elements}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(null, '{{%sproutseo_sitemaps}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%sproutseo_globals}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * @throws \craft\errors\SiteNotFoundException
     * @throws \craft\errors\StructureNotFoundException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function insertDefaultSettings()
    {
        $maxLevels = 1;
        $structure = new Structure();
        $structure->maxLevels = $maxLevels;
        $settings = new Settings();

        Craft::$app->structures->saveStructure($structure);
        $settings->structureId = $structure->id;
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
