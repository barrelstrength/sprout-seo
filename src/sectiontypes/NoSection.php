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
    public function getName(): string
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
    public function getElementIdColumnName(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName(): string
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
    public function getElementTableName(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getElementType(): string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getMatchedElementVariable(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAllUrlEnabledSections($siteId): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null): bool
    {
        return true;
    }
}
