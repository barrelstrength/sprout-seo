<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use barrelstrength\sproutseo\SproutSeo;
use craft\base\Model;
use craft\helpers\UrlHelper;
use Craft;

/**
 * Class SitemapSection
 *
 * This class is used to manage the ajax updates of the sitemap settings on the
 * sitemap tab. The attributes are a subset of the Metadata
 */
class SitemapSection extends Model
{
    public $id;
    public $siteId;

    public $urlEnabledSectionId;
    public $type;
    public $uri;
    public $changeFrequency;
    public $priority;
    public $enabled;

    // Attributes assigned from URL-Enabled Section integration
    public $name;
    public $handle;

// @todo - do we need the following attributes or can we update things and remove them?

    /**
     * @var
     */
    public $isNew;

    /**
     * @var
     */
    public $dateCreated;

    /**
     * @var
     */
    public $dateUpdated;

    /**
     * @var
     */
    public $uid;

    /**
     * @return \craft\models\Site|null
     */
    public function getSite()
    {
        return Craft::$app->sites->getSiteById($this->siteId);
    }

    public function getUrlEnabledSection()
    {
        $urlEnabledSectionType = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypeByType($this->type);
        $urlEnabledSections = $urlEnabledSectionType->urlEnabledSections;

        foreach ($urlEnabledSections as $key => $urlEnabledSection) {
            if ($key === $this->type.'-'.$this->urlEnabledSectionId) {
                return $urlEnabledSection;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uri'], 'sectionUri', 'on' => 'customSection'],
            [['uri'], 'required', 'on' => 'customSection', 'message' => 'Uri cannot be blank.'],
        ];
    }

    /**
     * Check is the url saved on custom sections are URI's
     * This is the 'sectionUri' validator as declared in rules().
     *
     * @param $attribute
     * @param $params
     */
    public function sectionUri($attribute, $params)
    {
        if (UrlHelper::isAbsoluteUrl($this->$attribute)) {
            $this->addError($attribute, Craft::t('sprout-seo', 'Invalid URI'));
        }
    }
}
