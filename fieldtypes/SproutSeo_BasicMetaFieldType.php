<?php
namespace Craft;

class SproutSeo_BasicMetaFieldType extends BaseFieldType
{
	/**
	 * FieldType name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Meta: Basic');
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

		// Add the entry ID to the field data we will submit for Sprout SEO
		$attributes['entryId'] = $entryId;

		// Grab all the other Sprout SEO fields.
		$attributes = array_merge($attributes, $_POST['fields']['sproutseo_fields']);

		// Make sure all of our images are strings (twitter/og)
		// We need to do this in case another seo field with images exists
		$attributes['twitterImage'] = (!empty($attributes['twitterImage']) ? $attributes['twitterImage'][0] : null);
		$attributes['ogImage'] = (!empty($attributes['ogImage']) ? $attributes['ogImage'][0] : null);

		// If our override entry exists update it,
		// if not create it
		if ($model->entryId)
		{
			craft()->sproutSeo_meta->updateOverride($model->id, $attributes);
		}
		else
		{
			craft()->sproutSeo_meta->createOverride($attributes);
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
		// $values = new SproutSeo_BasicMetaFieldModel;

		$values = craft()->sproutSeo_meta->getBasicMetaFieldsByEntryId($entryId);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");

		$name = "sproutseo_fields[$name]";
		// $value = $values['title'];

		return craft()->templates->render('sproutseo/_cp/fields/input', array(
			'name'	     => $name,
			'values'     => $values
		));
	}

}
