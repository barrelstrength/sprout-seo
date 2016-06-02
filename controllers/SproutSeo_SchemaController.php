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

		$schema = SproutSeo_SchemaModel::populateModel($postData);

		if (sproutSeo()->schema->saveSchema($schemaType, $schema))
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
				$ownershipMetaWithKeys[$key]['service'] = $meta[0];
				$ownershipMetaWithKeys[$key]['metaTag'] = $meta[1];
				$ownershipMetaWithKeys[$key]['verificationCode'] = $meta[2];
			}
		}

		$schema = SproutSeo_SchemaModel::populateModel(array(
			$schemaType => $ownershipMetaWithKeys
			)
		);

		if (sproutSeo()->schema->saveSchema($schemaType, $schema))
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
}
