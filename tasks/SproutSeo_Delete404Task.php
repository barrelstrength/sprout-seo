<?php
namespace Craft;

/**
 * Craft SproutSeo_Delete404Task task
 */
class SproutSeo_Delete404Task extends BaseTask
{
	private $_totalToDelete;
	private $_redirectModels;
	private $_redirectIdToExclude;

	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		//content table and array
		return array(
			'totalToDelete'     => AttributeType::Number,
			'redirectIdToExclude' => AttributeType::Number
		);
	}

	/**
	 * Returns the default description for this task.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Craft::t('Deleting oldest 404 redirects');
	}

	/**
	 * Gets the total number of steps for this task.
	 *
	 * @return int
	 */
	public function getTotalSteps()
	{
		$settings             = $this->getSettings();
		$this->_totalToDelete = $settings->totalToDelete;
		$this->_redirectIdToExclude = $settings->redirectIdToExclude;
		$params = array(
			':method' => SproutSeo_RedirectMethods::PageNotFound
		);

		$criteria = new \CDbCriteria;
		$criteria->condition = 'method = :method';

		if ($this->_redirectIdToExclude)
		{
			$criteria->condition .=' and id != :redirectId';
			$params[':redirectId'] =  $this->_redirectIdToExclude;
		}

		$criteria->limit = $this->_totalToDelete;
		$criteria->order = "dateUpdated DESC";
		$criteria->params = $params;

		$this->_redirectModels = SproutSeo_RedirectRecord::model()->findAll($criteria);

		return count($this->_redirectModels);
	}

	/**
	 * Runs a task step.
	 *
	 * @param int $step
	 *
	 * @return bool
	 */
	public function runStep($step)
	{
		$model    = $this->_redirectModels[$step];
		$response = false;

		if ($model)
		{
			$response = craft()->elements->deleteElementById($model->id);
		}

		if (!$response)
		{
			SproutSeoPlugin::log('SproutSeo has failed to delete the 404 redirect Id:'.$model->id, LogLevel::Error);
		}

		return true;
	}
}