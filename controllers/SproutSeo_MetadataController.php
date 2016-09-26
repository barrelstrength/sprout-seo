<?php
namespace Craft;

class SproutSeo_MetadataController extends BaseController
{
	/**
	 * Edit Section Metadata
	 *
	 * @throws HttpException
	 */
	public function actionSectionMetadataEditTemplate(array $variables = array())
	{
		$isSitemapCustomPage = true;
		$segment             = craft()->request->getSegment(3);
		$sectionMetadataId   = ($segment == 'new') ? null : $segment;

		// Get our Section Metadata Model
		$sectionMetadata = sproutSeo()->metadata->getSectionMetadataById($sectionMetadataId);
		$isNew           = $sectionMetadata->id != null ? false : true;
		$sitemaps        = craft()->plugins->call('registerSproutSeoSitemap');
		$elementInfo     = null;

		if (craft()->request->getSegment(3) == 'new')
		{
			$sectionmetadataname = craft()->request->getPost('sectionmetadataname');
			$elementGroupId      = craft()->request->getPost('elementgroupid');
			$groupName           = craft()->request->getPost('elementgrouphandle');
			$sitemapId           = craft()->request->getPost('sitemapid');
			$type                = explode('-', $sitemapId);
			$elementType         = $type[0];

			$sectionMetadata->elementGroupId = $elementGroupId;
			$sectionMetadata->type           = $elementType;

			// Just trying to get the url
			$elementInfo = sproutSeo()->sitemap->getSectionMetadataElementInfo($sitemaps, $elementType);

			if ($elementInfo != null)
			{
				$elementGroup = $elementInfo['elementGroupId'];

				$groupInfo = array(
					'groupName'      => $elementGroup,
					'sitemapId'      => $sitemapId,
					'elementGroupId' => $elementGroupId
				);

				$response = sproutSeo()->metadata->getSectionMetadataInfo($groupInfo);
				$element  = $response['element'];

				if ($element)
				{
					$sectionMetadata->url = $element->urlFormat;
				}
			}

			$sectionMetadata->name   = $sectionmetadataname;
			$sectionMetadata->handle = strtolower($groupName) . ucfirst($elementType);
			$sectionMetadata->handle = str_replace(' ', '', $sectionMetadata->handle);
		}

		$twitterImageElements = array();
		$ogImageElements      = array();

		if (isset($variables['sectionMetadata']))
		{
			$sectionMetadata = $variables['sectionMetadata'];
		}

		if ($sectionMetadata->type && $sectionMetadata->elementGroupId)
		{
			$isSitemapCustomPage = false;
		}

		// Set up our asset fields
		if (isset($sectionMetadata->optimizedImage))
		{
			$asset             = craft()->elements->getElementById($sectionMetadata->optimizedImage);
			$metaImageElements = array($asset);
		}

		if (isset($sectionMetadata->ogImage))
		{
			$asset           = craft()->elements->getElementById($sectionMetadata->ogImage);
			$ogImageElements = array($asset);
		}

		if (isset($sectionMetadata->twitterImage))
		{
			$asset                = craft()->elements->getElementById($sectionMetadata->twitterImage);
			$twitterImageElements = array($asset);
		}

		$sectionMetadata->robots = ($sectionMetadata->robots) ? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($sectionMetadata->robots) : SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($sectionMetadata->robots);

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		//get optimized settings
		$settings = SproutSeoOptimizeHelper::getDefaultFieldTypeSettings();

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		if (!$isNew)
		{
			$elementInfo = sproutSeo()->sitemap->getSectionMetadataElementInfo($sitemaps, $sectionMetadata->type);
		}

		$this->renderTemplate('sproutseo/sections/_edit', array(
			'sectionMetadataId'    => $sectionMetadataId,
			'sectionMetadata'      => $sectionMetadata,
			'metaImageElements'    => $metaImageElements,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType,
			'settings'             => $settings,
			'isSitemapCustomPage'  => $isSitemapCustomPage,
			'isNew'                => $isNew or $isSitemapCustomPage,
			'elementInfo'          => $elementInfo
		));
	}

	/**
	 * Save Section Metadata Section
	 *
	 * @throws Exception
	 * @throws HttpException
	 */
	public function actionSaveSectionMetadata()
	{
		$this->requirePostRequest();

		$model = new SproutSeo_MetadataModel();

		$sectionMetadata = craft()->request->getPost('sproutseo.metadata');

		// Check if this is a new or existing Section Metadata
		$sectionMetadata['id'] = (isset($sectionMetadata['id']) ? $sectionMetadata['id'] : null);

		// Convert Checkbox Array into comma-delimited String
		if (isset($sectionMetadata['robots']))
		{
			$sectionMetadata['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($sectionMetadata['robots']);
		}

		$model->setAttributes($sectionMetadata);

		$model = SproutSeoOptimizeHelper::updateOptimizedAndAdvancedMetaValues($model);

		if (sproutSeo()->metadata->saveSectionMetadata($model))
		{
			craft()->userSession->setNotice(Craft::t('Section Metadata saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t("Couldn't save the Section Metadata."));

			craft()->urlManager->setRouteVariables(array(
				'sectionMetadata' => $model
			));
		}
	}

	/**
	 * Delete Section Metadata Section
	 *
	 * @throws HttpException
	 */
	public function actionDeleteSectionMetadataById()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$sectionMetadataId = craft()->request->getRequiredPost('id');

		$result = sproutSeo()->metadata->deleteSectionMetadataById($sectionMetadataId);

		$this->returnJson(array(
			'success' => $result >= 0 ? true : false
		));
	}
}
