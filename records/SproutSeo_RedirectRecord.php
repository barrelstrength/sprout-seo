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
			'regex'  => array(AttributeType::Bool, 'required' => true),
			'count'  => array(AttributeType::Number, 'required' => true, 'default' => 0),
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('id')),
		);
	}

	/**
	 * Update "oldUrl" and "newUrl" to starts with a "/"
	 *
	 */
	protected function beforeValidate()
	{
		if (!$this->regex)
		{
			$this->oldUrl = sproutSeo()->redirects->addSlash($this->oldUrl);
		}

		$this->newUrl = sproutSeo()->redirects->addSlash($this->newUrl);

		return true;
	}

	/**
	 * Scope to join with structure
	 */
	public function structured()
	{
		$structureId = sproutSeo()->redirects->getStructureId();
		$tablePrefix = craft()->db->getNormalizedTablePrefix();

		$criteria        = $this->getDbCriteria();
		$criteria->alias = 'redirects';
		$criteria->join  = 'LEFT JOIN ' . $tablePrefix . 'elements element ON element.id=redirects.id ';
		$criteria->join  .= 'LEFT JOIN ' . $tablePrefix . 'structures structures ON structures.id=:structureId ';
		$criteria->join  .= 'LEFT JOIN ' . $tablePrefix . 'structureelements elements ON elements.structureId=structures.id ';
		$criteria->order = 'elements.lft ASC';
		$criteria->addCondition('elements.elementID = element.id');

		$criteria->params = array(':structureId' => $structureId);

		return $this;
	}
}
