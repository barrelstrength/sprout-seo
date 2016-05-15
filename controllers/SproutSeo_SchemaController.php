<?php
namespace Craft;

class SproutSeo_SchemaController extends BaseController
{

	public function actionSchemaEditTemplate()
	{
		$segment = craft()->request->getSegment(3);
		$this->renderTemplate('sproutseo/schema/' . $segment, array());
	}

	public function actionSaveSchema()
	{
		$this->requirePostRequest();

		$postData = craft()->request->getPost('sproutseo');

		$schema = SproutSeo_SchemaModel::populateModel($postData);

		if (sproutSeo()->schema->saveSchema('knowledgeGraph', $schema))
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
			->select('id, ownership')
			->from('sproutseo_globalmeta')
			->queryRow();

		if (count($results))
		{
			$results['ownership'] = JsonHelper::encode($ownershipMetaWithKeys);

			$id = (int) $results['id'];
			unset($results['id']);

			craft()->db->createCommand()->update('sproutseo_globalmeta',
				$results,
				'id=:id', array(':id'=> $id)
			);
		}
		else
		{

			craft()->db->createCommand()->insert('sproutseo_globalmeta', array(
				'ownership'=>JsonHelper::encode($ownershipTags)
			));
		}

	}
}
