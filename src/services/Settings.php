<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use yii\base\Component;

use Craft;

class Settings extends Component
{
    public function getDescriptionLength()
    {
        /**
         * @var Settings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $descriptionLength = $pluginSettings->maxMetaDescriptionLength;
        $descriptionLength = $descriptionLength > 160 ? $descriptionLength : 160;

        return $descriptionLength;
    }
}
