<?php
namespace Craft;

class SproutSeo_DefaultsController extends BaseController
{

	public function actionEditDefault()
	{
		// Determine what we're working with
		$defaultId = craft()->request->getSegment(3);
		$variables['defaultId'] = ($defaultId == 'new') ? null : $defaultId;

		// Get our Meta Model
		$variables['default'] = sproutSeo()->defaults->getDefaultById($defaultId);

		// Set up our asset fields
		if (isset($variables['default']->ogImage))
		{
			$asset = craft()->elements->getElementById($variables['default']->ogImage);
			$variables['ogImageElements'] = array($asset);
		}
		else
		{
		    $variables['ogImageElements'] = array();
		}

		// Set up our asset fields
		if (isset($variables['default']->twitterImage))
		{
			$asset = craft()->elements->getElementById($variables['default']->twitterImage);
			$variables['twitterImageElements'] = array($asset);
		}
		else
		{
			$variables['twitterImageElements'] = array();
		}

		// Set assetsSourceExists
		$sources = craft()->assets->findFolders();
		$variables['assetsSourceExists'] = count($sources);

		// Set elementType
		$variables['elementType'] = craft()->elements->getElementType(ElementType::Asset);

		$this->renderTemplate('sproutSeo/defaults/_edit', $variables);
	}

	public function actionSaveDefault()
	{
		$this->requirePostRequest();

		// check if this is a new or existing default
		if (craft()->request->getPost('sproutseo_fields[id]') == null)
		{
			$id = false;
		}
		else
		{
			$id = craft()->request->getPost('sproutseo_fields[id]');
		}

		$model = new SproutSeo_MetaModel();
		$model->id = $id;

		$defaultFields = craft()->request->getPost('sproutseo_fields');

		// Convert Checkbox Array into comma-delimited String
		if (isset($defaultFields['robots']))
		{
			$defaultFields['robots'] = SproutSeoMetaHelper::prepRobotsAsString($defaultFields['robots']);
		}

		// Make our images single IDs instead of an array
		$defaultFields['ogImage'] = (!empty($defaultFields['ogImage']) ? $defaultFields['ogImage'][0] : null);
		$defaultFields['twitterImage'] = (!empty($defaultFields['twitterImage']) ? $defaultFields['twitterImage'][0] : null);

		$model->setAttributes($defaultFields);

		if (sproutSeo()->defaults->saveDefault($model))
		{
			craft()->userSession->setNotice(Craft::t('New default saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t("Couldn't save the default."));

			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'default' => $model
			));
		}
	}

	public function actionDeleteDefaults()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$this->returnJson(array(
			'success' => sproutSeo()->defaults->deleteDefault(craft()->request->getRequiredPost('id')) >= 0 ? true : false));
	}
}
