<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use craft\base\Model;

/**
 * Class MetadataSitemap
 *
 * This class is used to manage the ajax updates of the sitemap settings on the
 * sitemap tab. The attributes are a subset of the Metadata
 */
class MetadataSitemap extends Model
{
    public $id;
    public $name;
    public $siteId;
    public $enabledForSite;
    public $handle;
    public $urlEnabledSectionId;
    public $type;
    public $uri;
    public $changeFrequency;
    public $priority;
    public $enabled;

}
