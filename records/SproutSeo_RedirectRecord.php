<?php
namespace Craft;

/**
 * SproutSeo - Redirect record
 */
class SproutSeo_RedirectRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_redirects';
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'oldUrl' => array(AttributeType::String, 'required' => true),
			'newUrl' => array(AttributeType::String, 'required' => true),
			'method' => array(AttributeType::Number, 'required' => true),
			'regex' => array(AttributeType::Bool, 'required' => true)
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element'  => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

	/**
	 * Update "oldUrl" and "newUrl" to starts with a "/"
	 *
	 */
	public function beforeSave()
	{
		$this->oldUrl = sproutSeo()->redirects->addSlash($this->oldUrl);

		$this->newUrl = sproutSeo()->redirects->addSlash($this->newUrl);

		return true;
	}
}
