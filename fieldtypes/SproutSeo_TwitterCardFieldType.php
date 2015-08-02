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
		return Craft::t('Meta: Twitter Card');
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
		// grab only the opengraph fields
		$fields = (isset($_POST['fields']['sproutseo_fields'])) ? $_POST['fields']['sproutseo_fields'] : null;

		if ( ! isset($fields)) return;

		$entryId = (isset($_POST['entryId']) && $_POST['entryId'] != "")
			? $_POST['entryId']
			: $this->element->id;

		$locale = $this->element->locale;

		$model = sproutSeo()->overrides->getOverrideByEntryId($entryId, $locale);

		// Test to see if we have any values in our Sprout SEO fields
		$saveSproutSeoFields = false;
		foreach ($fields as $key => $value) {
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
				sproutSeo()->overrides->deleteOverrideById($model->id);
			}

			return;
		}

		if (isset($_POST['fields']['sproutseo_fields']['robots']))
		{
			$fields['robots'] = SproutSeoMetaHelper::prepRobotsAsString($_POST['fields']['sproutseo_fields']['robots']);
		}

		// Add the entry ID to the field data we will submit for Sprout SEO
		$attributes['entryId'] = $entryId;
		$attributes['locale'] = $locale;

		// Grab all the other Sprout SEO fields.
		$attributes = array_merge($attributes, $fields);

		// Make sure all of our images are strings (twitter/og)
		// We need to do this in case another seo field with images exists
		$attributes['twitterImage'] = (!empty($attributes['twitterImage']) ? $attributes['twitterImage'][0] : null);
		$attributes['ogImage'] = (!empty($attributes['ogImage']) ? $attributes['ogImage'][0] : null);

		// If our override entry exists update it,
		// if not create it
		if ($model->entryId)
		{
			sproutSeo()->overrides->updateOverride($model->id, $attributes);
		}
		else
		{
			sproutSeo()->overrides->createOverride($attributes);
		}

	}

	/**
	 * Display our FieldType
	 *
	 * @param string $name  Our FieldType handle
	 * @param string $value Always returns blank, our block
	 * only styles the Instructions field
	 * @return string Return our blocks input template
	 */
	public function getInputHtml($name, $value)
	{
		$entryId = craft()->request->getSegment(3);
		$locale = $this->element->locale;

		$variables['values'] = sproutSeo()->overrides->getTwitterCardFieldByEntryId($entryId, $locale);

		// Set up our asset fields
		if (isset($variables['values']->twitterImage))
		{
			$asset = craft()->elements->getElementById($variables['values']->twitterImage);
			$variables['twitterImageElements'] = array($asset);
		}
		else
		{
			$variables['twitterImageElements'] = array();
		}

		// Set assetsSourceExists
		$sources = craft()->assets->findFolders();
		$variables['assetsSourceExists'] = count($sources);

		// Set elementType
		$variables['elementType'] = craft()->elements->getElementType(ElementType::Asset);

		// include css resource
		craft()->templates->includeCssResource('sproutseo/css/fields.css');

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");
		$name = "sproutseo_fields[$name]";

		return craft()->templates->render('sproutseo/_partials/fields/twitter-card', $variables);
	}

}
