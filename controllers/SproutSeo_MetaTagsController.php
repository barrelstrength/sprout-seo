<?php
namespace Craft;

class SproutSeo_MetaTagsController extends BaseController
{
	/**
	 * Edit a Meta Tag Group
	 *
	 * @throws HttpException
	 */
	public function actionEditMetaTagGroup(array $variables = array())
	{
		// Determine what we're working with
		$segment        = craft()->request->getSegment(3);
		$metaTagGroupId = ($segment == 'new') ? null : $segment;

		// Get our Meta Model
		$metaTags = sproutSeo()->metaTags->getMetaTagGroupById($metaTagGroupId);

		if (isset($variables['metaTags']))
		{
			$metaTags = $variables['metaTags'];
		}

		// Set up our asset fields
		if (isset($metaTags->ogImage))
		{
			$asset           = craft()->elements->getElementById($metaTags->ogImage);
			$ogImageElements = array($asset);
		}
		else
		{
			$ogImageElements = array();
		}

		// Set up our asset fields
		if (isset($metaTags->twitterImage))
		{
			$asset                = craft()->elements->getElementById($metaTags->twitterImage);
			$twitterImageElements = array($asset);
		}
		else
		{
			$twitterImageElements = array();
		}

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		//get optimized settigns
		$settings = sproutSeo()->optimize->getDefaultFieldTypeSettings();

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		$this->renderTemplate('sproutseo/metatags/_edit', array(
			'metaTagGroupId'       => $metaTagGroupId,
			'metaTags'             => $metaTags,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType,
			'settings'             => $settings
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

		$metaTags = craft()->request->getPost('sproutseo_fields');

		// Check if this is a new or existing Meta Tag Group
		$metaTags['id'] = (isset($metaTags['id']) ? $metaTags['id'] : null);

		// Convert Checkbox Array into comma-delimited String
		if (isset($metaTags['robots']))
		{
			$metaTags['robots'] = SproutSeoOptimizeHelper::prepRobotsAsString($metaTags['robots']);
		}

		// Make our images single IDs instead of an array
		$metaTags['ogImage']      = (!empty($metaTags['ogImage']) ? $metaTags['ogImage'][0] : null);
		$metaTags['twitterImage'] = (!empty($metaTags['twitterImage']) ? $metaTags['twitterImage'][0] : null);

		$model->setAttributes($metaTags);

		if (sproutSeo()->metaTags->saveMetaTagGroup($model))
		{
			craft()->userSession->setNotice(Craft::t('New Meta Tag Group saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t("Couldn't save the Meta Tag Group."));
			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'metaTags' => $model
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

		$result = sproutSeo()->metaTags->deleteMetaTagGroupById($metaTagGroupId);

		$this->returnJson(array(
			'success' => $result >= 0 ? true : false
		));
	}
}
