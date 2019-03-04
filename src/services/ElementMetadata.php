<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;


use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;
use craft\base\Element;
use craft\base\Field;
use craft\events\FieldLayoutEvent;

use yii\base\Component;
use craft\db\Query;

use barrelstrength\sproutseo\fields\ElementMetadata as ElementMetadataField;
use barrelstrength\sproutseo\SproutSeo;


class ElementMetadata extends Component
{
    /**
     * Returns the metadata for an Element's Element Metadata as a Metadata model
     *
     * @param Element|null $element
     *
     * @return Metadata|null
     */
    public function getElementMetadata(Element $element = null)
    {
        $fieldHandle = $this->getElementMetadataFieldHandle($element);

        if (isset($element->{$fieldHandle})) {
            $metadata = $element->{$fieldHandle};

            // Support Live Preview (where image IDs still need to be converted from arrays)
            if (isset($metadata['ogImage'])) {
                $metadata['ogImage'] = OptimizeHelper::getImageId($metadata['ogImage']);
            }
            if (isset($metadata['twitterImage'])) {
                $metadata['twitterImage'] = OptimizeHelper::getImageId($metadata['twitterImage']);
            }

            return new Metadata($metadata);
        }

        return null;
    }

    /**
     * Returns the Field handle of the first Element Metadata field found in an Element Field Layout
     *
     * @param Element|null $element
     *
     * @return null|string
     */
    public function getElementMetadataFieldHandle(Element $element = null)
    {
        if (!$element) {
            return null;
        }

        $fields = $element->getFieldLayout()->getFields();

        /**
         * Get our ElementMetadata Field
         *
         * @var Field $field
         */
        foreach ($fields as $field) {
            if (get_class($field) == ElementMetadataField::class) {
                if (isset($element->{$field->handle})) {
                    return $field->handle;
                }
            }
        }

        return null;
    }

    /**
     * Re-save Elements after a field layout or Element Metadata field is updated
     *
     * This is necessary when an Element Metadata field is added to a Field Layout
     * in a Section that Elements already exist, or if any changes are made to the
     * Element Metadata field type.
     *
     * @param FieldLayoutEvent $event
     *
     * @throws \craft\errors\SiteNotFoundException
     */
    public function resaveElementsAfterFieldLayoutIsSaved(FieldLayoutEvent $event)
    {
        /**
         * The Field Layout event identifies the Element Type that the layout is for:
         * Category, Entry, Commerce_Product, etc.
         */
        $fieldLayout = $event->layout;
        $elementType = $fieldLayout->type;
        $fieldLayoutFields = $fieldLayout->getFields();
        $hasElementMetadataField = false;

        foreach ($fieldLayoutFields as $field) {
            if (get_class($field) === ElementMetadataField::class) {
                $hasElementMetadataField = true;
                break;
            }
        }

        if ($hasElementMetadataField) {
            // Some Elements, like Commerce_Products
            // also need to save the related Variant field layout which returns as an array
            $this->resaveElementsByUrlEnabledSection($elementType, true);
        }
    }

    /**
     * @param $fieldId
     *
     * @throws \craft\errors\SiteNotFoundException
     */
    public function resaveElementsIfUsingElementMetadataField($fieldId)
    {
        //Get all layoutIds where this field is used from craft_fieldlayoutfields.layoutId
        $fieldLayoutIds = (new Query())
            ->select('[[layoutId]]')
            ->from(['{{%fieldlayoutfields}}'])
            ->where(['[[fieldId]]' => $fieldId])
            ->all();

        $fieldLayoutIds = array_column($fieldLayoutIds, 'layoutId');

        $elementTypes = [];

        foreach ($fieldLayoutIds as $fieldLayoutId) {
            //Use that id to get the Element Type of each layout via the craft_fieldlayouts.type column
            $fieldLayout = (new Query())
                ->select('type')
                ->from(['{{%fieldlayouts}}'])
                ->where(['id' => $fieldLayoutId])
                ->one();

            $elementTypes[] = $fieldLayout['type'];
        }

        $elementTypes = array_unique($elementTypes);

        foreach ($elementTypes as $elementType) {
            $this->resaveElementsByUrlEnabledSection($elementType);
        }
    }

    /**
     * Triggers a Resave Elements job for each Url-Enabled Section with an Element Metadata field
     *
     * @param      $elementType
     * @param bool $afterFieldLayout
     *
     * @return bool
     * @throws \craft\errors\SiteNotFoundException
     */
    protected function resaveElementsByUrlEnabledSection($elementType, $afterFieldLayout = false)
    {
        //@todo - discuss with ben
        return false;
        //Get the URL-Enabled Section Type based using the Element Type
        /*
        $urlEnabledSectionType = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypeByElementType($elementType);

        if ($urlEnabledSectionType === null)
        {
            return false;
        }

        if ($afterFieldLayout && !$urlEnabledSectionType->resaveElementsAfterFieldLayoutSaved())
        {
            return false;
        }

        if ($urlEnabledSectionType) {
            foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSection) {
                if ($urlEnabledSection->hasElementMetadataField(false)) {
                    // Need to figure out where to grab sectionId, entryTypeId, categoryGroupId, etc.
                    $elementGroupId = $urlEnabledSection->id;

                    //Resave Element on that URL-Enabled Section Type
                    $urlEnabledSectionType->resaveElements($elementGroupId);
                }
            }
        }

        return true;
        */
    }
}
