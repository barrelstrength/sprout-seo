<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\fields\ElementMetadata as ElementMetadataField;
use barrelstrength\sproutseo\models\Settings as SproutSeoSettings;
use barrelstrength\sproutseo\SproutSeo;
use craft\db\Query;
use yii\base\Component;

/**
 *
 * @property string|int        $metadataFieldCount
 * @property SproutSeoSettings $settings
 * @property int               $descriptionLength
 */
class Settings extends Component
{
    /**
     * Returns plugin settings model.
     *
     * This method helps explicitly define what we're getting back so we can
     * avoid NullReferenceException warnings
     *
     * @return SproutSeoSettings
     */
    public function getSettings(): SproutSeoSettings
    {
        /** @var SproutSeo $plugin */
        $plugin = SproutSeo::getInstance();

        /** @var SproutSeoSettings $settings */
        $settings = $plugin->getSettings();

        return $settings;
    }

    public function getDescriptionLength(): int
    {
        return $this->getSettings()->maxMetaDescriptionLength ?: 160;
    }

    /**
     * @return int|string
     */
    public function getMetadataFieldCount()
    {
        $totalFields = (new Query())
            ->select(['id'])
            ->from(['{{%fields}}'])
            ->where(['type' => ElementMetadataField::class])
            ->count();

        return $totalFields;
    }
}
