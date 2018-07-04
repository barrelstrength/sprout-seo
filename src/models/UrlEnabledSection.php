<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use craft\base\Element;
use craft\base\Model;
use craft\db\Query;
use barrelstrength\sproutseo\fields\ElementMetadata;
use Craft;

/**
 * Class UrlEnabledSection
 */
class UrlEnabledSection extends Model
{
    /**
     * URL-Enabled Section ID
     *
     * @var
     */
    public $id;

    /**
     * @var UrlEnabledSectionType $type
     */
    public $type;

    /**
     * @var SitemapSection $sitemapSection
     */
    public $sitemapSection;

    /**
     * The current locales URL Format for this URL-Enabled Section
     *
     * @var string
     */
    public $uriFormat;

    /**
     * The Element Model for the Matched Element Variable of the current page load
     *
     * @var Element
     */
    public $element;

    /**
     * Name of the Url Enabled Element Group
     *
     * @var string
     */
    public $name;

    /**
     * Handle of the Url Enabled Element Group
     *
     * @var string
     */
    public $handle;

    /**
     * Get the URL format from the element via the Element Group integration
     *
     * @return false|null|string
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlFormat()
    {
        $primarySite = Craft::$app->getSites()->getPrimarySite();

        $urlEnabledSectionUrlFormatTableName = $this->type->getTableName();
        $urlEnabledSectionUrlFormatColumnName = $this->type->getUrlFormatColumnName();
        $urlEnabledSectionIdColumnName = $this->type->getUrlFormatIdColumnName();

        $query = (new Query())
            ->select($urlEnabledSectionUrlFormatColumnName)
            ->from(["{{%$urlEnabledSectionUrlFormatTableName}}"])
            ->where([$urlEnabledSectionIdColumnName => $this->id]);

        if ($this->type->isLocalized()) {
            $query->andWhere(['siteId' => $primarySite->id]);
        }

        if ($query->scalar()) {
            $this->uriFormat = $query->scalar();
        }

        return $this->uriFormat;
    }

    /**
     * @param bool $matchAll
     *
     * @return bool
     */
    public function hasElementMetadataField($matchAll = true)
    {
        $fieldLayoutObjects = $this->type->getFieldLayoutSettingsObject($this->id);

        if (!$fieldLayoutObjects) {
            return false;
        }

        // Make what we get back into an array
        if (!is_array($fieldLayoutObjects)) {
            $fieldLayoutObjects = [$fieldLayoutObjects];
        }

        $totalFieldLayouts = count($fieldLayoutObjects);
        $totalElementMetaFields = 0;

        // We want to make sure there is an Element Metadata field on every field layout object.
        // For example, a Category Group or Product Type just needs one Element Metadata for its Field Layout.
        // A section with multiple Entry Types needs an Element Metadata field on each of it's Field Layouts.
        foreach ($fieldLayoutObjects as $fieldLayoutObject) {
            $fields = $fieldLayoutObject->getFieldLayout()->getFields();

            /** @noinspection ForeachSourceInspection */
            foreach ($fields as $field) {
                if (get_class($field) == ElementMetadata::class) {
                    $totalElementMetaFields++;
                }
            }
        }

        if ($matchAll) {
            // If we have an equal number of Element Metadata fields,
            // the setup is optimized to handle metadata at each level
            // We use this to indicate to the user if everything is setup
            if ($totalElementMetaFields >= $totalFieldLayouts) {
                return true;
            }
        } else {
            // When we're resaving our elements, we don't care if everything is
            // setup, we just need to know if any Element Metadata Fields exist
            // and need updating.
            if ($totalElementMetaFields > 0) {
                return true;
            }
        }

        return false;
    }
}
