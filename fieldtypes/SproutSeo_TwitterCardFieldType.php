<?php
namespace Craft;

class SproutSeo_TwitterCardFieldType extends BaseFieldType
{
    /**
     * FieldType name
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('SEO: Twitter Card');
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

        $values = craft()->sproutSeo_meta->getTwitterCardFieldsByEntryId($entryId);

        // $inputId = craft()->templates->formatInputId($name);
		// $namespaceInputId = craft()->templates->namespaceInputId($inputId);
		// RETURN craft()->templates->render('sproutlinkfield/_fields/input', array(
		// 	'id' => $namespaceInputId,
		// 	'name'  => $name,
		// 	'value' => $value
		// ));


        // Cleanup the namespace around the $name handle
        $name = str_replace("fields[", "", $name);
        $name = rtrim($name, "]");
        $name = "sproutseo_fields[$name]";

        return craft()->templates->render('sproutseo/_cp/fields/twitter', array(
            'name'	     => $name,
            'values'     => $values
        ));
    }

}
