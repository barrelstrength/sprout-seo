<?php
namespace Craft;

class SproutSeo_MetaController extends BaseController
{
	/**
	 * Save Template Info to the Datbase
	 * 
	 * @return mixed Return to Page
	 */
	public function actionSaveTemplates()
	{
		$this->requirePostRequest();

		// Assume we have a new item
		// @TODO - probably not a good assumption
		$id = false; 

		$model = craft()->sproutSeo_meta->newMetaModel($id);
		
		$templateFields = craft()->request->getPost('template_fields');

		// Convert Checkbox Array into comma-delimited String
		if (isset($templateFields['robots']))
		{
			$templateFields['robots'] = craft()->sproutSeo_meta->prepRobotsForDb($templateFields['robots']);
		}

		$model->setAttributes($templateFields);

		if (craft()->sproutSeo_meta->saveTemplateInfo($model))
		{
			craft()->userSession->setNotice(Craft::t('Item saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t("Couldn't save."));
			
			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'template' => $model
			));
		}

	}

	/**
	 * Delete template
	 * 
	 * @return json response
	 */
	public function actionDeleteTemplates()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();
		
		$templateId = craft()->request->getRequiredPost('id');
		$response = craft()->sproutSeo_meta->deleteTemplate($templateId);

		$this->returnJson(array(
			'success' => ($response >= 0) ? true : false
		));
	}
}
