<?php
namespace Craft;

/**
 * SproutSeo - Redirect model
 */
class SproutSeo_RedirectModel extends BaseElementModel
{
	protected $elementType = 'SproutSeo_Redirect';

	public function __toString()
	{
		return (string)$this->oldUrl;
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'oldUrl' => AttributeType::String,
			'newUrl' => AttributeType::String,
			'method' => AttributeType::Number,
			'regex' => AttributeType::Bool
		));
	}

	/**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * Returns the element's CP edit URL.
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutseo/redirects/'.$this->id);
	}
	//Get layout
}
