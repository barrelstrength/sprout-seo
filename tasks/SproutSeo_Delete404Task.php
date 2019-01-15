<?php
namespace Craft;

/**
 * Craft SproutSeo_Delete404Task task
 */
class SproutSeo_Delete404Task extends BaseTask
{
	private $_totalToDelete;
	private $_redirectIds;
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

        $query = craft()->db->createCommand()
            ->select('id')
            ->from('{{sproutseo_redirects}}')
            ->where(array('method' => SproutSeo_RedirectMethods::PageNotFound));

		if ($this->_redirectIdToExclude)
		{
            $query->andWhere('id != :redirectId', array(':redirectId' => $this->_redirectIdToExclude));
		}

        $query->limit = $this->_totalToDelete;
		$query->order = "dateUpdated ASC";

		$this->_redirectIds = $query->queryAll();

		return count($this->_redirectIds);
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
		$modelId    = isset($this->_redirectIds[$step]['id']) ? $this->_redirectIds[$step]['id'] : null;
		$response = false;

		if ($modelId)
		{
			$response = craft()->elements->deleteElementById($modelId);
		}

		if (!$response)
		{
			SproutSeoPlugin::log('SproutSeo has failed to delete the 404 redirect Id:'.$modelId, LogLevel::Error);
		}

		return true;
	}
}