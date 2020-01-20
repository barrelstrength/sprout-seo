<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\seo;

use barrelstrength\sproutbasefields\web\assets\selectother\SelectOtherFieldAsset;
use barrelstrength\sproutseo\web\assets\tageditor\TagEditorAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\datepickeri18n\DatepickerI18nAsset;
use craft\web\assets\timepicker\TimepickerAsset;
use yii\web\JqueryAsset;

class SproutSeoAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/seo/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
            DatepickerI18nAsset::class,
            TimepickerAsset::class,
            TagEditorAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->js = [
            'js/sproutseo.js'
        ];

        $this->css = [
            'css/sproutseo.css'
        ];

        parent::init();
    }
}