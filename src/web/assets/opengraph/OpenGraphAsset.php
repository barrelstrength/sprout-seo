<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\opengraph;

use barrelstrength\sproutseo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class OpenGraphAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/opengraph/dist';

        $this->depends = [
            BaseAsset::class
        ];

        $this->js = [
            'js/open-graph.js'
        ];

        parent::init();
    }
}