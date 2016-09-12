<?php
namespace Craft;

class SproutSeo_SchemaController extends BaseController
{
	/**
	 * Load the Structured Data Edit Page
	 *
	 * @throws HttpException
	 */
	public function actionSchemaEditTemplate()
	{
		$segment = craft()->request->getSegment(3);
		$this->renderTemplate('sproutseo/schema/' . $segment, array());
	}

	/**
	 * Save Structured Data to the database
	 *
	 * @throws HttpException
	 */
	public function actionSaveSchema()
	{
		$this->requirePostRequest();

		$postData   = craft()->request->getPost('sproutseo.schema');
		$schemaType = craft()->request->getPost('schemaType');

		$schemaTypes = explode(',', $schemaType);

		$schema = SproutSeo_SchemaModel::populateModel($postData);

		$globalFallbackMetaTags = $this->populateGlobalFallbackMetaTags($postData);

		$schema->meta = JsonHelper::encode($globalFallbackMetaTags);

		if (sproutSeo()->schema->saveSchema($schemaTypes, $schema))
		{
			craft()->userSession->setNotice(Craft::t('Schema saved.'));

			$this->redirectToPostedUrl($schema);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save schema.'));
			craft()->urlManager->setRouteVariables(array(
				'schema' => $schema
			));
		}
	}

	/**
	 * Save the Verify Ownership Structured Data to the database
	 *
	 * @todo - consider refactoring this into the standard saveSchema method
	 *
	 * @throws HttpException
	 */
	public function actionSaveVerifyOwnership()
	{
		$this->requirePostRequest();

		$ownershipMeta = craft()->request->getPost('sproutseo.meta.ownership');
		$schemaType    = 'ownership';

		// Remove empty items from multi-dimensional array
		$ownershipMeta = array_filter(array_map('array_filter', $ownershipMeta));

		$ownershipMetaWithKeys = array();

		foreach ($ownershipMeta as $key => $meta)
		{
			// @todo - add proper validation and return errors to template
			if (count($meta) == 3)
			{
				$ownershipMetaWithKeys[$key]['service']          = $meta[0];
				$ownershipMetaWithKeys[$key]['metaTag']          = $meta[1];
				$ownershipMetaWithKeys[$key]['verificationCode'] = $meta[2];
			}
		}

		$schema = SproutSeo_SchemaModel::populateModel(array(
				$schemaType => $ownershipMetaWithKeys
			)
		);

		if (sproutSeo()->schema->saveSchema(array($schemaType), $schema))
		{
			craft()->userSession->setNotice(Craft::t('Schema saved.'));

			$this->redirectToPostedUrl($schema);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Unable to save schema.'));

			craft()->urlManager->setRouteVariables(array(
				'schema' => $schema
			));
		}
	}

	public function populateGlobalFallbackMetaTags($postData)
	{
		$oldGlobals        = sproutSeo()->schema->getGlobals();
		$oldIdentity       = isset($oldGlobals) ? $oldGlobals->identity : null;
		$identity          = isset($postData['identity']) ? $postData['identity'] : $oldIdentity;
		$oldSocialProfiles = isset($oldGlobals) ? $oldGlobals->social : array();

		$globalFallbackMetaTags = new SproutSeo_MetadataModel();
		$siteName               = craft()->getSiteName();

		$urlSetting = isset($postData['identity']['url']) ? $postData['identity']['url'] : null;
		$siteUrl    = SproutSeoOptimizeHelper::getGlobalSiteUrl($urlSetting);

		$socialProfiles     = isset($postData['social']) ? $postData['social'] : $oldSocialProfiles;
		$twitterProfileName = SproutSeoOptimizeHelper::getTwitterProfileName($socialProfiles);

		$robots          = isset($postData['robots']) ? $postData['robots'] : $oldGlobals->robots;
		$robotsMetaValue = SproutSeoOptimizeHelper::getRobotsMetaValue($robots);

		if ($identity)
		{
			$identityName         = $identity['name'];
			$optimizedTitle       = $identityName;
			$optimizedDescription = $identity['description'];
			$optimizedImage       = isset($identity['logo'][0]) ? $identity['logo'][0] : null;

			$globalFallbackMetaTags->optimizedTitle       = $optimizedTitle;
			$globalFallbackMetaTags->optimizedDescription = $optimizedDescription;
			$globalFallbackMetaTags->optimizedImage       = $optimizedImage;

			$globalFallbackMetaTags->title       = $optimizedTitle;
			$globalFallbackMetaTags->description = $optimizedDescription;
			$globalFallbackMetaTags->keywords    = $identity['keywords'];

			$globalFallbackMetaTags->robots    = $robotsMetaValue;
			$globalFallbackMetaTags->canonical = $siteUrl;

			$globalFallbackMetaTags->region    = ""; // @todo - add location info
			$globalFallbackMetaTags->placename = "";
			$globalFallbackMetaTags->position  = "";
			$globalFallbackMetaTags->latitude  = "";
			$globalFallbackMetaTags->longitude = "";

			$globalFallbackMetaTags->ogType        = 'website';
			$globalFallbackMetaTags->ogSiteName    = $siteName;
			$globalFallbackMetaTags->ogUrl         = $siteUrl;
			$globalFallbackMetaTags->ogAuthor      = $identityName;
			$globalFallbackMetaTags->ogPublisher   = $identityName;
			$globalFallbackMetaTags->ogTitle       = $optimizedTitle;
			$globalFallbackMetaTags->ogDescription = $optimizedDescription;
			$globalFallbackMetaTags->ogImage       = $optimizedImage;

			$globalFallbackMetaTags->twitterCard        = 'summary';
			$globalFallbackMetaTags->twitterSite        = $twitterProfileName;
			$globalFallbackMetaTags->twitterCreator     = $twitterProfileName;
			$globalFallbackMetaTags->twitterUrl         = $siteUrl;
			$globalFallbackMetaTags->twitterTitle       = $optimizedTitle;
			$globalFallbackMetaTags->twitterDescription = $optimizedDescription;
			$globalFallbackMetaTags->twitterImage       = $optimizedImage;
		}

		return $globalFallbackMetaTags;
	}
}
