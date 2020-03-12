<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\fields\ElementMetadata;
use Craft;
use craft\base\Element;
use craft\db\Migration;
use craft\db\Query;
use Throwable;

/**
 * m180620_000001_element_metadata_field migration.
 */
class m180620_000001_element_metadata_field extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutSeo_ElementMetadata'])
            ->all();

        $primarySite = (new Query())
            ->select(['id'])
            ->from(['{{%sites}}'])
            ->where(['primary' => 1])
            ->one();

        $primarySiteId = $primarySite['id'];

        $sites = (new Query())
            ->select(['id', 'language'])
            ->from(['{{%sites}}'])
            ->all();

        $siteIdsByLocale = [];

        foreach ($sites as $site) {
            $siteIdsByLocale[$site['language']] = $site['id'];
        }

        $metadataElements = [];
        if ($this->db->tableExists('{{%sproutseo_metadata_elements}}')) {
            $metadataElements = (new Query())
                ->select(['*'])
                ->from(['{{%sproutseo_metadata_elements}}'])
                ->all();
        }

        foreach ($fields as $field) {
            $settings = json_decode($field['settings'], true);
            unset($settings['displayPreview']);
            $settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', ['type' => ElementMetadata::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
            $fieldHandle = $field['handle'];

            foreach ($metadataElements as $metadataElement) {
                $siteId = $siteIdsByLocale[$metadataElement['locale']] ?? $primarySiteId;
                /** @var Element $element */
                $element = Craft::$app->getElements()->getElementById($metadataElement['elementId'], null, $siteId);

                if ($element) {
                    $contentTable = $element->getContentTable();
                    $columnPrefix = $element->getFieldColumnPrefix();
                    $fieldName = $columnPrefix.$fieldHandle;

                    $metadataAsJson = $this->getMetadataAsJson($metadataElement);

                    if (isset($element->{$fieldHandle})) {
                        $this->update($contentTable, [
                            $fieldName => $metadataAsJson
                        ], [
                            'and',
                            ['elementId' => $element->id],
                            ['siteId' => $element->siteId],
                        ], [], false);
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
        echo "m180620_000001_element_metadata_field cannot be reverted.\n";

        return false;
    }

    private function getMetadataAsJson($metadataElement)
    {
        unset(
            $metadataElement['id'],
            $metadataElement['elementId'],
            $metadataElement['locale'],
            $metadataElement['ogAudio'],
            $metadataElement['ogVideo'],
            $metadataElement['twitterPlayer'],
            $metadataElement['twitterPlayerStream'],
            $metadataElement['twitterPlayerStreamContentType'],
            $metadataElement['twitterPlayerWidth'],
            $metadataElement['twitterPlayerHeight'],
            $metadataElement['displayPreview'],
            $metadataElement['dateCreated'],
            $metadataElement['dateUpdated'],
            $metadataElement['uid']
        );

        return json_encode($metadataElement);
    }
}