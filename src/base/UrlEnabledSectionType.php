<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\base;

use barrelstrength\sproutseo\models\UrlEnabledSection;
use craft\base\Element;
use craft\base\Model;

/**
 * Class UrlEnabledSectionType
 */
abstract class UrlEnabledSectionType
{
    /**
     * An array of URL-Enabled Sections for this URL-Enabled Section Type
     *
     * @var UrlEnabledSection[] $urlEnabledSections
     */
    public $urlEnabledSections;

    /**
     * Get a unique ID for this URL-Enabled Section Type
     *
     * We use the element table name as the unique ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getElementTableName();
    }

    /**
     * Returns the namespace of this Url-Enabled Section Type class
     *
     * @return string
     */
    public function getType(): string
    {
        return get_class($this);
    }

    /**
     * This setting we'll help us determine if we should use the $locale to limit some queries
     * like the URL Format query.
     *
     * @return bool
     */
    public function isLocalized(): bool
    {
        return true;
    }

    /**
     * The user-friendly name of your URL-Enabled Section Type
     *
     * This name will display in the user interface.
     *
     * @return mixed
     */
    abstract public function getName(): string;

    /**
     * Allow an integration to define how to get its specific URL-Enabled Section by ID
     *
     * @param $id
     *
     * @return mixed
     */
    abstract public function getById($id);

    /**
     * Get the thing that we can call getFieldLayouts on. We will try to loop
     * through whatever we get back and call getFieldLayouts() on each item in the array.
     *
     * @param $id
     *
     * @return Model
     */
    abstract public function getFieldLayoutSettingsObject($id);

    /**
     * Return the name of the table that we get URL-Enabled Section info from. In most cases, this is the sites table.
     *
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * Get the name of the database column that stores the ID for this URL Enabled Section
     *
     * @return string
     */
    abstract public function getElementIdColumnName(): string;

    /**
     * A silly variable because Craft Commerce inconsistently names productTypeId/typeId.
     *
     * Updating this setting allows us to target different typeId column values in different
     * contexts such as when we are trying to match an element on page load and when we are
     * trying to determine the URL-format.
     *
     * @return string
     */
    abstract public function getUrlFormatIdColumnName(): string;

    /**
     * By default, we assume the uriFormat setting is in a column of the same name
     *
     * @return string
     */
    public function getUrlFormatColumnName(): string
    {
        return 'uriFormat';
    }

    /**
     * Return the name of the Element Type managed by this URL-Enabled Section Type
     *
     * @return string
     */
    abstract public function getElementType(): string;

    /**
     * Return the name of the table that element-specific data is stored
     *
     * @return string
     */
    abstract public function getElementTableName(): string;

    /**
     * Return the variable name that is used by the Element for this URL-Enabled section
     * when providing the Element data to the page with a URL.
     *
     * @example An Entry is made available to a page as `entry`.
     *          A Category is made available to a page as `category`.
     *
     * @return string
     */
    abstract public function getMatchedElementVariable(): string;

    /**
     * Returns the value to add to the database query to ensure that entries being received have a published status.
     *
     * @example An Entry requires a status of Entry::STATUS_LIVE to be published.
     *
     * @return null
     */
    public function getElementLiveStatus()
    {
        return Element::STATUS_ENABLED;
    }

    /**
     * Return all the URL-Enabled Sections for this URL-Enabled Section Type
     *
     * @param $siteId
     *
     * @return UrlEnabledSection[]
     */
    abstract public function getAllUrlEnabledSections($siteId): array;

    /**
     * Disable support for resaving elements when a field layout for this
     * URL-Enabled Section is saved. Some Elements already do this by default
     * and you may want to set this to false if they do.
     *
     * @return bool
     */
    public function resaveElementsAfterFieldLayoutSaved(): bool
    {
        return true;
    }

    /**
     * Add support for triggering the ResaveElements task for this URL-Enabled Section
     *
     * @param null $elementGroupId
     *
     * @return bool
     */
    abstract public function resaveElements($elementGroupId = null): bool;
}
