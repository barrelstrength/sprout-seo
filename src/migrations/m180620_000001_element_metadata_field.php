<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\fields\ElementMetadata;
use craft\db\Migration;
use craft\db\Query;
use Craft;

/**
 * m180620_000001_element_metadata_field migration.
 */
class m180620_000001_element_metadata_field extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function safeUp()
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

        foreach ($fields as $field) {
            $this->update('{{%fields}}', ['type' => ElementMetadata::class], ['id' => $field['id']], [], false);
            $fieldHandle = $field['handle'];

            $metadataElements = (new Query())
                ->select(['*'])
                ->from(['{{%sproutseo_metadata_elements}}'])
                ->all();

            foreach ($metadataElements as $metadataElement) {
                $siteId = $siteIdsByLocale[$metadataElement['locale']] ?? $primarySiteId;
                $element = Craft::$app->getElements()->getElementById($metadataElement['elementId'], null, $siteId);
                if ($element) {
                    $metadataAsJson = $this->getMetadataAsJson($metadataElement);

                    if (isset($element->{$fieldHandle})) {
                        $element->{$fieldHandle} = $metadataAsJson;
                        Craft::$app->getElements()->saveElement($element);
                    }
                }
            }
        }

        return true;
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
            $metadataElement['dateCreated'],
            $metadataElement['dateUpdated'],
            $metadataElement['uid']
        );

        return json_encode($metadataElement);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180620_000001_element_metadata_field cannot be reverted.\n";
        return false;
    }
}