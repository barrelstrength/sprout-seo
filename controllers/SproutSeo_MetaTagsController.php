<?php
namespace Craft;

class SproutSeo_MetaTagsController extends BaseController
{
	/**
	 * Edit a Meta Tag Group
	 *
	 * @throws HttpException
	 */
	public function actionEditMetaTagGroup()
	{
		// Determine what we're working with
		$segment   = craft()->request->getSegment(4);
		$defaultId = ($segment == 'new') ? null : $segment;

		// Get our Meta Model
		$default = sproutSeo()->defaults->getDefaultById($defaultId);

		// Set up our asset fields
		if (isset($default->ogImage))
		{
			$asset           = craft()->elements->getElementById($default->ogImage);
			$ogImageElements = array($asset);
		}
		else
		{
			$ogImageElements = array();
		}

		// Set up our asset fields
		if (isset($default->twitterImage))
		{
			$asset                = craft()->elements->getElementById($default->twitterImage);
			$twitterImageElements = array($asset);
		}
		else
		{
			$twitterImageElements = array();
		}

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		$this->renderTemplate('sproutseo/globals/meta-tags/_edit', array(
			'defaultId'            => $defaultId,
			'default'              => $default,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType
		));
	}

	/**
	 * Save a Meta Tag Group
	 *
	 * @throws Exception
	 * @throws HttpException
	 */
	public function actionSaveMetaTagGroup()
	{
		$this->requirePostRequest();

		$model = new SproutSeo_MetaTagsModel();

		$defaultFields = craft()->request->getPost('sproutseo_fields');
		// check if this is a new or existing default
		$defaultFields['id'] = (isset($defaultFields['id']) ? $defaultFields['id'] : null);

		// Convert Checkbox Array into comma-delimited String
		if (isset($defaultFields['robots']))
		{
			$defaultFields['robots'] = SproutSeoMetaHelper::prepRobotsAsString($defaultFields['robots']);
		}

		// Make our images single IDs instead of an array
		$defaultFields['ogImage']      = (!empty($defaultFields['ogImage']) ? $defaultFields['ogImage'][0] : null);
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

	/**
	 * Delete Meta Tag Group
	 *
	 * @throws HttpException
	 */
	public function actionDeleteMetaTagGroupById()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$metaTagGroupId = craft()->request->getRequiredPost('id');

		$result = sproutSeo()->defaults->deleteDefault($metaTagGroupId);

		$this->returnJson(array(
			'success' => $result >= 0 ? true : false
		));
	}
}
