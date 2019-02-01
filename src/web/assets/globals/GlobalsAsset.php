<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\globals;

use barrelstrength\sproutbase\app\fields\web\assets\address\AddressFieldAsset;
use barrelstrength\sproutseo\web\assets\base\BaseAsset;
use barrelstrength\sproutseo\web\assets\tageditor\TagEditorAsset;
use craft\web\AssetBundle;
use craft\web\assets\datepickeri18n\DatepickerI18nAsset;
use craft\web\assets\timepicker\TimepickerAsset;
use yii\web\JqueryAsset;
use barrelstrength\sproutbase\app\fields\web\assets\selectother\SelectOtherFieldAsset;

class GlobalsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/globals/dist';

        $this->depends = [
            JqueryAsset::class,
            DatepickerI18nAsset::class,
            TimepickerAsset::class,
            BaseAsset::class,
            AddressFieldAsset::class,
            TagEditorAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->js = [
            'js/websiteidentity.js'
        ];

        parent::init();
    }
}