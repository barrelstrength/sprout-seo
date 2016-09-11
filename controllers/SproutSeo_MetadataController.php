<?php
namespace Craft;

class SproutSeo_MetadataController extends BaseController
{
	/**
	 * Edit a Metadata Group
	 *
	 * @throws HttpException
	 */
	public function actionMetadataGroupEditTemplate(array $variables = array())
	{
		$isCustom = true;

		// Determine what we're working with
		$segment         = craft()->request->getSegment(3);
		$metadataGroupId = ($segment == 'new') ? null : $segment;

		// Get our Meta Model
		$metaTags = sproutSeo()->metadata->getMetadataGroupById($metadataGroupId);

		// Check if we need to create a new metadata group from an existing url enabled section
		// This appears to be for when a Metadata Group is clicked on for the first time.
		// We pass additional info to the page to build the right record in the db.
		// /sproutseo/metadata/new?metatag=secondGroupOfCategories,categories-new-1,2
		if (craft()->request->getSegment(3) == 'new')
		{
			//'metadatagroupname' => 'Field Test'
			//'elementgrouphandle' => 'fieldTest'
			//'sitemapid' => 'sections-new-2'
			//'elementgroupid' => '9'
			//'metadataId' => ''
			//'metatag' => 'fieldTest,sections-new-2,9'

			$metadatagroupname = craft()->request->getPost('metadatagroupname');
			$elementGroupId    = craft()->request->getPost('elementgroupid');
			$groupName         = craft()->request->getPost('elementgrouphandle');
			$sitemapId         = craft()->request->getPost('sitemapid');
			$type              = explode('-', $sitemapId);
			$elementType       = $type[0];

			$metaTags->elementGroupId = $elementGroupId;
			$metaTags->type           = $elementType;

			// Just trying to get the url
			$sitemaps    = craft()->plugins->call('registerSproutSeoSitemap');
			$elementInfo = sproutSeo()->sitemap->getElementInfo($sitemaps, $elementType);

			if ($elementInfo != null)
			{
				$elementGroup = $elementInfo['elementGroupId'];

				$groupInfo = array(
					'groupName'      => $elementGroup,
					'sitemapId'      => $sitemapId,
					'elementGroupId' => $elementGroupId
				);

				$response = sproutSeo()->metadata->getMetadataInfo($groupInfo);
				$element  = $response['element'];

				if ($element)
				{
					$metaTags->url = $element->urlFormat;
				}
			}

			$metaTags->name   = $metadatagroupname;
			$metaTags->handle = strtolower($groupName) . ucfirst($elementType);
			$metaTags->handle = str_replace(' ', '', $metaTags->handle);
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
			'metadataGroupId'      => $metadataGroupId,
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
	 * Save a Metadata Group
	 *
	 * @throws Exception
	 * @throws HttpException
	 */
	public function actionSaveMetadataGroup()
	{
		$this->requirePostRequest();

		$model = new SproutSeo_MetadataModel();

		$metaTags = craft()->request->getPost('sproutseo.metadata');

		// Check if this is a new or existing Metadata Group
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

		if (sproutSeo()->metadata->saveMetadataGroup($model))
		{
			craft()->userSession->setNotice(Craft::t('New Metadata Group saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t("Couldn't save the Metadata Group."));

			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'metaTags' => $model
			));
		}
	}

	/**
	 * Delete Metadata Group
	 *
	 * @throws HttpException
	 */
	public function actionDeleteMetadataGroupById()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$metadataGroupId = craft()->request->getRequiredPost('id');

		$result = sproutSeo()->metadata->deleteMetadataGroupById($metadataGroupId);

		$this->returnJson(array(
			'success' => $result >= 0 ? true : false
		));
	}
}
