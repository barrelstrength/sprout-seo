<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\models\Settings as PluginSettings;
use yii\base\Component;

use Craft;

class Settings extends Component
{
    public function getDescriptionLength()
    {
        /**
         * @var PluginSettings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $descriptionLength = $pluginSettings->maxMetaDescriptionLength;
        $descriptionLength = $descriptionLength ? $descriptionLength : 160;

        return $descriptionLength;
    }
}
