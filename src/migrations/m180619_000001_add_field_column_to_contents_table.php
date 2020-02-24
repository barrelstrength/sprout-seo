<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;


use Craft;
use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m180619_000001_add_field_column_to_contents_table migration.
 */
class m180619_000001_add_field_column_to_contents_table extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%sproutseo_metadata_elements}}')) {
            $metadataElements = (new Query())
                ->select(['*'])
                ->from(['{{%sproutseo_metadata_elements}}'])
                ->all();

            $fields = (new Query())
                ->select(['id', 'handle', 'settings'])
                ->from(['{{%fields}}'])
                ->where(['type' => 'SproutSeo_ElementMetadata'])
                ->all();

            // Let's add
            foreach ($fields as $field) {
                foreach ($metadataElements as $metadataElement) {
                    $element = Craft::$app->getElements()->getElementById($metadataElement['elementId']);
                    if ($element) {
                        $contentTable = $element->getContentTable();
                        $columnPrefix = $element->getFieldColumnPrefix();
                        $seoField = $columnPrefix.$field['handle'];
                        if (!$this->db->columnExists($contentTable, $seoField)) {
                            $this->addColumn($contentTable, $seoField, $this->text());
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180619_000001_add_field_column_to_contents_table cannot be reverted.\n";

        return false;
    }
}