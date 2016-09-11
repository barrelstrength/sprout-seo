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
		$isCustom = true;
		// Determine what we're working with
		$segment        = craft()->request->getSegment(3);
		$metaTagGroupId = ($segment == 'new') ? null : $segment;

		// Get our Meta Model
		$metaTags = sproutSeo()->metaTags->getMetaTagGroupById($metaTagGroupId);

		//Check if is metadata GET
		if (isset($_GET['metatag']))
		{
			$metatag = $_GET['metatag'];
			$metatag = explode(',', $metatag);

			if (count($metatag) == 3)
			{
				$elementGroupId = $metatag[2];
				$groupName      = $metatag[0];
				$type           = explode('-', $metatag[1]);
				$elementType    = $type[0];

				$metaTags->elementGroupId = $elementGroupId;
				$metaTags->type           = $elementType;

				if ($segment == 'new')
				{
					// Just trying to get the url
					$sitemaps    = craft()->plugins->call('registerSproutSeoSitemap');
					$elementInfo = sproutSeo()->sitemap->getElementInfo($sitemaps, $elementType);

					if ($elementInfo != null)
					{
						$elementGroup = $elementInfo['elementGroupId'];

						$groupInfo = array(
							'groupName'      => $elementGroup,
							'sitemapId'      => $metatag[1],
							'elementGroupId' => $elementGroupId
						);

						$response = sproutSeo()->metaTags->getMetadataInfo($groupInfo);
						$element  = $response['element'];

						if ($element)
						{
							$metaTags->url = $element->urlFormat;
						}
					}

					$metaTags->name   = ucfirst($groupName) . ' ' . ucfirst($elementType);
					$metaTags->handle = strtolower($groupName) . ucfirst($elementType);
					$metaTags->handle = str_replace(' ', '', $metaTags->handle);
				}
			}
		}

		$twitterImageElements = array();
		$ogImageElements      = array();

		if (isset($variables['metaTags']))
		{
			$metaTags = $variables['metaTags'];
		}

		if ($metaTags->type && $metaTags->elementGroupId)
		{
			$isCustom = false;
		}

		// Set up our asset fields
		if (isset($metaTags->optimizedImage))
		{
			$asset             = craft()->elements->getElementById($metaTags->optimizedImage);
			$metaImageElements = array($asset);
		}

		if (isset($metaTags->ogImage))
		{
			$asset           = craft()->elements->getElementById($metaTags->ogImage);
			$ogImageElements = array($asset);
		}

		if (isset($metaTags->twitterImage))
		{
			$asset                = craft()->elements->getElementById($metaTags->twitterImage);
			$twitterImageElements = array($asset);
		}

		$metaTags->robots = ($metaTags->robots) ? SproutSeoOptimizeHelper::prepRobotsForSettings($metaTags->robots) : SproutSeoOptimizeHelper::prepRobotsForSettings($metaTags->robots);

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		//get optimized settigns
		$settings = sproutSeo()->optimize->getDefaultFieldTypeSettings();

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		$this->renderTemplate('sproutseo/metadata/_edit', array(
			'metaImageElements'    => $metaImageElements,
			'metaTagGroupId'       => $metaTagGroupId,
			'metaTags'             => $metaTags,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType,
			'settings'             => $settings,
			'isCustom'             => $isCustom
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
		$sitemap  = craft()->request->getPost('sitemap_fields');

		// Check if this is a new or existing Meta Tag Group
		$metaTags['id'] = (isset($metaTags['id']) ? $metaTags['id'] : null);

		// Convert Checkbox Array into comma-delimited String
		if (isset($metaTags['robots']))
		{
			$metaTags['robots'] = SproutSeoOptimizeHelper::getRobotsMetaValue($metaTags['robots']);
		}

		$optimizedTitle       = (!empty($metaTags['optimizedTitle']) ? $metaTags['optimizedTitle'] : null);
		$optimizedDescription = (!empty($metaTags['optimizedDescription']) ? $metaTags['optimizedDescription'] : null);

		$metaTags['title']              = $optimizedTitle;
		$metaTags['ogTitle']            = $optimizedTitle;
		$metaTags['twitterTitle']       = $optimizedTitle;
		$metaTags['description']        = $optimizedDescription;
		$metaTags['ogDescription']      = $optimizedDescription;
		$metaTags['twitterDescription'] = $optimizedDescription;

		// Make our images single IDs instead of an array
		$optimizedImage = (!empty($metaTags['optimizedImage']) ? $metaTags['optimizedImage'][0] : null);

		$metaTags['optimizedImage'] = $optimizedImage;
		$metaTags['ogImage']        = $optimizedImage;
		$metaTags['twitterImage']   = $optimizedImage;

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
