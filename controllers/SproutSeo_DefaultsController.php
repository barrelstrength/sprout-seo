<?php
namespace Craft;

class SproutSeo_DefaultsController extends BaseController
{

	public function actionEditDefault()
	{

		$defaultId = craft()->request->getSegment(3);

		// check if the segment is 'new' for a new entry
		if ($defaultId === 'new') {
			$defaultId = null;
		}

		$this->renderTemplate('sproutSeo/defaults/_edit', array(
			'defaultId' => $defaultId
			)
		);
	}

	public function actionSaveDefault()
	{

		$this->requirePostRequest();

		$id = false; // we assume have a new item now

		$model = craft()->sproutSeo_meta->newMetaModel($id);

		$defaultFields = craft()->request->getPost('default_fields');

		// Convert Checkbox Array into comma-delimited String
		if (isset($defaultFields['robots']))
		{
			$defaultFields['robots'] = craft()->sproutSeo_meta->prepRobotsForDb($defaultFields['robots']);
		}

		$model->setAttributes($defaultFields);

		if (craft()->sproutSeo_meta->saveDefaultInfo($model))
		{
			craft()->userSession->setNotice(Craft::t('New default saved.'));
			$this->redirectToPostedUrl();
		}

		craft()->userSession->setError(Craft::t("Couldn't save the default."));

		// Send the field back to the template
		craft()->urlManager->setRouteVariables(array(
			'default' => $model
		));
	}

	public function actionDeleteDefaults()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$this->returnJson(array(
			'success' => craft()->sproutSeo_meta->deleteDefault(craft()->request->getRequiredPost('id')) >= 0 ? true : false));
	}
}
