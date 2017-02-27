<?php
namespace Craft;

class SproutSeo_SectionMetadataController extends BaseController
{
	/**
	 * Loads a Section Metadata Edit template
	 *
	 * @throws HttpException
	 */
	public function actionSectionMetadataEditTemplate(array $variables = array())
	{
		$isCustom          = true;
		$segment           = craft()->request->getSegment(3);
		$sectionMetadataId = ($segment == 'new') ? null : $segment;

		// Get our Section Metadata Model
		$sectionMetadata       = sproutSeo()->sectionMetadata->getSectionMetadataById($sectionMetadataId);
		$isNew                 = $sectionMetadata->id != null ? false : true;
		$urlEnabledSectionType = null;

		$twitterImageElements = array();
		$ogImageElements      = array();

		if (isset($variables['sectionMetadata']))
		{
			$sectionMetadata = $variables['sectionMetadata'];
		}

		if ($sectionMetadata->type && $sectionMetadata->urlEnabledSectionId)
		{
			$isCustom = false;
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

		if (!$isNew && !$isCustom)
		{
			$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByType($sectionMetadata->type);

			$type                                    = $sectionMetadata->type;
			$urlEnabledSectionId                     = $sectionMetadata->urlEnabledSectionId;
			$urlEnabledSection                       = $urlEnabledSectionType->urlEnabledSections[$type . '-' . $urlEnabledSectionId];
			sproutSeo()->optimize->urlEnabledSection = $urlEnabledSection;
		}

		sproutSeo()->optimize->globals = sproutSeo()->globalMetadata->getGlobalMetadata();

		$prioritizedMetadata = sproutSeo()->optimize->getPrioritizedMetadataModel();

		$this->renderTemplate('sproutseo/sections/_edit', array(
			'sectionMetadataId'     => $sectionMetadataId,
			'sectionMetadata'       => $sectionMetadata,
			'metaImageElements'     => $metaImageElements,
			'ogImageElements'       => $ogImageElements,
			'twitterImageElements'  => $twitterImageElements,
			'assetsSourceExists'    => $assetsSourceExists,
			'elementType'           => $elementType,
			'settings'              => $settings,
			'isCustom'              => $isCustom,
			'isNew'                 => $isNew or $isCustom,
			'urlEnabledSectionType' => $urlEnabledSectionType,
			'prioritizedMetadata'   => $prioritizedMetadata
		));
	}

	/**
	 * Saves a Section Metadata Section
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

		if (sproutSeo()->sectionMetadata->saveSectionMetadata($model))
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'success'         => true,
					'sectionMetadata' => $model
				));
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('Section Metadata saved.'));

				$this->redirectToPostedUrl($model);
			}
		}
		else
		{
			if (craft()->request->isAjaxRequest())
			{
				$this->returnJson(array(
					'errors' => $model->getErrors(),
				));
			}
			else
			{
				craft()->userSession->setError(Craft::t("Couldn't save the Section Metadata."));

				craft()->urlManager->setRouteVariables(array(
					'sectionMetadata' => $model
				));
			}
		}
	}

	/**
	 * Saves a Section Metadata Section
	 *
	 * @todo - Refactor
	 *         can we merge with actionSaveSectionMetadata?
	 *
	 * @throws HttpException
	 */
	public function actionSaveSectionMetadataViaSitemapSection()
	{
		$this->requireAjaxRequest();

		$sectionMetadata = craft()->request->getPost('sproutseo.metadata');

		$model = SproutSeo_MetadataSitemapModel::populateModel($sectionMetadata);

		if (sproutSeo()->sectionMetadata->saveSectionMetadataViaSitemapSection($model))
		{
			$this->returnJson(array(
				'success'         => true,
				'sectionMetadata' => $model
			));
		}
		else
		{
			$this->returnJson(array(
				'errors' => $model->getErrors()
			));
		}
	}

	/**
	 * Deletes a Section Metadata Section
	 *
	 * @throws HttpException
	 */
	public function actionDeleteSectionMetadataById()
	{
		$this->requirePostRequest();

		$sectionMetadataId = craft()->request->getRequiredPost('id');

		$result = sproutSeo()->sectionMetadata->deleteSectionMetadataById($sectionMetadataId);

		if (craft()->request->isAjaxRequest())
		{
			$this->returnJson(array(
				'success' => $result >= 0 ? true : false
			));
		}

		$this->redirectToPostedUrl();
	}
}
