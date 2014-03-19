<?php
namespace Craft;

class SproutSeo_BasicSeoFieldType extends BaseFieldType
{
    /**
     * FieldType name
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('SEO: Basic');
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


    /**
     * Performs any additional actions after the element has been saved.
     */
    public function onAfterElementSave()
    {
 
        // Make sure we are actually submitting our field
        if ( ! isset($_POST['fields']['sproutseo_fields'])) return;

        // Determine our entryId
        $entryId = (isset($_POST['entryId']))
            ? $_POST['entryId']
            : $this->element->id;

        // get any overrides for this entry
        $model = craft()->sproutSeo->getOverrideByEntryId($entryId);
        
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
                craft()->sproutSeo->deleteOverrideById($model->id);
            }
            
            return;
        }

        
        // Add the entry ID to the field data we will submit for Sprout SEO
        $attributes['entryId'] = $entryId;
        
        // Grab all the other Sprout SEO fields.
        $attributes = array_merge($attributes, $_POST['fields']['sproutseo_fields']);

        // If our override entry exists update it, 
        // if not create it
        if ($model->entryId) 
        {
            craft()->sproutSeo->updateOverride($model->id, $attributes);
        } 
        else 
        {
            craft()->sproutSeo->createOverride($attributes);
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

        // @TODO - Make this into a Model
        // $values = new SproutSeo_BasicSeoFieldModel;
        
        $values = craft()->sproutSeo->getBasicSeoFeildsByEntryId($entryId);

        // Cleanup the namespace around the $name handle
        $name = str_replace("fields[", "", $name);
        $name = rtrim($name, "]");

        $name = "sproutseo_fields[$name]";
        // $value = $values['title'];

        return craft()->templates->render('sproutseo/_fields/input', array(
            'name'	     => $name,
            'values'     => $values
        ));
    }

}
