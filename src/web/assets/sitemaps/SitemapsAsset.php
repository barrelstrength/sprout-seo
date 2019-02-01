<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\sitemaps;

use barrelstrength\sproutseo\web\assets\base\BaseAsset;
use craft\web\AssetBundle;

class SitemapsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/sitemaps/dist';

        $this->depends = [
            BaseAsset::class
        ];

        // @todo - update this file to be named better
        $this->js = [
            'js/sitemaps.js',
            'js/MetaTags.js'
        ];

        parent::init();
    }
}