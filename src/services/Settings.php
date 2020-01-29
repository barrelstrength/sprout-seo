<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Settings as PluginSettings;
use Craft;
use craft\db\Query;
use yii\base\Component;

/**
 *
 * @property string|int $metadataFieldCount
 * @property int        $descriptionLength
 */
class Settings extends Component
{
    public function getDescriptionLength(): int
    {
        /**
         * @var PluginSettings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $descriptionLength = $pluginSettings->maxMetaDescriptionLength;
        $descriptionLength = $descriptionLength ?: 160;

        return $descriptionLength;
    }

    /**
     * @return int|string
     */
    public function getMetadataFieldCount()
    {
        $totalFields = (new Query())
            ->select(['id'])
            ->from(['{{%fields}}'])
            ->where(['type' => ElementMetadata::class])
            ->count();

        return $totalFields;
    }
}
