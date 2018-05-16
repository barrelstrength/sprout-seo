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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%sproutseo_metadata_globals}}');
        $this->dropTable('{{%sproutseo_metadata_sections}}');
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
        $this->createTable('{{%sproutseo_metadata_globals}}', [
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

        $this->createTable('{{%sproutseo_metadata_sections}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'enabledForSite' => $this->boolean()->defaultValue(false),
            'urlEnabledSectionId' => $this->integer(),
            'isCustom' => $this->boolean()->defaultValue(false),
            'enabled' => $this->boolean()->defaultValue(false),
            'type' => $this->string(),
            'name' => $this->string(),
            'handle' => $this->string(),
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
            'newUrl' => $this->string()->notNull(),
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
        $this->createIndex(null, '{{%sproutseo_metadata_globals}}', 'id, siteId', true);
        $this->createIndex(null, '{{%sproutseo_metadata_globals}}', ['siteId'], true);
        $this->createIndex(null, '{{%sproutseo_redirects}}', 'id');
        $this->createIndex(null, '{{%sproutseo_metadata_sections}}', ['siteId'], false);
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            null,
            '{{%sproutseo_redirects}}', 'id',
            '{{%elements}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(null, '{{%sproutseo_metadata_sections}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%sproutseo_metadata_globals}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
    }

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
        $settingsProperties = $settings->getAttributes();

        $affectedRows = $this->db->createCommand()->update('{{%plugins}}', [
            'settings' => json_encode($settingsProperties)
        ],
            [
                'handle' => 'sprout-seo'
            ]
        )->execute();
    }

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

    protected function createAddressTable()
    {
        $migration = new SproutBaseFieldsInstall();

        ob_start();
        $migration->up();
        ob_end_clean();
    }
}