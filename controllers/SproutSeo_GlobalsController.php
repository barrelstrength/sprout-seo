<?php
namespace Craft;

class SproutSeo_GlobalsController extends BaseController
{
	/**
	 * Save Globals to the database
	 *
	 * @throws HttpException
	 */
	public function actionSaveGlobals()
	{
		$this->requirePostRequest();

		$postData   = craft()->request->getPost('sproutseo.globals');
		$globalKeys = craft()->request->getPost('globalKeys');

		$globalKeys = explode(',', $globalKeys);

		$globals = SproutSeo_GlobalsModel::populateModel($postData);

		$globalMetadata = $this->populateGlobalMetadata($postData);

		$globals->meta = JsonHelper::encode($globalMetadata);

		if (sproutSeo()->globals->saveGlobals($globalKeys, $globals))
		{
			craft()->userSession->setNotice(Craft::t('Globals saved.'));

			$this->redirectToPostedUrl($globals);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save globals.'));

			craft()->urlManager->setRouteVariables(array(
				'globals' => $globals
			));
		}
	}

	/**
	 * @param $postData
	 *
	 * @return SproutSeo_MetadataModel
	 */
	public function populateGlobalMetadata($postData)
	{
		$settings = craft()->plugins->getPlugin('sproutseo')->getSettings();
		$locale   = craft()->i18n->getLocaleById(craft()->language);
		$localeId = $locale->id;

		$oldGlobals        = sproutSeo()->globals->getGlobalMetadata();
		$oldIdentity       = isset($oldGlobals) ? $oldGlobals->identity : null;
		$identity          = isset($postData['identity']) ? $postData['identity'] : $oldIdentity;
		$oldSocialProfiles = isset($oldGlobals) ? $oldGlobals->social : array();

		$globalMetadata = new SproutSeo_MetadataModel();
		$siteName       = craft()->getSiteName();

		$urlSetting = isset($postData['identity']['url']) ? $postData['identity']['url'] : null;
		$siteUrl    = SproutSeoOptimizeHelper::getGlobalMetadataSiteUrl($urlSetting);

		$socialProfiles     = isset($postData['social']) ? $postData['social'] : $oldSocialProfiles;
		$twitterProfileName = SproutSeoOptimizeHelper::getTwitterProfileName($socialProfiles);

		$robots          = isset($postData['robots']) ? $postData['robots'] : $oldGlobals->robots;
		$robotsMetaValue = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($robots);

		if ($settings->localeIdOverride)
		{
			$localeId = $settings->localeIdOverride;
		}

		if ($identity)
		{
			$identityName         = $identity['name'];
			$optimizedTitle       = $identityName;
			$optimizedDescription = $identity['description'];
			$optimizedImage       = isset($identity['logo'][0]) ? $identity['logo'][0] : null;

			$globalMetadata->optimizedTitle       = $optimizedTitle;
			$globalMetadata->optimizedDescription = $optimizedDescription;
			$globalMetadata->optimizedImage       = $optimizedImage;

			$globalMetadata->title       = $optimizedTitle;
			$globalMetadata->description = $optimizedDescription;
			$globalMetadata->keywords    = $identity['keywords'];

			$globalMetadata->robots    = $robotsMetaValue;
			$globalMetadata->canonical = $siteUrl;

			$globalMetadata->region    = ""; // @todo - add location info
			$globalMetadata->placename = "";
			$globalMetadata->position  = "";
			$globalMetadata->latitude  = "";
			$globalMetadata->longitude = "";

			$globalMetadata->ogType        = 'website';
			$globalMetadata->ogSiteName    = $siteName;
			$globalMetadata->ogUrl         = $siteUrl;
			$globalMetadata->ogAuthor      = $identityName;
			$globalMetadata->ogPublisher   = $identityName;
			$globalMetadata->ogTitle       = $optimizedTitle;
			$globalMetadata->ogDescription = $optimizedDescription;
			$globalMetadata->ogImage       = $optimizedImage;
			$globalMetadata->ogLocale      = $localeId;

			$globalMetadata->twitterCard        = 'summary';
			$globalMetadata->twitterSite        = $twitterProfileName;
			$globalMetadata->twitterCreator     = $twitterProfileName;
			$globalMetadata->twitterUrl         = $siteUrl;
			$globalMetadata->twitterTitle       = $optimizedTitle;
			$globalMetadata->twitterDescription = $optimizedDescription;
			$globalMetadata->twitterImage       = $optimizedImage;
		}

		return $globalMetadata;
	}
}
