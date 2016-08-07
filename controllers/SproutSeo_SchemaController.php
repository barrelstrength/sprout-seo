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

		// @todo - can we enforce this in a better place?
		if (isset($postData['identity']['url']) && $postData['identity']['url'] == "")
		{
			$postData['identity']['url'] = UrlHelper::getSiteUrl();
		}

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
			Craft::dd($schema->getErrors());
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
		$globalFallbackMetaTags = new SproutSeo_MetaTagsModel();

		$siteName = craft()->getSiteName();

		if (isset($postData['identity']))
		{
			$metaTitle       = $postData['identity']['name'];
			$metaDescription = $postData['identity']['description'];
			$metaKeywords    = $postData['identity']['keywords'];
			$metaImage       = isset($postData['identity']['logo'][0]) ? $postData['identity']['logo'][0] : null;

			// appendSiteName?  useAlternateName?
			$globalFallbackMetaTags->title       = $metaTitle;
			$globalFallbackMetaTags->description = $metaDescription;
			$globalFallbackMetaTags->keywords    = $metaKeywords;

			$globalFallbackMetaTags->ogType        = 'website';
			$globalFallbackMetaTags->ogSiteName    = $siteName;
			$globalFallbackMetaTags->ogAuthor      = '';
			$globalFallbackMetaTags->ogPublisher   = '';
			$globalFallbackMetaTags->ogTitle       = $metaTitle;
			$globalFallbackMetaTags->description   = $metaDescription;
			$globalFallbackMetaTags->ogImage       = $metaImage;
			$globalFallbackMetaTags->ogImageSecure = '';
			$globalFallbackMetaTags->ogImageWidth  = '';
			$globalFallbackMetaTags->ogImageHeight = '';
			$globalFallbackMetaTags->ogImageType   = '';

			$globalFallbackMetaTags->twitterCard        = 'summary';
			$globalFallbackMetaTags->twitterTitle       = $metaTitle;
			$globalFallbackMetaTags->twitterDescription = $metaDescription;
			$globalFallbackMetaTags->twitterImage       = $metaImage;
		}

		if (isset($postData['social']))
		{
			$socialProfiles = $postData['social'];

			foreach ($socialProfiles as $profile)
			{
				if ($profile[0] == 'Twitter')
				{
					$twitterUrl  = $profile[1];
					$twitterName = '@' . substr($twitterUrl, strrpos($twitterUrl, '/') + 1);

					$globalFallbackMetaTags->twitterSite    = $twitterName;
					$globalFallbackMetaTags->twitterCreator = $twitterName;
					break;
				}
			}
		}

		return $globalFallbackMetaTags;
	}
}
