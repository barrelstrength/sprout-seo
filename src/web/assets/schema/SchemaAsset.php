<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\assets\schema;

use craft\web\AssetBundle;

class SchemaAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@barrelstrength/sproutseo/web/assets/schema/dist';

        $this->js = [
            'js/schema.js'
        ];

        parent::init();
    }
}