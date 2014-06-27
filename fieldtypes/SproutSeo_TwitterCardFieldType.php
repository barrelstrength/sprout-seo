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
		// Make sure we are actually submitting our field
		if ( ! isset($_POST['fields']['sproutseo_fields'])) return;
		
		$entryId = craft()->request->getSegment(3);

		$values = craft()->sproutSeo_meta->getTwitterCardFieldsByEntryId($entryId);

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
