<?php
namespace Craft;

/**
 * Redirects field type
 */
class SproutSeo_RedirectsFieldType extends BaseElementFieldType
{
	/**
	 * @access protected
	 * @var string $elementType The element type this field deals with.
	 */
	protected $elementType = 'SproutSeo_Redirect';

	/**
	 * Returns the label for the "Add" button.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getAddButtonLabel()
	{
		return Craft::t('Add Redirect');
	}
}
