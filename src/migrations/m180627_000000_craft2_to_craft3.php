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

        if (isset($settings['toggleLocaleOverride']) && $settings['toggleLocaleOverride']){
            $groups = Craft::$app->getSites()->getAllGroups();
            $groupsArray = [];
            $settings['enableMultilingualSitemaps'] = "1";
            foreach ($groups as $group) {
                $groupsArray[$group->id] = $group->id;
            }
            $settings['groupSettings'] = $groupsArray;
        }else{
            $sites = Craft::$app->getSites()->getAllSites();
            $sitesArray = [];
            $settings['enableMultilingualSitemaps'] = "";
            foreach ($sites as $site) {
                $sitesArray[$site->id] = $site->id;
            }
            $settings['siteSettings'] = $sitesArray;
        }

        $this->update('{{%plugins}}', ['settings' => json_encode($settings)], ['id' => $plugin['id']], [], false);

        return true;
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