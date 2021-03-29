<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * m180627_000000_craft2_to_craft3 migration.
 */
class m180627_000000_craft2_to_craft3 extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Get the settings using Project Config if upgrading to
        // a Craft version after it has been added
        // https://github.com/craftcms/cms/blob/develop/CHANGELOG.md#310---2019-01-15
        if (version_compare(Craft::$app->getInfo()->version, '3.1.0', '>=')) {
            $projectConfig = Craft::$app->getProjectConfig();
            $settings = $projectConfig->get('plugins.sprout-seo.settings');
        } else {
            /** @var SproutSeo $plugin */
            $plugin = SproutSeo::getInstance();
            $pluginSettings = $plugin->getSettings();
            $settings = $pluginSettings->getAttributes();
        }

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
            $settings['enableMultilingualSitemaps'] = '';
            foreach ($sites as $site) {
                $sitesArray[$site->id] = $site->id;
            }
            $settings['siteSettings'] = $sitesArray;
        }

        $pluginHandle = 'sprout-seo';
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $settings);

        $globals = (new Query())
            ->select(['id', 'meta', 'identity'])
            ->from(['{{%sproutseo_globals}}'])
            ->one();

        if ($globals) {
            if (isset($globals['meta'])) {
                $meta = json_decode($globals['meta'], true);
                if ($meta) {
                    $metaAsJson = $this->getMetadataAsJson($meta);
                    $this->update('{{%sproutseo_globals}}', ['meta' => $metaAsJson], ['id' => $globals['id']], [], false);
                }
            }

            if (isset($globals['identity'])) {
                $identity = json_decode($globals['identity'], true);

                if ($identity) {
                    $identityAsJson = $this->getIdentityAsJson($identity);
                    $this->update('{{%sproutseo_globals}}', ['identity' => $identityAsJson], ['id' => $globals['id']], [], false);
                }
            }
        }

        // Small change in Redirects
        $this->alterColumn('{{%sproutseo_redirects}}', 'newUrl', $this->string());

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180627_000000_craft2_to_craft3 cannot be reverted.\n";

        return false;
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
}
