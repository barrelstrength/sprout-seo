<?php
namespace Craft;

class OneSeo_GeographicSeoFieldType extends BaseFieldType
{
    /**
     * FieldType name
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('SEO: Geographic');
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
        if ( ! isset($_POST['oneseo_fields'])) return;

        // Determine our entryId
        $entryId = (isset($_POST['entryId']))
            ? $_POST['entryId']
            : $this->element->id;

        // get any overrides for this entry
        $model = craft()->oneSeo->getOverrideByEntryId($entryId);
        
        // Test to see if we have any values in our One SEO fields
        $saveOneSeoFields = false;
        foreach ($_POST['oneseo_fields'] as $key => $value) {
            if ($value) 
            {
                $saveOneSeoFields = true;
                continue;
            }
        }

        // If we don't have any values in our One SEO fields
        // don't add a record to the database
        // but if a record already exists, we also should delete it.
        if ( ! $saveOneSeoFields )
        {
            // Remove record since it is now blank
            if ($model->id)
            {
                craft()->oneSeo->deleteOverrideById($model->id);
            }
            
            return;
        }

        
        // Add the entry ID to the field data we will submit for One SEO
        $attributes['entryId'] = $entryId;
        
        // Grab all the other One SEO fields.
        $attributes = array_merge($attributes, $_POST['oneseo_fields']);

        // If our override entry exists update it, 
        // if not create it
        if ($model->entryId) 
        {
            craft()->oneSeo->updateOverride($model->id, $attributes);
        } 
        else 
        {
            craft()->oneSeo->createOverride($attributes);
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
        // $values = new OneSeo_BasicSeoFieldModel;

        $values = craft()->oneSeo->getGeographicSeoFeildsByEntryId($entryId);

        // Cleanup the namespace around the $name handle
        $name = str_replace("fields[", "", $name);
        $name = rtrim($name, "]");

        $name = "oneseo_fields[$name]";
        // $value = $values['title'];

        return craft()->templates->render('oneseo/_fields/geo', array(
            'name'	     => $name,
            // 'value'      => $value,
            'values'     => $values
        ));
    }

}
