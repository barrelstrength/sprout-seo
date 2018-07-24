<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

use Craft;

/**
 * m180627_000000_craft2_to_craft3 migration.
 */
class m180627_000000_craft2_to_craft3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $plugin = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%plugins}}'])
            ->where(['handle' => 'sprout-seo'])
            ->one();

        $settings = json_decode($plugin['settings'], true);

        if (isset($settings['toggleLocaleOverride']) && $settings['toggleLocaleOverride']) {
            $groups = Craft::$app->getSites()->getAllGroups();
            $groupsArray = [];
            $settings['enableMultilingualSitemaps'] = '1';
            foreach ($groups as $group) {
                $groupsArray[$group->id] = $group->id;
            }
            $settings['groupSettings'] = $groupsArray;
        } else {
            $sites = Craft::$app->getSites()->getAllSites();
            $sitesArray = [];
            $settings['enableMultilingualSitemaps'] = "";
            foreach ($sites as $site) {
                $sitesArray[$site->id] = $site->id;
            }
            $settings['siteSettings'] = $sitesArray;
        }

        $this->update('{{%plugins}}', ['settings' => json_encode($settings)], ['id' => $plugin['id']], [], false);

        $globals = (new Query())
            ->select(['id', 'meta'])
            ->from(['{{%sproutseo_globals}}'])
            ->one();

        if ($globals){
            if (isset($globals['meta'])){
                $meta = json_decode($globals['meta'], true);
                if ($meta){
                    $metaAsJson = $this->getMetadataAsJson($meta);
                    $this->update('{{%sproutseo_globals}}', ['meta' => $metaAsJson], ['id' => $globals['id']], [], false);
                }
            }
        }

        // Small change in Redirects
        $this->alterColumn('{{%sproutseo_redirects}}', 'newUrl', $this->string());

        return true;
    }

    private function getMetadataAsJson($meta)
    {
        unset(
            $meta['ogAudio'],
            $meta['ogVideo'],
            $meta['twitterPlayer'],
            $meta['twitterPlayerStream'],
            $meta['twitterPlayerStreamContentType'],
            $meta['twitterPlayerWidth'],
            $meta['twitterPlayerHeight'],
            $meta['dateCreated'],
            $meta['dateUpdated'],
            $meta['uid']
        );

        return json_encode($meta);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180627_000000_craft2_to_craft3 cannot be reverted.\n";
        return false;
    }
}