<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;

use craft\elements\Entry as EntryElement;
use craft\models\Section;
use craft\queue\jobs\ResaveElements;
use Craft;

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
    public function getIdVariableName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getIdColumnName()
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
    public function getAllUrlEnabledSections()
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
       return null;
    }
}
