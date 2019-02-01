<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\twittercard;

use barrelstrength\sproutseo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class TwitterCardAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/twittercard/dist';

        $this->depends = [
            BaseAsset::class
        ];

        $this->js = [
            'js/twitter-card.js'
        ];

        parent::init();
    }
}

