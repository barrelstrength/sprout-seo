<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\records;


use craft\db\ActiveRecord;


/**
 * SproutSeo - RedirectLog
 */
class RedirectLog extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%sproutseo_redirects_log}}';
    }
}
