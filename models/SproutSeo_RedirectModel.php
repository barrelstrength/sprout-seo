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
		return (string) $this->oldUrl;
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
			'regex'  => AttributeType::Bool,
			'count'  => AttributeType::Number,
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
		return UrlHelper::getCpUrl('sproutseo/redirects/' . $this->id);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('oldUrl, newUrl, method', 'required'),
			array('oldUrl', 'uniqueUrl'),
			array('method', 'validateMethod')
		);
	}

	/**
	 * Add validation so a user can't save a 404 in "enabled" status
	 */
	public function validateMethod($attribute,$params)
	{
		if ($this->enabled && $this->$attribute == SproutSeo_RedirectMethods::PageNotFound)
		{
			$this->addError($attribute, 'Cannot enable a 404 Redirect. Update Redirect method.');
		}
	}

	/**
	 * Add validation to unique oldUrl's
	 */
	public function uniqueUrl($attribute,$params)
	{
		$redirect = sproutSeo()->redirects->findUrl($this->$attribute);

		if ($redirect)
		{
			$this->addError($attribute, 'This url already exists.');
		}
	}
}
