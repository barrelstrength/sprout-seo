<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;


class NoSection extends UrlEnabledSectionType
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'No Section';
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return 'none';
    }

    /**
     * @inheritdoc
     */
    public function getElementIdColumnName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayoutSettingsObject($id)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getElementTableName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getElementType()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getMatchedElementVariable()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAllUrlEnabledSections($siteId)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getTableName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null)
    {
        return true;
    }
}
