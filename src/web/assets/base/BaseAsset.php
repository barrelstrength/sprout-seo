<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\base;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class BaseAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/base/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/sproutseo.css'
        ];

        parent::init();
    }
}