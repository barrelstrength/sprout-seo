<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\SproutSeo;
use craft\db\Migration;
use craft\db\Query;

use Craft;
use craft\services\Plugins;

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
        $plugin = SproutSeo::getInstance();

        $settings = $plugin->getSettings()->getAttributes();

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

        $pluginHandle = 'sprout-seo';
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY . '.' . $pluginHandle . '.settings', $settings);

        $globals = (new Query())
            ->select(['id', 'meta', 'identity'])
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

            if (isset($globals['identity'])){
                $identity = json_decode($globals['identity'], true);

                if ($identity){
                    $identityAsJson = $this->getIdentityAsJson($identity);
                    $this->update('{{%sproutseo_globals}}', ['identity' => $identityAsJson], ['id' => $globals['id']], [], false);
                }
            }
        }

        // Small change in Redirects
        $this->alterColumn('{{%sproutseo_redirects}}', 'newUrl', $this->string());

        return true;
    }

    private function getIdentityAsJson($identity)
    {
        unset(
            $identity['url']
        );

        return json_encode($identity);
    }

    private function getMetadataAsJson($meta)
    {
        unset(
            $meta['id'],
            $meta['isNew'],
            $meta['default'],
            $meta['name'],
            $meta['handle'],
            $meta['hasUrls'],
            $meta['uri'],
            $meta['priority'],
            $meta['changeFrequency'],
            $meta['urlEnabledSectionId'],
            $meta['isCustom'],
            $meta['type'],
            $meta['enabled'],
            $meta['locale'],
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
