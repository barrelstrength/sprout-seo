<?php
namespace Craft;

class SproutSeo_OpenGraphFieldType extends BaseFieldType
{
	/**
	 * FieldType name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Meta: Open Graph');
	}

	/**
	 * Define database column
	 *
	 * @return false
	 */
	public function defineContentAttribute()
	{
		// We don't need a column in the content table
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

		$values = craft()->sproutSeo_meta->getOpenGraphFieldsByEntryId($entryId);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");
		$name = "sproutseo_fields[$name]";

		return craft()->templates->render('sproutseo/_cp/fields/opengraph', array(
			'name'	     => $name,
			'values'     => $values
		));
	}

}
