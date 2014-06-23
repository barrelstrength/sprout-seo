<?php
namespace Craft;

class SproutSeo_TwitterCardSummaryLargeFieldType extends BaseFieldType
{
    /**
     * FieldType name
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('SEO: Twitter Summary Card With Large Image');
    }

    /**
     * Define database column
     *
     * @return false
     */
    public function defineContentAttribute()
    {
        // We don't need a field in the Content table because
        // we are going to hijack the saving and retrieving of this
        // fieldtypes content so we can have multiple fields and store
        // them in a separate table.
        return false;
    }

    public function onAfterElementSave()
    {

        // Make sure we are actually submitting our field
        if ( ! isset($_POST['fields']['sproutseo_fields'])) return;

        // Determine our entryId
        $entryId = (isset($_POST['entryId']))
            ? $_POST['entryId']
            : $this->element->id;

        // get any overrides for this entry
        $model = craft()->sproutSeo_meta->getOverrideByEntryId($entryId);

        // Test to see if we have any values in our Sprout SEO fields
        $saveSproutSeoFields = false;
        foreach ($_POST['fields']['sproutseo_fields'] as $key => $value) {
            if ($value)
            {
                $saveSproutSeoFields = true;
                continue;
            }
        }

        // If we don't have any values in our Sprout SEO fields
        // don't add a record to the database
        // but if a record already exists, we also should delete it.
        if ( ! $saveSproutSeoFields )
        {
            // Remove record since it is now blank
            if ($model->id)
            {
                craft()->sproutSeo_meta->deleteOverrideById($model->id);
            }

            return;
        }
    }

    /**
     * Display our FieldType
     *
     * @param string $name  Our FieldType handle
     * @param string $value Always returns blank, our block
     *                       only styles the Instructions field
     * @return string Return our blocks input template
     */
    public function getInputHtml($name, $value)
    {
        $entryId = craft()->request->getSegment(3);

        $values = craft()->sproutSeo_meta->getTwitterCardSummaryLargeFieldsByEntryId($entryId);

        // $values->twitterSite = explode(',', $values->twitterSite);

        // Cleanup the namespace around the $name handle
        $name = str_replace("fields[", "", $name);
        $name = rtrim($name, "]");
        $name = "sproutseo_fields[$name]";

        return craft()->templates->render('sproutseo/_cp/fields/twitterCardSummaryLarge', array(
            'name'	     => $name,
            // // 'value'      => $value,
            'values'     => $values
        ));
    }

}
