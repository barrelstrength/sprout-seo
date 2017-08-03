<?php
namespace Craft;

/**
 * SproutSeo - SproutSeo_RedirectLogRecord
 */
class SproutSeo_RedirectLogRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_redirects_log';
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'redirectId'  => array(AttributeType::Number, 'required' => true),
			'ipAddress'   => array(AttributeType::String, 'required' => false),
			'referralURL' => array(AttributeType::String, 'required' => false),
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'redirect' => array(static::BELONGS_TO, 'SproutSeo_RedirectRecord', 'redirectId', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('id')),
		);
	}
}
