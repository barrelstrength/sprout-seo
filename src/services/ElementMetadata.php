<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use craft\models\FieldLayout;
use yii\base\Component;
use craft\db\Query;

use barrelstrength\sproutseo\fields\ElementMetadata as ElementMetadataField;
use barrelstrength\sproutseo\SproutSeo;

use yii\base\Event;

class ElementMetadata extends Component
{
    /**
     * @todo - TEST CRAFT3
     * Re-save Elements after a field layout or Element Metadata field is updated
     *
     * This is necessary when an Element Metadata field is added to a Field Layout
     * in a Section that Elements already exist, or if any changes are made to the
     * Element Metadata field type.
     *
     * @param Event $event
     *
     * @throws \craft\errors\SiteNotFoundException
     */
    public function resaveElements(Event $event)
    {
        /**
         * The Field Layout event identifies the Element Type that the layout is for:
         * Category, Entry, Commerce_Product, etc.
         *
         * @var FieldLayout $fieldLayout
         */
        $fieldLayout = $event->params['layout'];

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
            $urlEnabledSectionType = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypeByElementType($elementType);

            // We only need to save the current field layout. Some Elements, like Commerce_Products
            // also need to save the related Variant field layout which returns as an array
            if (!is_array($urlEnabledSectionType) && $urlEnabledSectionType->resaveElementsAfterFieldLayoutSaved()) {
                $urlEnabledSectionType->resaveElements();
            }
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
            ->select('layoutId')
            ->from(['{{%fieldlayoutfields}}'])
            ->where(['fieldId' => $fieldId])
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
            //Get the URL-Enabled Section Type based using the Element Type
            $urlEnabledSectionType = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypeByElementType($elementType);

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
        }
    }
}
