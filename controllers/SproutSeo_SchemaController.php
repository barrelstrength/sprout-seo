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

		// Remove empty items from multi-dimensional array
		$ownershipMeta = array_filter(array_map('array_filter', $ownershipMeta));

		$ownershipMetaWithKeys = array();

		foreach ($ownershipMeta as $key => $meta)
		{
			// @todo - add proper validation and return errors to template
			if (count($meta) == 3)
			{
				foreach ($meta as $key2 => $value)
				{
					if ($key2 == 0)
					{
						$ownershipMetaWithKeys[$key]['service'] = $value;
					}

					if ($key2 == 1)
					{
						$ownershipMetaWithKeys[$key]['metaTag'] = $value;
					}

					if ($key2 == 2)
					{
						$ownershipMetaWithKeys[$key]['verificationCode'] = $value;
					}
				}
			}
		}

		// @todo - move to service layer
		$results = craft()->db->createCommand()
			->select('id, locale, ownership')
			->from('sproutseo_globals')
			->queryRow();

		$globals            = SproutSeo_GlobalsModel::populateModel($results);
		$globals->ownership = JsonHelper::encode($ownershipMetaWithKeys);

		if (is_array($results) && count($results))
		{
			//$id = (int) $results['id'];
			//unset($results['id']);

			craft()->db->createCommand()->update('sproutseo_globals',
				$results,
				'id=:id', array(':id' => $globals->id)
			);
		}
		else
		{
			// @todo - update locale to be dynamic
			craft()->db->createCommand()->insert('sproutseo_globals', array(
				'locale'    => 'en_us',
				'ownership' => JsonHelper::encode($globals->ownership)
			));
		}
	}
}
